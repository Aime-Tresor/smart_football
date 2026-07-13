<?php

namespace App\Services;

use App\Repositories\CardRepository;

/**
 * Cards-by-season / cards-by-competition breakdown for a player, derived
 * from the same `cards` table CardStatsService uses for totals - so the
 * breakdown and the running totals can never disagree.
 */
class PlayerCardBreakdownService
{
    public function __construct(private CardRepository $cards)
    {
    }

    /**
     * @return array<string, array{season:string,competition:?string,yellow:int,double_yellow:int,red:int,total:int}>
     */
    public function bySeason(int $memberId): array
    {
        $rows = $this->cards->breakdownByMember($memberId);

        $bySeason = [];
        foreach ($rows as $row) {
            $season = $row['season'];
            $bySeason[$season] ??= [
                'season' => $season,
                'competition' => $row['competition'],
                'yellow' => 0,
                'double_yellow' => 0,
                'red' => 0,
                'total' => 0,
            ];
            $bySeason[$season][$row['card_type']] += (int) $row['total'];
            $bySeason[$season]['total'] += (int) $row['total'];
        }

        return array_values($bySeason);
    }

    /**
     * @return array<string, array{competition:string,yellow:int,double_yellow:int,red:int,total:int}>
     */
    public function byCompetition(int $memberId): array
    {
        $rows = $this->cards->breakdownByMember($memberId);

        $byCompetition = [];
        foreach ($rows as $row) {
            $competition = $row['competition'] ?? $row['season'];
            $byCompetition[$competition] ??= [
                'competition' => $competition,
                'yellow' => 0,
                'double_yellow' => 0,
                'red' => 0,
                'total' => 0,
            ];
            $byCompetition[$competition][$row['card_type']] += (int) $row['total'];
            $byCompetition[$competition]['total'] += (int) $row['total'];
        }

        return array_values($byCompetition);
    }
}
