<?php

namespace App\Services;

use App\Repositories\MatchRepository;
use PDO;

/**
 * Recalculates player statistics after a match is finished. Cards are
 * handled by CardStatsService (the `cards` table is reliable). Goals and
 * appearances are recomputed on a best-effort basis from
 * `match_day_reports`, since that is the only place this schema records
 * per-player goal attribution and match selection - and it's optional data
 * (a quick goal entry with no player chosen leaves no per-player row).
 *
 * Assists, own goals, and minutes played are NOT recalculated here: there
 * is no column or table anywhere in this schema that records them (no
 * assist/own-goal flag, no substitution/time-on-pitch tracking). Adding
 * real numbers for those would mean fabricating data, so they are left as
 * a follow-up that needs its own schema work.
 */
class PlayerStatsService
{
    public function __construct(
        private PDO $pdo,
        private MatchRepository $matches,
        private CardStatsService $cardStats,
    ) {
    }

    public function recalculateForMatch(int $matchId): void
    {
        foreach ($this->matches->participantMemberIds($matchId) as $memberId) {
            $this->recalculatePlayerTotals($memberId);
        }
    }

    public function recalculatePlayerTotals(int $memberId): void
    {
        $this->cardStats->recalculate($memberId);

        $stmt = $this->pdo->prepare('SELECT team FROM team_members WHERE member_id = ?');
        $stmt->execute([$memberId]);
        $team = $stmt->fetchColumn();
        if ($team === false) {
            return;
        }

        $stmt = $this->pdo->prepare(
            "SELECT
                SUM(CASE WHEN mdr.goal = '1' THEN 1 ELSE 0 END) AS goals,
                COUNT(DISTINCT mdr.week) AS appearances
             FROM match_day_reports mdr
             JOIN `match` m ON m.week = mdr.week AND (m.team1_id = mdr.team OR m.team2_id = mdr.team)
             WHERE mdr.team_member = ? AND mdr.team = ? AND m.status = 'completed'"
        );
        $stmt->execute([$memberId, $team]);
        $row = $stmt->fetch();

        $update = $this->pdo->prepare('UPDATE team_members SET goals_scored = ?, appearances = ? WHERE member_id = ?');
        $update->execute([(int) ($row['goals'] ?? 0), (int) ($row['appearances'] ?? 0), $memberId]);
    }
}
