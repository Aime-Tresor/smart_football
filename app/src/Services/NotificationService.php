<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\TeamMemberRepository;
use PDO;

/**
 * Resolves card/appeal notification recipients and delivers in-app +
 * (best-effort) email notifications. The app only has one shared login
 * per team (no per-staff accounts), so "team administrators" maps to the
 * team's own login/account, while manager/coach/captain are addressed by
 * email where one is on file and are also listed (by name/role) inside
 * the shared team notification feed.
 */
class NotificationService
{
    public function __construct(
        private PDO $pdo,
        private NotificationRepository $notifications,
        private TeamMemberRepository $members,
        private MailService $mail,
    ) {
    }

    /**
     * Notifies the affected team immediately after any card (yellow or
     * red) is recorded, per:
     *   "Red Card Issued
     *    John Doe received a Red Card in the 72nd minute.
     *    Reason: Violent Conduct."
     *
     * @param array $card    row from `cards` (after create, including the AI-generated card_reason_title)
     * @param array $match   row from `match` (with team1_name/team2_name)
     * @param array $player  row from `team_members`
     */
    public function notifyCardIssued(array $card, array $match, array $player): void
    {
        $teamId = (string) $player['team'];
        $team = $this->findTeam((int) $teamId);
        if (!$team) {
            return;
        }

        $matchName = ($match['team1_name'] ?? '?') . ' vs ' . ($match['team2_name'] ?? '?');
        $minute = $card['card_time'] ?: null;
        $timestamp = $card['created_at'] ?? date('Y-m-d H:i:s');
        $cardTypeLabel = $card['card_type'] === 'yellow' ? 'Yellow Card' : 'Red Card';
        $playerName = trim($player['fname'] . ' ' . $player['lname']);

        $recipients = $this->members->notificationRecipients($teamId);

        $title = $cardTypeLabel . ' Issued';
        $reasonTitle = $card['card_reason_title'] ?: 'Pending';
        $body = sprintf(
            "%s received a %s in the %s of %s.\nReason: %s.",
            $playerName,
            $cardTypeLabel,
            $this->minutePhrase($minute),
            $matchName,
            $reasonTitle
        );

        $data = [
            'player_name' => $playerName,
            'card_type' => $card['card_type'],
            'card_reason_title' => $card['card_reason_title'] ?? null,
            'match_name' => $matchName,
            'match_minute' => $minute,
            'timestamp' => $timestamp,
            'for_roles' => array_map(
                fn ($r) => trim($r['fname'] . ' ' . $r['lname']) . ($r['is_captain'] ? ' (Captain)' : ' (' . $r['post'] . ')'),
                $recipients
            ),
        ];

        // One in-app notification for the team account ("team administrators"),
        // marked unread until the team views it.
        $this->notifications->create(
            'team',
            (int) $teamId,
            'card_issued',
            $title,
            $body,
            $data,
            $card['ai_summary'] ?? null,
            $card['ai_summary_status'] ?? 'none'
        );

        $emailTargets = [];
        if (!empty($team['email'])) {
            $emailTargets[] = $team['email'];
        }
        foreach ($recipients as $recipient) {
            if (!empty($recipient['email'])) {
                $emailTargets[] = $recipient['email'];
            }
        }

        foreach (array_unique($emailTargets) as $email) {
            $this->mail->send($email, $title, nl2br(htmlspecialchars($body)));
        }
    }

    private function minutePhrase(?string $cardTime): string
    {
        if (!$cardTime) {
            return 'match';
        }
        if (ctype_digit($cardTime)) {
            return $this->ordinal((int) $cardTime) . ' minute';
        }
        return $cardTime . ' minute';
    }

    private function ordinal(int $number): string
    {
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            return $number . 'th';
        }
        return match ($number % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }

    /**
     * @param array $appeal row from `appeal_cases` (after the decision + AI
     *                       summary have been recorded)
     */
    public function notifyAppealDecision(array $appeal): void
    {
        $teamId = (string) $appeal['team_id'];
        $team = $this->findTeam((int) $teamId);
        if (!$team) {
            return;
        }

        $recipients = $this->members->notificationRecipients($teamId);
        $decision = $appeal['status'] === 'approved' ? 'Approved' : 'Rejected';

        $title = 'Appeal ' . $decision;
        // The AI summary is deliberately kept out of this plain body text -
        // it's stored in `data.ai_summary` and rendered as its own labeled
        // element in teams/notifications.php, so it isn't duplicated there.
        $body = sprintf(
            "Your appeal has been %s.\nCommittee Reasoning: %s\nDecision Date: %s",
            strtolower($decision),
            $appeal['decision_reason'] ?? '',
            $appeal['decision_date'] ?? date('Y-m-d H:i:s')
        );

        $data = [
            'appeal_id' => $appeal['appeal_id'],
            'status' => $appeal['status'],
            'decision_reason' => $appeal['decision_reason'] ?? null,
            'decision_date' => $appeal['decision_date'] ?? null,
        ];

        // One in-app notification for the team account, marked unread until
        // viewed. The AI summary is stored in its own column (not shown to
        // teams today - see teams/teams_appeals.php), kept available for
        // any future admin-facing notification view.
        $this->notifications->create(
            'team',
            (int) $teamId,
            'appeal_decision',
            $title,
            $body,
            $data,
            $appeal['ai_summary'] ?? null,
            $appeal['ai_summary_status'] ?? 'none'
        );

        $emailBody = $body . "\nAI Summary: " . ($appeal['ai_summary'] ?: 'Not available');

        $emailTargets = [];
        if (!empty($team['email'])) {
            $emailTargets[] = $team['email'];
        }
        foreach ($recipients as $recipient) {
            if (!empty($recipient['email'])) {
                $emailTargets[] = $recipient['email'];
            }
        }

        foreach (array_unique($emailTargets) as $email) {
            $this->mail->send($email, $title, nl2br(htmlspecialchars($emailBody)));
        }
    }

    private function findTeam(int $teamId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM team WHERE team_id = ?');
        $stmt->execute([$teamId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
