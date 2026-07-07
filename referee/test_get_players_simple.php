<?php
session_start();

// Set a test referee ID if not set
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$team_id = intval($_GET['team_id'] ?? 0);
$match_id = intval($_GET['match_id'] ?? 0);

echo json_encode([
    'success' => true,
    'debug' => [
        'team_id' => $team_id,
        'match_id' => $match_id,
        'session_referee_id' => $_SESSION['referee_id'],
        'get_params' => $_GET
    ],
    'message' => 'Simple test successful'
]);

$conn->close();
?>
