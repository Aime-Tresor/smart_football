<?php

namespace Tests\Services;

use App\Repositories\CardRepository;
use App\Repositories\MatchRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TeamMemberRepository;
use App\Services\CardService;
use App\Services\CardStatsService;
use App\Services\MailService;
use App\Services\NotificationService;
use Tests\Fakes\FakeAiSummaryGenerator;
use Tests\TestCase;

class CardServiceTest extends TestCase
{
    private CardService $service;
    private CardRepository $cards;
    private TeamMemberRepository $members;
    private int $team1;
    private int $team2;
    private int $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cards = new CardRepository($this->pdo);
        $this->members = new TeamMemberRepository($this->pdo);
        $matches = new MatchRepository($this->pdo);
        $stats = new CardStatsService($this->cards, $this->members);

        $this->service = new CardService($this->pdo, $this->cards, $matches, $this->members, $stats, new FakeAiSummaryGenerator());

        $this->team1 = $this->createTeam('Card Test Team A');
        $this->team2 = $this->createTeam('Card Test Team B');
        $this->player = $this->createPlayer($this->team1);
    }

    public function test_card_reason_title_is_required(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $result = $this->service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => '',
        ]);

        $this->assertFalse($result->success);
        $this->assertSame('Card Reason Title is required.', $result->error);
    }

    public function test_issuing_a_card_recalculates_team_member_totals(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $result = $this->service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => 'Dissent',
        ]);

        $this->assertTrue($result->success);
        $this->trackCard($result->card['card_id']);

        $totals = (new CardStatsService($this->cards, $this->members))->totalsFor($this->player);
        $this->assertSame(1, $totals['yellow']);
        $this->assertSame(0, $totals['red']);
    }

    public function test_ai_summary_is_a_deep_explanation_grounded_in_title_and_detail(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');
        $ai = new FakeAiSummaryGenerator(true, 'The player deliberately struck an opponent with an elbow during an off-the-ball incident, constituting violent conduct under the Laws of the Game.');
        $service = $this->serviceWithAi($ai);

        $result = $service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'red',
            'card_reason_title' => 'Violent Conduct',
            'card_reason_detail' => 'Struck an opponent off the ball.',
        ]);

        $this->assertTrue($result->success);
        $this->trackCard($result->card['card_id']);
        $this->assertSame('completed', $result->card['ai_summary_status']);
        $this->assertStringContainsString('violent conduct', $result->card['ai_summary']);
        $this->assertSame('Violent Conduct', $ai->lastTitle, 'The AI generator must receive the Card Reason Title.');
        $this->assertSame('Struck an opponent off the ball.', $ai->lastDetail);
        // The referee-chosen title itself is never touched by AI.
        $this->assertSame('Violent Conduct', $result->card['card_reason_title']);
    }

    public function test_ai_summary_is_generated_from_title_alone_when_no_detail_given(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');
        $ai = new FakeAiSummaryGenerator();
        $service = $this->serviceWithAi($ai);

        $result = $service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => 'Dissent',
        ]);

        $this->assertTrue($result->success);
        $this->trackCard($result->card['card_id']);
        $this->assertSame(1, $ai->callCount, 'AI summary must be generated even without a detailed explanation.');
        $this->assertSame('Dissent', $ai->lastTitle);
        $this->assertNull($ai->lastDetail);
        $this->assertSame('completed', $result->card['ai_summary_status']);
    }

    public function test_failed_ai_generation_does_not_block_the_card(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');
        $ai = new FakeAiSummaryGenerator(false);
        $service = $this->serviceWithAi($ai);

        $result = $service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => 'Dissent',
        ]);

        $this->assertTrue($result->success, 'AI failure must never block the card from saving.');
        $this->trackCard($result->card['card_id']);
        $this->assertSame('failed', $result->card['ai_summary_status']);
        $this->assertNull($result->card['ai_summary']);
        // The card's own title is unaffected by an AI outage.
        $this->assertSame('Dissent', $result->card['card_reason_title']);
    }

    public function test_deferred_ai_processing_leaves_summary_pending_until_completed(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');
        $ai = new FakeAiSummaryGenerator(true, 'Deep explanation of the incident.');
        $service = $this->serviceWithAi($ai);

        $result = $service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'red',
            'card_reason_title' => 'Violent Conduct',
            'card_reason_detail' => 'Struck an opponent off the ball.',
        ], deferAiProcessing: true);

        $this->assertTrue($result->success);
        $this->trackCard($result->card['card_id']);
        $this->assertSame(0, $ai->callCount, 'AI must not run yet when processing is deferred.');
        $this->assertSame('pending', $result->card['ai_summary_status']);
        $this->assertSame('Violent Conduct', $result->card['card_reason_title'], 'Title is known immediately - it is never AI-derived.');

        $completed = $service->completeCardProcessing($result->card['card_id']);
        $this->assertSame(1, $ai->callCount);
        $this->assertSame('Deep explanation of the incident.', $completed['ai_summary']);
        $this->assertSame('completed', $completed['ai_summary_status']);

        $stmt = $this->pdo->prepare('SELECT * FROM ai_discipline_cases WHERE card_id = ?');
        $stmt->execute([$result->card['card_id']]);
        $case = $stmt->fetch();
        if ($case) {
            $this->track('ai_discipline_cases', (int) $case['case_id']);
        }
    }

    public function test_team_is_notified_for_yellow_and_red_cards(): void
    {
        $notificationRepository = new NotificationRepository($this->pdo);
        $notifications = new NotificationService($this->pdo, $notificationRepository, $this->members, new MailService());
        $ai = new FakeAiSummaryGenerator();
        $service = new CardService(
            $this->pdo, $this->cards, new MatchRepository($this->pdo), $this->members,
            new CardStatsService($this->cards, $this->members), $ai, $notifications
        );

        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $yellow = $service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'yellow',
            'card_reason_title' => 'Reckless Tackle',
        ]);
        $this->trackCard($yellow->card['card_id']);

        $rows = $notificationRepository->forRecipient('team', $this->team1);
        $this->assertCount(1, $rows, 'A yellow card must also notify the team, not just red cards.');
        $this->track('notifications', (int) $rows[0]['id']);
        $this->assertSame('Yellow Card Issued', $rows[0]['title']);
        $this->assertStringContainsString('Reckless Tackle', $rows[0]['body']);
    }

    public function test_second_yellow_card_is_recorded_as_double_yellow_and_creates_discipline_case(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $first = $this->service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => 'Unsporting Behaviour',
        ]);
        $this->trackCard($first->card['card_id']);

        $second = $this->service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => 'Second Yellow Card',
        ]);
        $this->assertTrue($second->success);
        $this->trackCard($second->card['card_id']);

        $this->assertSame('double_yellow', $second->card['card_type']);

        // Totals are derived straight from the `cards` rows (one true yellow,
        // one second-yellow-converted-to-red) rather than a mutated counter,
        // so both are reflected - nothing is silently reset/lost.
        $totals = (new CardStatsService($this->cards, $this->members))->totalsFor($this->player);
        $this->assertSame(1, $totals['yellow']);
        $this->assertSame(1, $totals['double_yellow']);

        $stmt = $this->pdo->prepare('SELECT * FROM ai_discipline_cases WHERE card_id = ?');
        $stmt->execute([$second->card['card_id']]);
        $case = $stmt->fetch();
        $this->assertNotFalse($case, 'Expected a discipline case to be created for the red-equivalent card.');
        $this->track('ai_discipline_cases', (int) $case['case_id']);
    }

    public function test_a_yellow_card_from_an_earlier_match_does_not_carry_over_to_the_next(): void
    {
        $earlierMatch = $this->createMatch($this->team1, $this->team2, 'live');
        $first = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $earlierMatch, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->assertTrue($first->success);
        $this->trackCard($first->card['card_id']);

        $laterMatch = $this->createMatch($this->team1, $this->team2, 'live');
        $second = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $laterMatch, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->assertTrue($second->success);
        $this->trackCard($second->card['card_id']);

        // Must still be a plain yellow in the new match, not a carried-over second-yellow red.
        $this->assertSame('yellow', $second->card['card_type']);
    }

    public function test_no_further_cards_can_be_issued_after_a_player_is_sent_off_in_that_match(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $red = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'red', 'card_reason_title' => 'Serious Foul Play',
        ]);
        $this->assertTrue($red->success);
        $this->trackCard($red->card['card_id']);

        $secondRed = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'red', 'card_reason_title' => 'Violent Conduct',
        ]);
        $this->assertFalse($secondRed->success, 'A player already sent off must not receive a second red card in the same match.');

        $yellowAfterRed = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->assertFalse($yellowAfterRed->success, 'A player already sent off must not receive any further card in the same match.');
    }

    public function test_a_player_with_any_red_card_is_suspended_from_future_matches_until_cleared(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');
        $red = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'red', 'card_reason_title' => 'Serious Foul Play',
        ]);
        $this->assertTrue($red->success);
        $this->trackCard($red->card['card_id']);

        // Suspended (this app's existing definition: any red/double-yellow,
        // or 5+ yellows) - must not be issuable a card in ANY match, not
        // just the one they were sent off in.
        $otherMatch = $this->createMatch($this->team1, $this->team2, 'live');
        $inOtherMatch = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $otherMatch, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->assertFalse($inOtherMatch->success);
        $this->assertStringContainsString('suspended', $inOtherMatch->error);

        // An admin clearing the card (e.g. once the suspension is served)
        // is the recovery path - the player becomes eligible again.
        $deleteResult = $this->service->deleteCard($red->card['card_id']);
        $this->assertTrue($deleteResult->success);

        $afterClearing = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $otherMatch, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->assertTrue($afterClearing->success);
        $this->trackCard($afterClearing->card['card_id']);
    }

    public function test_a_third_yellow_card_attempt_in_the_same_match_is_rejected(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $first = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->trackCard($first->card['card_id']);

        $second = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'yellow', 'card_reason_title' => 'Second Yellow Card',
        ]);
        $this->assertTrue($second->success);
        $this->trackCard($second->card['card_id']);
        $this->assertSame('double_yellow', $second->card['card_type']);

        $third = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->assertFalse($third->success, 'A player already sent off (2 yellows) must not receive a 3rd yellow in the same match.');

        $stmt = $this->pdo->prepare('SELECT * FROM ai_discipline_cases WHERE card_id = ?');
        $stmt->execute([$second->card['card_id']]);
        $case = $stmt->fetch();
        if ($case) {
            $this->track('ai_discipline_cases', (int) $case['case_id']);
        }
    }

    public function test_cards_cannot_be_issued_once_match_is_completed(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'completed', ['team1_goal' => 1, 'team2_goal' => 0]);

        $result = $this->service->issueCard([
            'member_id' => $this->player,
            'match_id' => $match,
            'card_type' => 'yellow',
            'card_reason_title' => 'Dissent',
        ]);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('finished', $result->error);
    }

    public function test_deleting_a_card_recalculates_totals_and_prevents_duplicate_counting(): void
    {
        $match = $this->createMatch($this->team1, $this->team2, 'live');

        $a = $this->service->issueCard([
            'member_id' => $this->player, 'match_id' => $match, 'card_type' => 'yellow', 'card_reason_title' => 'Dissent',
        ]);
        $this->trackCard($a->card['card_id']);

        $totalsAfterCreate = (new CardStatsService($this->cards, $this->members))->totalsFor($this->player);
        $this->assertSame(1, $totalsAfterCreate['yellow']);

        $deleteResult = $this->service->deleteCard($a->card['card_id']);
        $this->assertTrue($deleteResult->success);

        $totalsAfterDelete = (new CardStatsService($this->cards, $this->members))->totalsFor($this->player);
        $this->assertSame(0, $totalsAfterDelete['yellow'], 'Deleted cards must not be double counted.');
    }

    private function serviceWithAi(FakeAiSummaryGenerator $ai): CardService
    {
        return new CardService(
            $this->pdo, $this->cards, new MatchRepository($this->pdo), $this->members,
            new CardStatsService($this->cards, $this->members), $ai
        );
    }
}
