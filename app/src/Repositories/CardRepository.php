<?php

namespace App\Repositories;

use PDO;

/**
 * Single point of read/write access to the `cards` table, which is the
 * canonical per-match card record (see plan: `cards` is the source of
 * truth - `match_day_reports` is no longer written to for cards).
 */
class CardRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO cards
                (member_id, card_type, match_id, card_time, card_reason_title, card_reason_detail, created_at)
             VALUES (:member_id, :card_type, :match_id, :card_time, :card_reason_title, :card_reason_detail, NOW())'
        );
        $stmt->execute([
            'member_id' => $data['member_id'],
            'card_type' => $data['card_type'],
            'match_id' => $data['match_id'],
            'card_time' => $data['card_time'] ?? null,
            'card_reason_title' => $data['card_reason_title'],
            'card_reason_detail' => $data['card_reason_detail'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $cardId, array $data): void
    {
        $fields = [];
        $params = ['card_id' => $cardId];

        foreach ($data as $column => $value) {
            $fields[] = "`$column` = :$column";
            $params[$column] = $value;
        }

        if (!$fields) {
            return;
        }

        $sql = 'UPDATE cards SET ' . implode(', ', $fields) . ' WHERE card_id = :card_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $cardId): void
    {
        $stmt = $this->pdo->prepare('UPDATE cards SET deleted_at = NOW() WHERE card_id = ?');
        $stmt->execute([$cardId]);
    }

    public function find(int $cardId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM cards WHERE card_id = ?');
        $stmt->execute([$cardId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string, mixed>> */
    public function findActiveByMatch(int $matchId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM cards WHERE match_id = ? AND deleted_at IS NULL ORDER BY created_at ASC'
        );
        $stmt->execute([$matchId]);
        return $stmt->fetchAll();
    }

    public function countActiveByMemberAndType(int $memberId, string $cardType): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM cards WHERE member_id = ? AND card_type = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$memberId, $cardType]);
        return (int) $stmt->fetchColumn();
    }

    public function countActiveByMemberMatchAndType(int $memberId, int $matchId, string $cardType): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM cards WHERE member_id = ? AND match_id = ? AND card_type = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$memberId, $matchId, $cardType]);
        return (int) $stmt->fetchColumn();
    }

    /** True if the player already has a red or second-yellow (double_yellow) card in this match, i.e. was sent off. */
    public function hasSendOffInMatch(int $memberId, int $matchId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM cards
             WHERE member_id = ? AND match_id = ? AND card_type IN ('red', 'double_yellow') AND deleted_at IS NULL"
        );
        $stmt->execute([$memberId, $matchId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return array{yellow:int,double_yellow:int,red:int} */
    public function activeCountsByMember(int $memberId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT card_type, COUNT(*) AS total
             FROM cards
             WHERE member_id = ? AND deleted_at IS NULL
             GROUP BY card_type'
        );
        $stmt->execute([$memberId]);

        $counts = ['yellow' => 0, 'double_yellow' => 0, 'red' => 0];
        foreach ($stmt->fetchAll() as $row) {
            if (isset($counts[$row['card_type']])) {
                $counts[$row['card_type']] = (int) $row['total'];
            }
        }
        return $counts;
    }

    /** @return array<int, array<string, mixed>> cards issued in a given match for a set of member ids */
    public function findActiveByMatchAndMembers(int $matchId, array $memberIds): array
    {
        if (!$memberIds) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT * FROM cards WHERE match_id = ? AND member_id IN ($placeholders) AND deleted_at IS NULL"
        );
        $stmt->execute([$matchId, ...$memberIds]);
        return $stmt->fetchAll();
    }

    /** Cards-by-season / cards-by-competition breakdown for one player. */
    public function breakdownByMember(int $memberId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.season, m.competition, c.card_type, COUNT(*) AS total
             FROM cards c
             JOIN `match` m ON c.match_id = m.id
             WHERE c.member_id = ? AND c.deleted_at IS NULL
             GROUP BY m.season, m.competition, c.card_type
             ORDER BY m.season DESC'
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }
}
