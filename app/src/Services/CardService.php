<?php

namespace App\Services;

use App\Repositories\CardRepository;
use App\Repositories\MatchRepository;
use App\Repositories\TeamMemberRepository;
use PDO;
use Throwable;

/**
 * Single entry point for issuing/editing/deleting cards. Both the quick
 * AJAX flow (referee/save_card.php) and the detailed form
 * (referee/record_card.php) go through this class, which is what makes
 * `cards` the one place card data lives (previously save_card.php and
 * record_card.php wrote to two different tables with no cross-reference).
 *
 * The referee provides the Card Reason Title (short, required) and an
 * optional detailed explanation. AI's role is to write a deep, thorough
 * explanation of the incident (ai_summary) grounded in both - it never
 * generates the title. issueCard() always persists the card immediately;
 * AI summary generation, the discipline case, and the team notification
 * are a separate step (completeCardProcessing) so a slow or failing AI
 * call never blocks the card from saving, and callers that care about
 * response time (the live quick-card AJAX flow) can defer that step until
 * after the HTTP response has been sent.
 */
class CardService
{
    private const SANCTIONS = [
        'violent conduct' => ['article' => 'ART-15', 'sanction' => '5 game suspension'],
        'spitting' => ['article' => 'ART-16', 'sanction' => '10 game suspension'],
        'abusive language' => ['article' => 'ART-14', 'sanction' => '3 game suspension'],
        'serious foul play' => ['article' => 'ART-17', 'sanction' => '2 game suspension'],
        'second yellow card' => ['article' => 'ART-1', 'sanction' => '1 game suspension'],
        'reckless tackle' => ['article' => 'ART-12', 'sanction' => '2 game suspension'],
        'denial of an obvious goal-scoring opportunity' => ['article' => 'ART-12', 'sanction' => '1 game suspension'],
        'deliberate handball' => ['article' => 'ART-12', 'sanction' => '1 game suspension'],
        'persistent infringement' => ['article' => 'ART-12', 'sanction' => '1 game suspension'],
    ];

    public function __construct(
        private PDO $pdo,
        private CardRepository $cards,
        private MatchRepository $matches,
        private TeamMemberRepository $members,
        private CardStatsService $stats,
        private ?AiSummaryGenerator $ai = null,
        private ?NotificationService $notifications = null,
    ) {
    }

    /**
     * Validates and persists the card. When $deferAiProcessing is true, the
     * caller is responsible for calling completeCardProcessing() afterwards
     * (typically after the HTTP response has already been flushed to the
     * referee, to minimize perceived response time) - the returned card
     * will have ai_summary_status='pending' until then.
     */
    public function issueCard(array $input, bool $deferAiProcessing = false): CardResult
    {
        $memberId = (int) ($input['member_id'] ?? 0);
        $matchId = (int) ($input['match_id'] ?? 0);
        $cardType = $input['card_type'] ?? '';
        $reasonTitle = trim((string) ($input['card_reason_title'] ?? ''));
        $reasonDetail = trim((string) ($input['card_reason_detail'] ?? ''));
        $reasonDetail = $reasonDetail === '' ? null : $reasonDetail;
        $cardTime = self::normalizeCardTime($input['card_time'] ?? null);

        if (!$memberId || !$matchId) {
            return CardResult::fail('Player and match are required.');
        }
        if (!in_array($cardType, ['yellow', 'red'], true)) {
            return CardResult::fail('Invalid card type.');
        }
        if ($reasonTitle === '') {
            return CardResult::fail('Card Reason Title is required.');
        }

        $match = $this->matches->find($matchId);
        if (!$match) {
            return CardResult::fail('Match not found.');
        }
        if ($match['status'] === 'completed') {
            return CardResult::fail('Match is finished; cards can no longer be recorded.');
        }

        $player = $this->members->find($memberId);
        if (!$player) {
            return CardResult::fail('Player not found.');
        }
        if ($this->members->isSuspended($player)) {
            return CardResult::fail('This player is suspended and cannot take part in a match.');
        }

        // A player who already has a red card (direct or via a second
        // yellow) in THIS match has been sent off and cannot receive any
        // further card in it - enforces "no more than one red card and no
        // more than two yellow cards per match".
        if ($this->cards->hasSendOffInMatch($memberId, $matchId)) {
            return CardResult::fail('This player has already been sent off in this match and cannot receive further cards.');
        }

        // A second yellow card in the same match is recorded as the
        // red-equivalent `double_yellow` type (sending the player off),
        // scoped to this match only - a yellow from an earlier match must
        // never carry over and trigger this.
        $storedType = $cardType;
        if ($cardType === 'yellow' && $this->cards->countActiveByMemberMatchAndType($memberId, $matchId, 'yellow') >= 1) {
            $storedType = 'double_yellow';
        }

        try {
            $this->pdo->beginTransaction();

            $cardId = $this->cards->create([
                'member_id' => $memberId,
                'card_type' => $storedType,
                'match_id' => $matchId,
                'card_time' => $cardTime,
                'card_reason_title' => $reasonTitle,
                'card_reason_detail' => $reasonDetail,
            ]);
            $this->cards->update($cardId, ['ai_summary_status' => 'pending']);

            $this->stats->recalculate($memberId);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return CardResult::fail('Failed to save card: ' . $e->getMessage());
        }

        $card = $this->cards->find($cardId);

        if (!$deferAiProcessing) {
            $card = $this->completeCardProcessing($cardId) ?? $card;
        }

        return CardResult::ok($card);
    }

    /**
     * Generates the deep AI explanation (ai_summary), creates the
     * discipline case for a send-off (if not already created), and
     * notifies the team - all best-effort side effects of an
     * already-saved card. Safe to call exactly once per card; safe to
     * retry if it was never completed.
     */
    public function completeCardProcessing(int $cardId): ?array
    {
        $card = $this->cards->find($cardId);
        if (!$card || $card['deleted_at']) {
            return null;
        }

        if ($this->ai) {
            $card = $this->generateSummary($cardId, $card['card_reason_title'], $card['card_reason_detail']) ?? $card;
        }

        $match = $this->matches->find((int) $card['match_id']);
        $player = $this->members->find((int) $card['member_id']);
        if (!$match || !$player) {
            return $card;
        }

        // Discipline cases are specifically for send-offs (a straight red,
        // or a second yellow) - not every yellow card warrants one.
        $isSendOff = in_array($card['card_type'], ['red', 'double_yellow'], true);
        if ($isSendOff && !$this->disciplineCaseExists($cardId)) {
            $this->createDisciplineCase(
                $cardId,
                (int) $player['team'],
                (int) $card['member_id'],
                $card['card_reason_title'],
                $card['card_reason_detail']
            );
        }

        // The team is notified for every card - yellow or red.
        if ($this->notifications) {
            try {
                $this->notifications->notifyCardIssued($card, $match, $player);
            } catch (Throwable $e) {
                error_log('Card notification failed for card ' . $cardId . ': ' . $e->getMessage());
            }
        }

        return $card;
    }

    public function updateCard(int $cardId, array $input): CardResult
    {
        $existing = $this->cards->find($cardId);
        if (!$existing || $existing['deleted_at']) {
            return CardResult::fail('Card not found.');
        }
        if ($this->matches->isCompleted((int) $existing['match_id'])) {
            return CardResult::fail('Match is finished; cards can no longer be edited.');
        }

        $reasonTitle = trim((string) ($input['card_reason_title'] ?? $existing['card_reason_title']));
        if ($reasonTitle === '') {
            return CardResult::fail('Card Reason Title is required.');
        }
        $reasonDetail = array_key_exists('card_reason_detail', $input)
            ? (trim((string) $input['card_reason_detail']) ?: null)
            : $existing['card_reason_detail'];

        try {
            $this->pdo->beginTransaction();
            $this->cards->update($cardId, [
                'card_reason_title' => $reasonTitle,
                'card_reason_detail' => $reasonDetail,
                'card_time' => array_key_exists('card_time', $input)
                    ? self::normalizeCardTime($input['card_time'])
                    : $existing['card_time'],
            ]);
            $this->stats->recalculate((int) $existing['member_id']);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return CardResult::fail('Failed to update card: ' . $e->getMessage());
        }

        $card = $this->cards->find($cardId);

        $titleChanged = $reasonTitle !== $existing['card_reason_title'];
        $detailChanged = $reasonDetail !== $existing['card_reason_detail'];
        if (($titleChanged || $detailChanged) && $this->ai) {
            $card = $this->generateSummary($cardId, $reasonTitle, $reasonDetail) ?? $card;
        }

        return CardResult::ok($card);
    }

    public function deleteCard(int $cardId): CardResult
    {
        $existing = $this->cards->find($cardId);
        if (!$existing || $existing['deleted_at']) {
            return CardResult::fail('Card not found.');
        }
        if ($this->matches->isCompleted((int) $existing['match_id'])) {
            return CardResult::fail('Match is finished; cards can no longer be deleted.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->cards->softDelete($cardId);
            $this->stats->recalculate((int) $existing['member_id']);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return CardResult::fail('Failed to delete card: ' . $e->getMessage());
        }

        return CardResult::ok($existing);
    }

    /** Regenerates just the AI summary for an existing card (admin action). */
    public function regenerateSummary(int $cardId): CardResult
    {
        $existing = $this->cards->find($cardId);
        if (!$existing) {
            return CardResult::fail('Card not found.');
        }
        if (!$existing['card_reason_title']) {
            return CardResult::fail('This card has no Card Reason Title to summarize.');
        }
        if (!$this->ai) {
            return CardResult::fail('AI summary service is not configured.');
        }

        $card = $this->generateSummary($cardId, $existing['card_reason_title'], $existing['card_reason_detail']);
        return $card ? CardResult::ok($card) : CardResult::fail('AI summary generation failed.');
    }

    private function generateSummary(int $cardId, string $cardReasonTitle, ?string $detail): ?array
    {
        try {
            $result = $this->ai->summarize($cardReasonTitle, $detail);
            $this->cards->update($cardId, [
                'ai_summary' => $result->text,
                'ai_summary_status' => $result->status,
                'ai_summary_error' => $result->error,
                'ai_summary_generated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            $this->cards->update($cardId, [
                'ai_summary_status' => 'failed',
                'ai_summary_error' => $e->getMessage(),
            ]);
        }
        return $this->cards->find($cardId);
    }

    /**
     * `cards.card_time` is a short label for the match minute a card was
     * issued at (e.g. "45", "90+3") - not a real time-of-day.
     */
    private static function normalizeCardTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : substr($value, 0, 10);
    }

    private function disciplineCaseExists(int $cardId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM ai_discipline_cases WHERE card_id = ?');
        $stmt->execute([$cardId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function createDisciplineCase(int $cardId, int $teamId, int $memberId, string $reasonTitle, ?string $reasonDetail): void
    {
        $key = strtolower($reasonTitle);
        $lookup = self::SANCTIONS[$key] ?? ['article' => 'ART-1', 'sanction' => 'To be determined'];

        $stmt = $this->pdo->prepare(
            'INSERT INTO ai_discipline_cases (team_id, member_id, card_id, offence_description, article_code, sanction, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $teamId,
            $memberId,
            $cardId,
            $reasonDetail ?: $reasonTitle,
            $lookup['article'],
            $lookup['sanction'],
            'pending',
        ]);
    }
}
