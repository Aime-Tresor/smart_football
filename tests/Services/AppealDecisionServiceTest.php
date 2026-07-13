<?php

namespace Tests\Services;

use App\Repositories\AppealRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TeamMemberRepository;
use App\Services\AppealDecisionService;
use App\Services\MailService;
use App\Services\NotificationService;
use Tests\Fakes\FakeAiSummaryGenerator;
use Tests\TestCase;

class AppealDecisionServiceTest extends TestCase
{
    /** @return array{0: int, 1: int} [$appealId, $teamId] */
    private function createAppeal(string $status, ?string $decisionReason, ?int $teamId = null): array
    {
        $team = $teamId ?? $this->createTeam('Appeal Test Team');
        $stmt = $this->pdo->prepare(
            'INSERT INTO appeal_cases (discipline_case_id, team_id, appeal_reason, status, decision_reason, decision_date)
             VALUES (1, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$team, 'We believe the sending off was unjustified.', $status, $decisionReason]);
        $appealId = (int) $this->pdo->lastInsertId();
        $this->track('appeal_cases', $appealId);
        return [$appealId, $team];
    }

    public function test_generates_a_summary_grounded_in_the_decision_and_reason(): void
    {
        $appeals = new AppealRepository($this->pdo);
        $ai = new FakeAiSummaryGenerator(true, 'Appeal upheld due to insufficient evidence.');
        $service = new AppealDecisionService($appeals, $ai);

        [$appealId] = $this->createAppeal('approved', 'The video evidence did not clearly show intent.');

        $service->generateDecisionSummary($appealId);

        $this->assertSame('Appeal Approved', $ai->lastTitle);
        $this->assertSame('The video evidence did not clearly show intent.', $ai->lastDetail);

        $updated = $appeals->find($appealId);
        $this->assertSame('completed', $updated['ai_summary_status']);
        $this->assertSame('Appeal upheld due to insufficient evidence.', $updated['ai_summary']);
    }

    public function test_does_nothing_when_there_is_no_decision_reason_yet(): void
    {
        $appeals = new AppealRepository($this->pdo);
        $ai = new FakeAiSummaryGenerator();
        $service = new AppealDecisionService($appeals, $ai);

        [$appealId] = $this->createAppeal('pending', null);

        $service->generateDecisionSummary($appealId);

        $this->assertSame(0, $ai->callCount);
        $updated = $appeals->find($appealId);
        $this->assertSame('none', $updated['ai_summary_status']);
    }

    public function test_regenerate_returns_a_result_reflecting_the_stored_summary(): void
    {
        $appeals = new AppealRepository($this->pdo);
        $ai = new FakeAiSummaryGenerator(true, 'Rejected - no new evidence presented.');
        $service = new AppealDecisionService($appeals, $ai);

        [$appealId] = $this->createAppeal('rejected', 'No new evidence was presented by the club.');

        $result = $service->regenerate($appealId);

        $this->assertSame('completed', $result->status);
        $this->assertSame('Rejected - no new evidence presented.', $result->text);
    }

    public function test_recordDecisionMade_generates_summary_and_notifies_the_team_once(): void
    {
        $appeals = new AppealRepository($this->pdo);
        $ai = new FakeAiSummaryGenerator(true, 'Appeal rejected - evidence was conclusive.');
        $notificationRepository = new NotificationRepository($this->pdo);
        $notifications = new NotificationService($this->pdo, $notificationRepository, new TeamMemberRepository($this->pdo), new MailService());
        $service = new AppealDecisionService($appeals, $ai, $notifications);

        [$appealId, $team] = $this->createAppeal('rejected', 'The video evidence was conclusive.');

        $service->recordDecisionMade($appealId);

        $updated = $appeals->find($appealId);
        $this->assertSame('completed', $updated['ai_summary_status']);

        $rows = $notificationRepository->forRecipient('team', $team);
        $this->assertCount(1, $rows);
        $this->track('notifications', (int) $rows[0]['id']);
        $this->assertSame('Appeal Rejected', $rows[0]['title']);
        $this->assertSame(0, (int) $rows[0]['is_read']);

        $data = json_decode($rows[0]['data'], true);
        $this->assertSame('rejected', $data['status']);

        // The AI summary lives in its own dedicated column, not in `data`.
        $this->assertSame('Appeal rejected - evidence was conclusive.', $rows[0]['ai_summary']);
        $this->assertSame('completed', $rows[0]['ai_summary_status']);

        // Regenerating the summary afterwards must not fire a second notification.
        $service->regenerate($appealId);
        $this->assertCount(1, $notificationRepository->forRecipient('team', $team));
    }
}
