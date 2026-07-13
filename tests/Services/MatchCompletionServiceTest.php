<?php

namespace Tests\Services;

use App\Repositories\CardRepository;
use App\Repositories\MatchRepository;
use App\Repositories\TeamMemberRepository;
use App\Services\CardStatsService;
use App\Services\MatchCompletionService;
use App\Services\PlayerStatsService;
use Tests\TestCase;

class MatchCompletionServiceTest extends TestCase
{
    private MatchCompletionService $service;
    private MatchRepository $matches;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matches = new MatchRepository($this->pdo);
        $playerStats = new PlayerStatsService(
            $this->pdo,
            $this->matches,
            new CardStatsService(new CardRepository($this->pdo), new TeamMemberRepository($this->pdo))
        );
        $this->service = new MatchCompletionService($this->pdo, $this->matches, $playerStats);
    }

    public function test_only_the_assigned_referee_can_finish_a_match(): void
    {
        $team1 = $this->createTeam('Completion Team A');
        $team2 = $this->createTeam('Completion Team B');
        $match = $this->createMatch($team1, $team2, 'live', ['team1_goal' => 1, 'team2_goal' => 0]);
        $this->assignReferee($match, 55);

        $wrongReferee = $this->service->finish($match, ['type' => 'referee', 'id' => 999]);
        $this->assertFalse($wrongReferee->success);
        $this->assertStringContainsString('assigned', $wrongReferee->error);

        $rightReferee = $this->service->finish($match, ['type' => 'referee', 'id' => 55]);
        $this->assertTrue($rightReferee->success);
        $this->assertSame('completed', $rightReferee->match['status']);
        $this->pdo->prepare('DELETE FROM match_status_log WHERE match_id = ?')->execute([$match]);
    }

    public function test_only_a_live_match_can_be_finished(): void
    {
        $team1 = $this->createTeam('Completion Team C');
        $team2 = $this->createTeam('Completion Team D');
        $match = $this->createMatch($team1, $team2, 'upcoming');

        $result = $this->service->finish($match, ['type' => 'fa_user', 'id' => 1]);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('live', $result->error);
    }

    public function test_missing_scores_require_explicit_confirmation(): void
    {
        $team1 = $this->createTeam('Completion Team E');
        $team2 = $this->createTeam('Completion Team F');
        $match = $this->createMatch($team1, $team2, 'live');

        $withoutConfirm = $this->service->finish($match, ['type' => 'fa_user', 'id' => 1]);
        $this->assertFalse($withoutConfirm->success);
        $this->assertTrue($withoutConfirm->needsConfirmation);

        $withConfirm = $this->service->finish($match, ['type' => 'fa_user', 'id' => 1], ['confirm_zero_scores' => true]);
        $this->assertTrue($withConfirm->success);
        $this->assertSame(0, $withConfirm->match['team1_goal']);
        $this->pdo->prepare('DELETE FROM match_status_log WHERE match_id = ?')->execute([$match]);
    }

    public function test_events_are_locked_after_completion_and_admin_can_reopen(): void
    {
        $team1 = $this->createTeam('Completion Team G');
        $team2 = $this->createTeam('Completion Team H');
        $match = $this->createMatch($team1, $team2, 'live', ['team1_goal' => 2, 'team2_goal' => 1]);

        $finish = $this->service->finish($match, ['type' => 'fa_user', 'id' => 1]);
        $this->assertTrue($finish->success);
        $this->assertTrue($this->matches->isCompleted($match));

        $refereeReopen = $this->service->reopen($match, ['type' => 'referee', 'id' => 1]);
        $this->assertFalse($refereeReopen->success);

        $adminReopen = $this->service->reopen($match, ['type' => 'fa_user', 'id' => 1]);
        $this->assertTrue($adminReopen->success);
        $this->assertSame('live', $adminReopen->match['status']);

        $this->pdo->prepare('DELETE FROM match_status_log WHERE match_id = ?')->execute([$match]);
    }
}
