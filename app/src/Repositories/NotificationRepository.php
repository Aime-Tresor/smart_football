<?php

namespace App\Repositories;

use PDO;

class NotificationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param string|null $aiSummary        AI-generated decision/reason description, if any
     * @param string      $aiSummaryStatus  'none'|'pending'|'completed'|'failed'
     */
    public function create(
        string $recipientType,
        int $recipientId,
        string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $aiSummary = null,
        string $aiSummaryStatus = 'none'
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications (recipient_type, recipient_id, type, title, body, data, ai_summary, ai_summary_status, is_read, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())'
        );
        $stmt->execute([$recipientType, $recipientId, $type, $title, $body, json_encode($data), $aiSummary, $aiSummaryStatus]);
        return (int) $this->pdo->lastInsertId();
    }

    public function forRecipient(string $recipientType, int $recipientId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE recipient_type = ? AND recipient_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $recipientType);
        $stmt->bindValue(2, $recipientId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(string $recipientType, int $recipientId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0'
        );
        $stmt->execute([$recipientType, $recipientId]);
        return (int) $stmt->fetchColumn();
    }

    public function markRead(int $notificationId, string $recipientType, int $recipientId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE id = ? AND recipient_type = ? AND recipient_id = ?'
        );
        $stmt->execute([$notificationId, $recipientType, $recipientId]);
    }

    public function markAllRead(string $recipientType, int $recipientId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0'
        );
        $stmt->execute([$recipientType, $recipientId]);
    }
}
