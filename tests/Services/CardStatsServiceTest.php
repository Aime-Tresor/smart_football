<?php

namespace Tests\Services;

use App\Repositories\CardRepository;
use App\Repositories\TeamMemberRepository;
use App\Services\CardStatsService;
use Tests\TestCase;

class CardStatsServiceTest extends TestCase
{
    public function test_recalculate_is_idempotent_and_never_double_counts(): void
    {
        $cards = new CardRepository($this->pdo);
        $members = new TeamMemberRepository($this->pdo);
        $stats = new CardStatsService($cards, $members);

        $team = $this->createTeam('Stats Test Team');
        $player = $this->createPlayer($team);
        $match = $this->createMatch($team, $this->createTeam('Stats Test Opponent'), 'live');

        $cardId1 = $cards->create([
            'member_id' => $player, 'card_type' => 'yellow', 'match_id' => $match,
            'card_reason_title' => 'Dissent',
        ]);
        $this->trackCard($cardId1);
        $cardId2 = $cards->create([
            'member_id' => $player, 'card_type' => 'red', 'match_id' => $match,
            'card_reason_title' => 'Violent Conduct',
        ]);
        $this->trackCard($cardId2);

        $stats->recalculate($player);
        $stats->recalculate($player); // calling twice must not double the counts
        $stats->recalculate($player);

        $totals = $stats->totalsFor($player);
        $this->assertSame(1, $totals['yellow']);
        $this->assertSame(1, $totals['red']);
        $this->assertSame(2, $totals['total']);
    }

    public function test_soft_deleted_cards_are_excluded_from_totals(): void
    {
        $cards = new CardRepository($this->pdo);
        $members = new TeamMemberRepository($this->pdo);
        $stats = new CardStatsService($cards, $members);

        $team = $this->createTeam('Stats Test Team 2');
        $player = $this->createPlayer($team);
        $match = $this->createMatch($team, $this->createTeam('Stats Test Opponent 2'), 'live');

        $cardId = $cards->create([
            'member_id' => $player, 'card_type' => 'yellow', 'match_id' => $match,
            'card_reason_title' => 'Dissent',
        ]);
        $this->trackCard($cardId);

        $cards->softDelete($cardId);
        $stats->recalculate($player);

        $totals = $stats->totalsFor($player);
        $this->assertSame(0, $totals['yellow']);
    }
}
