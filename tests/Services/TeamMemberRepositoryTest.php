<?php

namespace Tests\Services;

use App\Repositories\TeamMemberRepository;
use Tests\TestCase;

class TeamMemberRepositoryTest extends TestCase
{
    public function test_findEligiblePlayers_excludes_suspended_players(): void
    {
        $members = new TeamMemberRepository($this->pdo);
        $team = $this->createTeam('Eligibility Test Team');

        $available = $this->createPlayer($team, 'Available', 'Player');
        $fiveYellows = $this->createPlayer($team, 'Five', 'Yellows', ['number' => 11]);
        $redCarded = $this->createPlayer($team, 'Red', 'Carded', ['number' => 12]);
        $doubleYellowed = $this->createPlayer($team, 'Double', 'Yellowed', ['number' => 13]);

        $this->pdo->prepare('UPDATE team_members SET yellow = 5 WHERE member_id = ?')->execute([$fiveYellows]);
        $this->pdo->prepare('UPDATE team_members SET red = 1 WHERE member_id = ?')->execute([$redCarded]);
        $this->pdo->prepare('UPDATE team_members SET double_yellow = 1 WHERE member_id = ?')->execute([$doubleYellowed]);

        $eligible = $members->findEligiblePlayers((string) $team);
        $eligibleIds = array_column($eligible, 'member_id');

        $this->assertContains($available, $eligibleIds);
        $this->assertNotContains($fiveYellows, $eligibleIds);
        $this->assertNotContains($redCarded, $eligibleIds);
        $this->assertNotContains($doubleYellowed, $eligibleIds);
    }

    public function test_isSuspended_matches_the_same_thresholds(): void
    {
        $members = new TeamMemberRepository($this->pdo);

        $this->assertFalse($members->isSuspended(['yellow' => 4, 'double_yellow' => 0, 'red' => 0]));
        $this->assertTrue($members->isSuspended(['yellow' => 5, 'double_yellow' => 0, 'red' => 0]));
        $this->assertTrue($members->isSuspended(['yellow' => 0, 'double_yellow' => 1, 'red' => 0]));
        $this->assertTrue($members->isSuspended(['yellow' => 0, 'double_yellow' => 0, 'red' => 1]));
    }
}
