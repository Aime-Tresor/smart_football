<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\ServiceFactory;

session_start();

// Same admin gate used by every other fa_user/* page in this app.
if (!isset($_SESSION['fa_user']) || empty($_SESSION['fa_user'])) {
    header('Location: ../../login.php');
    exit;
}

$appealId = (int) ($_GET['appeal_id'] ?? $_POST['appeal_id'] ?? 0);
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'discipline_committee_dashboard.php';

// Guard against an open redirect - only allow a bare filename within fa_user/.
if (!preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $redirect)) {
    $redirect = 'discipline_committee_dashboard.php';
}

if ($appealId > 0) {
    $result = ServiceFactory::appealDecisionService()->regenerate($appealId);
    $_SESSION[$result->status === 'completed' ? 'success' : 'error'] = $result->status === 'completed'
        ? 'AI summary regenerated successfully.'
        : ('Failed to regenerate AI summary: ' . $result->error);
} else {
    $_SESSION['error'] = 'Appeal id is required.';
}

header('Location: ../' . $redirect);
exit;
