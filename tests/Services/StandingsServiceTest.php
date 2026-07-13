<?php

namespace Tests\Services;

use App\Services\StandingsService;
use Tests\TestCase;

class StandingsServiceTest extends TestCase
{
    public function test_standings_are_computed_only_from_completed_matches_in_the_given_season(): void
    {
        $service = new StandingsService($this->pdo);
        $season = 'st-' . substr(uniqid(), -10);

        $teamA = $this->createTeam('Standings Team A');
        $teamB = $this->createTeam('Standings Team B');

        // A beats B 2-1 (completed, this season) -> A: 3pts, B: 0pts
        $this->createMatch($teamA, $teamB, 'completed', ['team1_goal' => 2, 'team2_goal' => 1, 'season' => $season]);
        // Draw 1-1 (completed, this season)
        $this->createMatch($teamA, $teamB, 'completed', ['team1_goal' => 1, 'team2_goal' => 1, 'season' => $season]);
        // Live match must NOT count
        $this->createMatch($teamA, $teamB, 'live', ['season' => $season]);
        // Different season must NOT count
        $this->createMatch($teamA, $teamB, 'completed', ['team1_goal' => 5, 'team2_goal' => 0, 'season' => 'other-season']);

        $standings = $service->compute($season);
        $byTeam = [];
        foreach ($standings as $row) {
            $byTeam[$row['team_id']] = $row;
        }

        $this->assertSame(2, $byTeam[$teamA]['played']);
        $this->assertSame(1, $byTeam[$teamA]['won']);
        $this->assertSame(1, $byTeam[$teamA]['drawn']);
        $this->assertSame(0, $byTeam[$teamA]['lost']);
        $this->assertSame(4, $byTeam[$teamA]['points']); // 3 + 1
        $this->assertSame(1, $byTeam[$teamA]['goal_difference']); // (2+1) - (1+1)

        $this->assertSame(1, $byTeam[$teamB]['points']);

        // Sorted by points desc: team A must come before team B.
        $this->assertSame($teamA, $standings[0]['team_id']);
    }
}
