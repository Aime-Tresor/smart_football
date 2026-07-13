<?php

namespace App\Repositories;

use PDO;

class AppealRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $appealId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM appeal_cases WHERE appeal_id = ?');
        $stmt->execute([$appealId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateAiSummary(int $appealId, array $data): void
    {
        $fields = [];
        $params = ['appeal_id' => $appealId];
        foreach ($data as $column => $value) {
            $fields[] = "`$column` = :$column";
            $params[$column] = $value;
        }
        if (!$fields) {
            return;
        }
        $sql = 'UPDATE appeal_cases SET ' . implode(', ', $fields) . ' WHERE appeal_id = :appeal_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}
