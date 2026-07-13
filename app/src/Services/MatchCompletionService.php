<?php

namespace App\Services;

use App\Repositories\MatchRepository;
use PDO;
use Throwable;

/**
 * The "Finish Match" workflow. Only the assigned referee (per
 * weekly_fixtures) or an authenticated fa_user (admin/committee - this app
 * has no finer-grained admin role today) can finish or reopen a match.
 * Finishing is one transaction: match status -> completed, audit log
 * entry, and player stats (cards/goals/appearances) recalculated for every
 * participant. Reopening is admin-only and puts the match back in `live`
 * so a referee can correct events, then re-finish it.
 */
class MatchCompletionService
{
    public function __construct(
        private PDO $pdo,
        private MatchRepository $matches,
        private PlayerStatsService $playerStats,
    ) {
    }

    /**
     * @param array{type: string, id: int} $actor  type is 'referee' or 'fa_user'
     * @param array{team1_goal?: int|null, team2_goal?: int|null, confirm_zero_scores?: bool} $options
     */
    public function finish(int $matchId, array $actor, array $options = []): MatchActionResult
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return MatchActionResult::fail('Match not found.');
        }

        $authError = $this->authorize($matchId, $actor);
        if ($authError) {
            return MatchActionResult::fail($authError);
        }

        if ($match['status'] !== 'live') {
            return MatchActionResult::fail('Only a live match can be finished (current status: ' . $match['status'] . ').');
        }

        $team1Goal = $options['team1_goal'] ?? $match['team1_goal'];
        $team2Goal = $options['team2_goal'] ?? $match['team2_goal'];

        if (($team1Goal === null || $team2Goal === null) && empty($options['confirm_zero_scores'])) {
            return MatchActionResult::needsConfirmation(
                'This match has no final score recorded yet. Confirm to finish it with the score defaulted to 0.'
            );
        }

        $team1Goal ??= 0;
        $team2Goal ??= 0;

        try {
            $this->pdo->beginTransaction();

            $this->matches->updateStatus($matchId, 'completed', [
                'team1_goal' => $team1Goal,
                'team2_goal' => $team2Goal,
                'finished_at' => date('Y-m-d H:i:s'),
                'finished_by_type' => $actor['type'],
                'finished_by_id' => $actor['id'],
            ]);

            $this->matches->logStatusChange($matchId, $match['status'], 'completed', $actor['type'], $actor['id']);

            $this->playerStats->recalculateForMatch($matchId);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return MatchActionResult::fail('Failed to finish match: ' . $e->getMessage());
        }

        return MatchActionResult::ok($this->matches->find($matchId));
    }

    /** @param array{type: string, id: int} $actor */
    public function reopen(int $matchId, array $actor): MatchActionResult
    {
        if ($actor['type'] !== 'fa_user') {
            return MatchActionResult::fail('Only an administrator can reopen a match.');
        }

        $match = $this->matches->find($matchId);
        if (!$match) {
            return MatchActionResult::fail('Match not found.');
        }
        if ($match['status'] !== 'completed') {
            return MatchActionResult::fail('Only a completed match can be reopened.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->matches->updateStatus($matchId, 'live', [
                'reopened_at' => date('Y-m-d H:i:s'),
                'reopened_by_id' => $actor['id'],
            ]);

            $this->matches->logStatusChange($matchId, $match['status'], 'live', $actor['type'], $actor['id'], 'reopened');

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return MatchActionResult::fail('Failed to reopen match: ' . $e->getMessage());
        }

        return MatchActionResult::ok($this->matches->find($matchId));
    }

    private function authorize(int $matchId, array $actor): ?string
    {
        if ($actor['type'] === 'fa_user') {
            return null;
        }
        if ($actor['type'] === 'referee') {
            $assigned = $this->matches->assignedRefereeIds($matchId);
            if (!in_array((int) $actor['id'], $assigned, true)) {
                return 'Only the referee assigned to this match can finish it.';
            }
            return null;
        }
        return 'Unauthorized.';
    }
}
