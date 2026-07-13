<?php

namespace App\Services;

use PDO;

/**
 * League table computed live from completed matches - there is no
 * separately-maintained standings table to keep in sync (and therefore no
 * way for it to drift, unlike the old card counters). "Refresh immediately
 * after a match finishes" falls out for free: the very next read reflects
 * the new completed match.
 */
class StandingsService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> standings rows sorted by points desc, goal difference desc */
    public function compute(?string $season = null): array
    {
        $params = [];
        $seasonFilter = '';
        if ($season !== null) {
            $seasonFilter = 'AND m.season = ?';
            $params[] = $season;
        }

        $sql = "
            SELECT
                t.team_id,
                t.name,
                COUNT(*) AS played,
                SUM(CASE
                    WHEN (m.team1_id = t.team_id AND m.team1_goal > m.team2_goal)
                      OR (m.team2_id = t.team_id AND m.team2_goal > m.team1_goal) THEN 1 ELSE 0 END) AS won,
                SUM(CASE WHEN m.team1_goal = m.team2_goal THEN 1 ELSE 0 END) AS drawn,
                SUM(CASE
                    WHEN (m.team1_id = t.team_id AND m.team1_goal < m.team2_goal)
                      OR (m.team2_id = t.team_id AND m.team2_goal < m.team1_goal) THEN 1 ELSE 0 END) AS lost,
                SUM(CASE WHEN m.team1_id = t.team_id THEN m.team1_goal ELSE m.team2_goal END) AS goals_for,
                SUM(CASE WHEN m.team1_id = t.team_id THEN m.team2_goal ELSE m.team1_goal END) AS goals_against
            FROM team t
            JOIN `match` m ON (m.team1_id = t.team_id OR m.team2_id = t.team_id) AND m.status = 'completed'
            WHERE 1 = 1 $seasonFilter
            GROUP BY t.team_id, t.name
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['played'] = (int) $row['played'];
            $row['won'] = (int) $row['won'];
            $row['drawn'] = (int) $row['drawn'];
            $row['lost'] = (int) $row['lost'];
            $row['goals_for'] = (int) $row['goals_for'];
            $row['goals_against'] = (int) $row['goals_against'];
            $row['goal_difference'] = $row['goals_for'] - $row['goals_against'];
            $row['points'] = $row['won'] * 3 + $row['drawn'];
        }
        unset($row);

        usort($rows, fn ($a, $b) => $b['points'] <=> $a['points'] ?: $b['goal_difference'] <=> $a['goal_difference']);

        return $rows;
    }
}
