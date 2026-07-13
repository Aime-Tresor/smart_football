<?php

namespace App\Services;

use App\Repositories\AppealRepository;
use Throwable;

/**
 * Generates a concise AI summary of a discipline committee's appeal
 * decision, grounded in the decision itself (approved/rejected) and the
 * committee's `decision_reason` text - mirroring how CardService summarizes
 * a card's reason title/detail. Never throws: a failure is recorded as a
 * status on the row and must never block the decision from being saved.
 */
class AppealDecisionService
{
    public function __construct(
        private AppealRepository $appeals,
        private ?AiSummaryGenerator $ai = null,
        private ?NotificationService $notifications = null,
    ) {
    }

    /**
     * Called right after a decision is recorded: generates the AI summary
     * and (best-effort) notifies the team - including the AI summary in the
     * notification. Never blocks the decision itself from being saved.
     */
    public function recordDecisionMade(int $appealId): void
    {
        $this->generateDecisionSummary($appealId);

        if (!$this->notifications) {
            return;
        }

        try {
            $appeal = $this->appeals->find($appealId);
            if ($appeal) {
                $this->notifications->notifyAppealDecision($appeal);
            }
        } catch (Throwable $e) {
            error_log('Appeal decision notification failed for appeal ' . $appealId . ': ' . $e->getMessage());
        }
    }

    public function generateDecisionSummary(int $appealId): void
    {
        if (!$this->ai) {
            return;
        }

        $appeal = $this->appeals->find($appealId);
        if (!$appeal || !$appeal['decision_reason']) {
            return;
        }

        $title = $appeal['status'] === 'approved' ? 'Appeal Approved' : 'Appeal Rejected';

        try {
            $result = $this->ai->summarize($title, $appeal['decision_reason']);
            $this->appeals->updateAiSummary($appealId, [
                'ai_summary' => $result->text,
                'ai_summary_status' => $result->status,
                'ai_summary_error' => $result->error,
                'ai_summary_generated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            $this->appeals->updateAiSummary($appealId, [
                'ai_summary_status' => 'failed',
                'ai_summary_error' => $e->getMessage(),
            ]);
        }
    }

    /** Regenerates the decision summary on demand (admin action) - does not re-notify the team. */
    public function regenerate(int $appealId): AiSummaryResult
    {
        if (!$this->ai) {
            return AiSummaryResult::failure('AI summary service is not configured.');
        }
        $this->generateDecisionSummary($appealId);
        $appeal = $this->appeals->find($appealId);
        return $appeal && $appeal['ai_summary_status'] === 'completed'
            ? AiSummaryResult::success($appeal['ai_summary'])
            : AiSummaryResult::failure($appeal['ai_summary_error'] ?? 'AI summary generation failed.');
    }
}
