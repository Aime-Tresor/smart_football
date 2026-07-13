<?php

require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['match_id'])) {
    die('Match ID required.');
}

if (!isset($_SESSION['referee_id'])) {
    header('Location: ../referee.php');
    exit;
}

$matchId = (int) $_POST['match_id'];
$confirmZeroScores = isset($_POST['confirm_zero_scores']) && $_POST['confirm_zero_scores'] === '1';

$result = ServiceFactory::matchCompletionService()->finish($matchId, [
    'type' => 'referee',
    'id' => (int) $_SESSION['referee_id'],
], [
    'confirm_zero_scores' => $confirmZeroScores,
]);

$backTo = $_SERVER['HTTP_REFERER'] ?? 'cads.php';

if ($result->success) {
    // Both keys are set: `card_message` is what view_match.php displays,
    // `success_message`/`error_message` are what index.php displays -
    // different pages, different flash-message conventions.
    $_SESSION['card_message'] = 'Match finished successfully.';
    $_SESSION['success_message'] = 'Match finished successfully.';
    header('Location: ' . $backTo);
    exit;
}

if ($result->needsConfirmation) {
    header('Location: view_match.php?match_id=' . $matchId . '&confirm_finish=1');
    exit;
}

$_SESSION['card_message'] = 'Could not finish match: ' . $result->error;
$_SESSION['error_message'] = 'Could not finish match: ' . $result->error;
header('Location: ' . $backTo);
exit;
