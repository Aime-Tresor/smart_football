<?php

namespace App\Services;

use App\Repositories\CardRepository;
use App\Repositories\TeamMemberRepository;

/**
 * Single place responsible for keeping `team_members.yellow/double_yellow/red`
 * in sync with the `cards` table. Always *recomputes from source* (an
 * aggregate COUNT over active card rows) rather than incrementing/decrementing
 * a counter - that incremental approach is what caused the original
 * duplicate-counting/drift bug (see referee/save_card.php's old logic).
 */
class CardStatsService
{
    public function __construct(
        private CardRepository $cards,
        private TeamMemberRepository $members,
    ) {
    }

    public function recalculate(int $memberId): void
    {
        $counts = $this->cards->activeCountsByMember($memberId);
        $this->members->updateCardCounts($memberId, $counts['yellow'], $counts['double_yellow'], $counts['red']);
    }

    /** @return array{yellow:int,double_yellow:int,red:int,total:int} */
    public function totalsFor(int $memberId): array
    {
        $counts = $this->cards->activeCountsByMember($memberId);
        $counts['total'] = $counts['yellow'] + $counts['double_yellow'] + $counts['red'];
        return $counts;
    }
}
