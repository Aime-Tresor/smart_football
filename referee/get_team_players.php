<?php
session_start();

// Debug: Log the start of the request
error_log("get_team_players.php - Request started");

// Check if referee is logged in (set fallback for testing)
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1; // Fallback for testing
    error_log("get_team_players.php - Set fallback referee_id = 1");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$referee_id = $_SESSION['referee_id'];
$team_id = intval($_GET['team_id'] ?? 0);
$match_id = intval($_GET['match_id'] ?? 0);

error_log("get_team_players.php - Parameters: team_id=$team_id, match_id=$match_id, referee_id=$referee_id");

if ($team_id === 0 || $match_id === 0) {
    error_log("get_team_players.php - Invalid parameters");
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Verify match exists (simplified verification for now)
$verify_sql = "SELECT 1 FROM `match` WHERE id = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("i", $match_id);
$verify_stmt->execute();

if ($verify_stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Match not found']);
    exit();
}

// Get team name for logging
$team_sql = "SELECT name FROM team WHERE team_id = ?";
$team_stmt = $conn->prepare($team_sql);
$team_stmt->bind_param("i", $team_id);
$team_stmt->execute();
$team_result = $team_stmt->get_result();

error_log("get_team_players.php - Team query result rows: " . $team_result->num_rows);

if ($team_result->num_rows === 0) {
    error_log("get_team_players.php - Team not found for team_id: $team_id");
    echo json_encode(['success' => false, 'message' => 'Team not found']);
    exit();
}

$team_name = $team_result->fetch_assoc()['name'];
error_log("get_team_players.php - Team name found: $team_name");

// Get team players with their card information
// Note: team_members.team column stores team_id as string, not team name
$players_sql = "
    SELECT
        tm.member_id,
        tm.fname,
        tm.lname,
        tm.number,
        tm.position,
        tm.yellow,
        tm.red,
        tm.double_yellow,
        tm.role_in_team
    FROM team_members tm
    WHERE tm.team = ? AND tm.role_in_team = 'player'
    ORDER BY tm.number ASC, tm.fname ASC, tm.lname ASC
";

$players_stmt = $conn->prepare($players_sql);
if (!$players_stmt) {
    error_log("get_team_players.php - Failed to prepare players query: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
    exit();
}

// Bind team_id as string since team_members.team column stores team_id as string
$players_stmt->bind_param("s", $team_id);
if (!$players_stmt->execute()) {
    error_log("get_team_players.php - Failed to execute players query: " . $players_stmt->error);
    echo json_encode(['success' => false, 'message' => 'Database query execution failed']);
    exit();
}

$players_result = $players_stmt->get_result();

// Debug logging
error_log("get_team_players.php - Team ID: $team_id, Team Name: $team_name, Players found: " . $players_result->num_rows);

$players = [];
while ($row = $players_result->fetch_assoc()) {
    // Create cards array based on database values
    $cards = [];
    
    // Add yellow cards
    for ($i = 0; $i < intval($row['yellow']); $i++) {
        $cards[] = 'yellow';
    }
    
    // Add red cards
    for ($i = 0; $i < intval($row['red']); $i++) {
        $cards[] = 'red';
    }
    
    // Add double yellow (red from 2 yellows)
    for ($i = 0; $i < intval($row['double_yellow']); $i++) {
        $cards[] = 'red';
    }
    
    $players[] = [
        'member_id' => intval($row['member_id']),
        'fname' => $row['fname'],
        'lname' => $row['lname'],
        'number' => $row['number'],
        'position' => $row['position'],
        'role_in_team' => $row['role_in_team'],
        'cards' => $cards,
        'yellow_count' => intval($row['yellow']),
        'red_count' => intval($row['red']) + intval($row['double_yellow'])
    ];
}

// Goals data removed - not needed for player loading
$goals = [];

echo json_encode([
    'success' => true,
    'players' => $players,
    'goals' => $goals,
    'team_name' => $team_name,
    'team_id' => $team_id
]);

$conn->close();
?>

