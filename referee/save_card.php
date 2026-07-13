<?php

require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

ob_start();
session_start();

// Validate referee authorization (only referees can give cards)
if (!isset($_SESSION['referee_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Unauthorized: Only referees can issue cards']));
}

if (!isset($_POST['player_id'], $_POST['card'])) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Missing required data']));
}

$reasonTitle = trim($_POST['card_reason_title'] ?? '');
if ($reasonTitle === '') {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Card Reason Title is required.']));
}

$isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

// The deep AI summary, discipline-case creation and the team notification
// are deferred until after the response is sent (see below) so the
// referee's quick-card action returns as fast as possible - this app has
// no job queue, so this is the lightweight "poor man's async" pattern for
// a plain Apache/mod_php stack (no fastcgi_finish_request available).
$result = ServiceFactory::cardService()->issueCard([
    'member_id' => $_POST['player_id'],
    'match_id' => $_POST['match_id'] ?? null,
    'card_type' => $_POST['card'],
    'card_time' => $_POST['card_time'] ?? null,
    'card_reason_title' => $reasonTitle,
    'card_reason_detail' => $_POST['card_reason_detail'] ?? null,
], deferAiProcessing: $isAjax);

if (!$result->success) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $result->error]);
    exit;
}

$card = $result->card;

if ($isAjax) {
    $payload = json_encode([
        'success' => true,
        'message' => ucfirst($card['card_type'] === 'double_yellow' ? 'red' : $card['card_type']) . ' card recorded successfully.',
        'card_id' => (int) $card['card_id'],
        'card_type' => $card['card_type'],
        'card_reason_title' => $card['card_reason_title'],
        'ai_summary_status' => $card['ai_summary_status'],
    ]);

    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($payload));
    header('Connection: close');
    echo $payload;

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        ignore_user_abort(true);
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }

    // The referee's browser already has its response; this runs after.
    ServiceFactory::cardService()->completeCardProcessing((int) $card['card_id']);
} else {
    ServiceFactory::cardService()->completeCardProcessing((int) $card['card_id']);
    $_SESSION['card_message'] = 'Card recorded successfully.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'view_match.php'));
    exit;
}
