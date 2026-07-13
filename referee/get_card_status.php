<?php

require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['referee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$cardId = (int) ($_GET['card_id'] ?? 0);
if (!$cardId) {
    echo json_encode(['success' => false, 'message' => 'card_id is required']);
    exit;
}

$card = ServiceFactory::cardRepository()->find($cardId);
if (!$card) {
    echo json_encode(['success' => false, 'message' => 'Card not found']);
    exit;
}

echo json_encode([
    'success' => true,
    'card_id' => (int) $card['card_id'],
    'card_reason_title' => $card['card_reason_title'],
    'ai_summary' => $card['ai_summary'],
    'ai_summary_status' => $card['ai_summary_status'],
]);
