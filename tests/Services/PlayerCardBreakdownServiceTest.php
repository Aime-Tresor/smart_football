<?php

namespace Tests\Services;

use App\Repositories\CardRepository;
use App\Services\PlayerCardBreakdownService;
use Tests\TestCase;

class PlayerCardBreakdownServiceTest extends TestCase
{
    public function test_breakdown_groups_cards_by_season(): void
    {
        $cards = new CardRepository($this->pdo);
        $service = new PlayerCardBreakdownService($cards);

        $team = $this->createTeam('Breakdown Team');
        $opponent = $this->createTeam('Breakdown Opponent');
        $player = $this->createPlayer($team);

        $seasonA = 'bk-a-' . substr(uniqid(), -8);
        $seasonB = 'bk-b-' . substr(uniqid(), -8);

        $matchA = $this->createMatch($team, $opponent, 'completed', ['team1_goal' => 1, 'team2_goal' => 0, 'season' => $seasonA]);
        $matchB = $this->createMatch($team, $opponent, 'completed', ['team1_goal' => 1, 'team2_goal' => 0, 'season' => $seasonB]);

        $this->trackCard($cards->create(['member_id' => $player, 'card_type' => 'yellow', 'match_id' => $matchA, 'card_reason_title' => 'Dissent']));
        $this->trackCard($cards->create(['member_id' => $player, 'card_type' => 'yellow', 'match_id' => $matchA, 'card_reason_title' => 'Dissent']));
        $this->trackCard($cards->create(['member_id' => $player, 'card_type' => 'red', 'match_id' => $matchB, 'card_reason_title' => 'Violent Conduct']));

        $breakdown = $service->bySeason($player);
        $bySeasonKey = [];
        foreach ($breakdown as $row) {
            $bySeasonKey[$row['season']] = $row;
        }

        $this->assertSame(2, $bySeasonKey[$seasonA]['yellow']);
        $this->assertSame(2, $bySeasonKey[$seasonA]['total']);
        $this->assertSame(1, $bySeasonKey[$seasonB]['red']);
        $this->assertSame(1, $bySeasonKey[$seasonB]['total']);
    }
}
