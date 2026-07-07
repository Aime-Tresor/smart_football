<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Set fallback referee ID if not logged in
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1; // Fallback for testing
}

$referee_id = $_SESSION['referee_id'];

// Get form data
$match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : 0;
$team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
$player_id = isset($_POST['player_id']) && $_POST['player_id'] !== '' ? (int)$_POST['player_id'] : null;
$minute = isset($_POST['minute']) ? $_POST['minute'] : '';
$goal_type = isset($_POST['goal_type']) ? $_POST['goal_type'] : 'regular';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Validate input
if ($match_id <= 0 || $team_id <= 0 || empty($minute)) {
    die(json_encode(['success' => false, 'message' => 'Invalid input data. Match ID, Team ID, and Minute are required.']));
}

// Verify match exists and get match info
$matchSql = "SELECT team1_id, team2_id, team1_goal, team2_goal, status FROM `match` WHERE id = ?";
$matchStmt = $conn->prepare($matchSql);
if (!$matchStmt) {
    die(json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]));
}

$matchStmt->bind_param("i", $match_id);
$matchStmt->execute();
$matchResult = $matchStmt->get_result();

if ($matchResult->num_rows === 0) {
    die(json_encode(['success' => false, 'message' => 'Match not found']));
}

$match = $matchResult->fetch_assoc();

// Verify team belongs to this match
if ($team_id != $match['team1_id'] && $team_id != $match['team2_id']) {
    die(json_encode(['success' => false, 'message' => 'Team does not belong to this match']));
}

// Start transaction for data consistency
$conn->autocommit(false);

try {
    // Insert goal record into individual_goals table
    $insertGoalSql = "INSERT INTO individual_goals (match_id, team_id, player_id, goal_minute, goal_type, description, created_by, created_at)
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $insertGoalStmt = $conn->prepare($insertGoalSql);

    if (!$insertGoalStmt) {
        throw new Exception('Failed to prepare goal insertion query: ' . $conn->error);
    }

    $insertGoalStmt->bind_param("iiisssi", $match_id, $team_id, $player_id, $minute, $goal_type, $description, $referee_id);

    if (!$insertGoalStmt->execute()) {
        throw new Exception('Failed to insert goal record: ' . $insertGoalStmt->error);
    }

    $goal_id = $conn->insert_id;

    // The database triggers will automatically update match totals
    // But let's also manually update to ensure consistency
    $team1Goals = 0;
    $team2Goals = 0;

    // Count goals for team 1
    $countSql1 = "SELECT COUNT(*) as count FROM individual_goals WHERE match_id = ? AND team_id = ?";
    $countStmt1 = $conn->prepare($countSql1);
    $countStmt1->bind_param("ii", $match_id, $match['team1_id']);
    $countStmt1->execute();
    $team1Goals = $countStmt1->get_result()->fetch_assoc()['count'];

    // Count goals for team 2
    $countStmt2 = $conn->prepare($countSql1);
    $countStmt2->bind_param("ii", $match_id, $match['team2_id']);
    $countStmt2->execute();
    $team2Goals = $countStmt2->get_result()->fetch_assoc()['count'];

    // Update match totals
    $updateScoreSql = "UPDATE `match` SET team1_goal = ?, team2_goal = ? WHERE id = ?";
    $updateScoreStmt = $conn->prepare($updateScoreSql);
    $updateScoreStmt->bind_param("iii", $team1Goals, $team2Goals, $match_id);

    if (!$updateScoreStmt->execute()) {
        throw new Exception('Failed to update match score: ' . $updateScoreStmt->error);
    }

    // Commit transaction
    $conn->commit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    die(json_encode(['success' => false, 'message' => 'Error adding goal: ' . $e->getMessage()]));
}

// Get team name for response
$teamSql = "SELECT name FROM team WHERE team_id = ?";
$teamStmt = $conn->prepare($teamSql);
$teamStmt->bind_param("i", $team_id);
$teamStmt->execute();
$teamResult = $teamStmt->get_result();
$teamName = $teamResult->fetch_assoc()['name'] ?? 'Unknown Team';

// Get player name if provided
$playerName = '';
if ($player_id) {
    $playerSql = "SELECT fname, lname, number FROM team_members WHERE member_id = ?";
    $playerStmt = $conn->prepare($playerSql);
    $playerStmt->bind_param("i", $player_id);
    $playerStmt->execute();
    $playerResult = $playerStmt->get_result();
    if ($playerResult->num_rows > 0) {
        $player = $playerResult->fetch_assoc();
        $playerName = trim($player['fname'] . ' ' . $player['lname']);
        if ($player['number']) {
            $playerName = "#" . $player['number'] . " " . $playerName;
        }
    }
}

// Prepare success message
$message = "⚽ Goal added for {$teamName}";
if ($playerName) {
    $message .= " (scored by {$playerName})";
}
$message .= " at minute {$minute}";

if ($goal_type !== 'regular') {
    $message .= " (" . ucfirst(str_replace('_', ' ', $goal_type)) . ")";
}

// Get updated scores for response
$updatedMatchSql = "SELECT team1_goal, team2_goal FROM `match` WHERE id = ?";
$updatedMatchStmt = $conn->prepare($updatedMatchSql);
$updatedMatchStmt->bind_param("i", $match_id);
$updatedMatchStmt->execute();
$updatedMatch = $updatedMatchStmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'message' => $message,
    'goal_id' => $goal_id,
    'new_score' => [
        'team1' => (int)$updatedMatch['team1_goal'],
        'team2' => (int)$updatedMatch['team2_goal']
    ],
    'goal_details' => [
        'team_name' => $teamName,
        'player_name' => $playerName,
        'minute' => $minute,
        'goal_type' => $goal_type,
        'description' => $description
    ]
]);

$conn->close();
?>
