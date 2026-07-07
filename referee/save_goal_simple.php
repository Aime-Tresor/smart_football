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

// Determine which team scored and calculate new scores
$isTeam1 = ($team_id == $match['team1_id']);
$currentTeam1Goals = (int)($match['team1_goal'] ?? 0);
$currentTeam2Goals = (int)($match['team2_goal'] ?? 0);

$newTeam1Goals = $currentTeam1Goals + ($isTeam1 ? 1 : 0);
$newTeam2Goals = $currentTeam2Goals + ($isTeam1 ? 0 : 1);

// Start transaction for data consistency
$conn->autocommit(false);

try {
    // Update match score in the match table
    $updateScoreSql = "UPDATE `match` SET team1_goal = ?, team2_goal = ? WHERE id = ?";
    $updateScoreStmt = $conn->prepare($updateScoreSql);
    
    if (!$updateScoreStmt) {
        throw new Exception('Failed to prepare score update query: ' . $conn->error);
    }

    $updateScoreStmt->bind_param("iii", $newTeam1Goals, $newTeam2Goals, $match_id);
    
    if (!$updateScoreStmt->execute()) {
        throw new Exception('Failed to update match score: ' . $updateScoreStmt->error);
    }

    // Optionally, try to log the goal in match_day_reports table if it exists
    $logGoalSql = "INSERT INTO match_day_reports (team_member, team, goal, goal_min, card, card_min, week) 
                   VALUES (?, ?, '1', ?, '', '', 
                   (SELECT week FROM `match` WHERE id = ? LIMIT 1))";
    $logGoalStmt = $conn->prepare($logGoalSql);
    
    if ($logGoalStmt && $player_id) {
        $logGoalStmt->bind_param("iisi", $player_id, $team_id, $minute, $match_id);
        // Try to execute, but don't fail if this table doesn't work properly
        @$logGoalStmt->execute();
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

echo json_encode([
    'success' => true,
    'message' => $message,
    'new_score' => [
        'team1' => $newTeam1Goals,
        'team2' => $newTeam2Goals
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
