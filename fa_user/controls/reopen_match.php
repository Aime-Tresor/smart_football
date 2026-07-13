<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\ServiceFactory;

session_start();

if (!isset($_SESSION['fa_user']) || empty($_SESSION['fa_user'])) {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['match_id'])) {
    $_SESSION['error'] = 'Match id is required.';
    header('Location: ../fixture.php');
    exit;
}

$matchId = (int) $_POST['match_id'];

$actorId = ServiceFactory::pdo()
    ->prepare('SELECT id FROM fa_user WHERE username = ?');
$actorId->execute([$_SESSION['fa_user']]);

$result = ServiceFactory::matchCompletionService()->reopen($matchId, [
    'type' => 'fa_user',
    'id' => (int) ($actorId->fetchColumn() ?: 0),
]);

$_SESSION[$result->success ? 'success' : 'error'] = $result->success
    ? 'Match reopened. Referees can record events again.'
    : ('Failed to reopen match: ' . $result->error);

header('Location: ../fixture.php');
exit;
