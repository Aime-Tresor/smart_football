<?php

namespace Tests\Services;

use App\Repositories\CardRepository;
use App\Repositories\MatchRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TeamMemberRepository;
use App\Services\MailService;
use App\Services\NotificationService;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    public function test_red_card_creates_an_unread_team_notification_with_expected_content(): void
    {
        $notifications = new NotificationRepository($this->pdo);
        $members = new TeamMemberRepository($this->pdo);
        $service = new NotificationService($this->pdo, $notifications, $members, new MailService());

        $team = $this->createTeam('Notify Test Team');
        $captain = $this->createPlayer($team, 'Cap', 'Tain', ['is_captain' => 1]);
        $player = $this->createPlayer($team, 'Sent', 'Off');
        $opponent = $this->createTeam('Notify Test Opponent');
        $match = $this->createMatch($team, $opponent, 'live');

        $cards = new CardRepository($this->pdo);
        $cardId = $cards->create([
            'member_id' => $player,
            'card_type' => 'red',
            'match_id' => $match,
            'card_time' => '78',
            'card_reason_title' => 'Violent Conduct',
            'card_reason_detail' => 'Struck an opponent.',
        ]);
        $this->trackCard($cardId);
        $cards->update($cardId, [
            'ai_summary_status' => 'completed',
            'ai_summary' => 'The player made deliberate, forceful contact with an opponent well away from the ball, denying any realistic attempt to play it.',
        ]);
        $card = $cards->find($cardId);

        $matchRow = (new MatchRepository($this->pdo))->find($match);
        $playerRow = $members->find($player);

        $service->notifyCardIssued($card, $matchRow, $playerRow);

        $rows = $notifications->forRecipient('team', $team);
        $this->track('notifications', (int) $rows[0]['id']);

        $this->assertCount(1, $rows);
        $this->assertSame(0, (int) $rows[0]['is_read']);
        $this->assertSame('Red Card Issued', $rows[0]['title']);
        $this->assertStringContainsString('Sent Off received a Red Card', $rows[0]['body']);
        $this->assertStringContainsString('78th minute', $rows[0]['body']);
        $this->assertStringContainsString('Reason: Violent Conduct.', $rows[0]['body']);

        $data = json_decode($rows[0]['data'], true);
        $this->assertSame('Violent Conduct', $data['card_reason_title']);
        $this->assertContains('Cap Tain (Captain)', $data['for_roles']);

        $this->assertSame(
            'The player made deliberate, forceful contact with an opponent well away from the ball, denying any realistic attempt to play it.',
            $rows[0]['ai_summary']
        );
        $this->assertSame('completed', $rows[0]['ai_summary_status']);

        $this->assertSame(1, $notifications->unreadCount('team', $team));
        $notifications->markRead((int) $rows[0]['id'], 'team', $team);
        $this->assertSame(0, $notifications->unreadCount('team', $team));
    }

    public function test_yellow_card_notification_uses_ordinal_minute_and_correct_title(): void
    {
        $notifications = new NotificationRepository($this->pdo);
        $members = new TeamMemberRepository($this->pdo);
        $service = new NotificationService($this->pdo, $notifications, $members, new MailService());

        $team = $this->createTeam('Yellow Notify Team');
        $player = $this->createPlayer($team, 'John', 'Doe');
        $opponent = $this->createTeam('Yellow Notify Opponent');
        $match = $this->createMatch($team, $opponent, 'live');

        $cards = new CardRepository($this->pdo);
        $cardId = $cards->create([
            'member_id' => $player,
            'card_type' => 'yellow',
            'match_id' => $match,
            'card_time' => '44',
            'card_reason_title' => 'Reckless Tackle',
            'card_reason_detail' => 'A late, high challenge.',
        ]);
        $this->trackCard($cardId);
        $card = $cards->find($cardId);

        $matchRow = (new MatchRepository($this->pdo))->find($match);
        $playerRow = $members->find($player);

        $service->notifyCardIssued($card, $matchRow, $playerRow);

        $rows = $notifications->forRecipient('team', $team);
        $this->track('notifications', (int) $rows[0]['id']);

        $this->assertSame('Yellow Card Issued', $rows[0]['title']);
        $this->assertStringContainsString('John Doe received a Yellow Card', $rows[0]['body']);
        $this->assertStringContainsString('44th minute', $rows[0]['body']);
        $this->assertStringContainsString('Reason: Reckless Tackle.', $rows[0]['body']);
    }
}
