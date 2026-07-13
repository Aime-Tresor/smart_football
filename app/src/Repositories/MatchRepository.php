<?php

namespace App\Repositories;

use PDO;

class MatchRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $matchId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT m.*, t1.name AS team1_name, t2.name AS team2_name
             FROM `match` m
             JOIN team t1 ON m.team1_id = t1.team_id
             JOIN team t2 ON m.team2_id = t2.team_id
             WHERE m.id = ?'
        );
        $stmt->execute([$matchId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isCompleted(int $matchId): bool
    {
        $stmt = $this->pdo->prepare('SELECT status FROM `match` WHERE id = ?');
        $stmt->execute([$matchId]);
        return $stmt->fetchColumn() === 'completed';
    }

    /** Referee ids (main + official) assigned to this match via weekly_fixtures. */
    public function assignedRefereeIds(int $matchId): array
    {
        $stmt = $this->pdo->prepare('SELECT referee, official FROM weekly_fixtures WHERE match_id = ?');
        $stmt->execute([$matchId]);
        $row = $stmt->fetch();
        if (!$row) {
            return [];
        }
        return array_values(array_unique(array_filter([(int) $row['referee'], (int) $row['official']])));
    }

    public function updateStatus(int $matchId, string $status, array $extra = []): void
    {
        $fields = ['status = :status'];
        $params = ['status' => $status, 'id' => $matchId];
        foreach ($extra as $column => $value) {
            $fields[] = "`$column` = :$column";
            $params[$column] = $value;
        }
        $sql = 'UPDATE `match` SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function logStatusChange(int $matchId, string $oldStatus, string $newStatus, string $actorType, ?int $actorId, ?string $note = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO match_status_log (match_id, old_status, new_status, actor_type, actor_id, note, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$matchId, $oldStatus, $newStatus, $actorType, $actorId, $note]);
    }

    /** Distinct member ids who have any card or goal recorded in this match. */
    public function participantMemberIds(int $matchId): array
    {
        $stmt = $this->pdo->prepare('SELECT DISTINCT member_id FROM cards WHERE match_id = ? AND deleted_at IS NULL');
        $stmt->execute([$matchId]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if ($this->tableExists('individual_goals')) {
            $stmt = $this->pdo->prepare('SELECT DISTINCT player_id FROM individual_goals WHERE match_id = ?');
            $stmt->execute([$matchId]);
            $ids = array_merge($ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
        }

        return array_values(array_unique($ids));
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
