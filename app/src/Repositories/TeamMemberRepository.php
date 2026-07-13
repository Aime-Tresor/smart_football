<?php

namespace App\Repositories;

use PDO;

class TeamMemberRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $memberId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM team_members WHERE member_id = ?');
        $stmt->execute([$memberId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateCardCounts(int $memberId, int $yellow, int $doubleYellow, int $red): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE team_members SET yellow = ?, double_yellow = ?, red = ? WHERE member_id = ?'
        );
        $stmt->execute([$yellow, $doubleYellow, $red, $memberId]);
    }

    /**
     * Same suspension definition already surfaced as a "Suspended" badge in
     * teams/player_cards.php: 5+ accumulated yellow cards, or any red/
     * double-yellow (sent off). Kept in one place so every "players
     * available for this match" list agrees on who is eligible.
     */
    public function isSuspended(array $player): bool
    {
        return (int) $player['yellow'] >= 5
            || (int) $player['double_yellow'] > 0
            || (int) $player['red'] > 0;
    }

    /** Players on a team who are NOT suspended - i.e. eligible to play/be selected for a match. */
    public function findEligiblePlayers(string $teamId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM team_members
             WHERE team = ? AND role_in_team = 'player'
               AND yellow < 5 AND double_yellow = 0 AND red = 0
             ORDER BY number ASC, fname ASC"
        );
        $stmt->execute([$teamId]);
        return $stmt->fetchAll();
    }

    /**
     * Staff (manager/coach) and captain rows for a team, used to resolve
     * red-card notification recipients. `role_in_team`/`post` are free
     * text in this schema, so matching is a best-effort keyword match.
     */
    public function notificationRecipients(string $teamId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT member_id, fname, lname, email, post, role_in_team, is_captain
             FROM team_members
             WHERE team = ?
               AND (
                    is_captain = 1
                    OR (role_in_team = 'staff' AND (
                        post LIKE '%manager%' OR post LIKE '%coach%' OR post LIKE '%hc%'
                    ))
               )"
        );
        $stmt->execute([$teamId]);
        return $stmt->fetchAll();
    }
}
