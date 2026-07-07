<?php
session_start();

// Dummy login fallback for testing
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$referee_id = $_SESSION['referee_id'];
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 1; // Default to match 1 for testing

echo "<h2>Debug Information</h2>";
echo "<p>Referee ID: $referee_id</p>";
echo "<p>Match ID: $match_id</p>";

// Test the match query
$sql = "
    SELECT 
        m.id,
        m.match_date,
        m.match_time,
        m.status,
        m.team1_goal,
        m.team2_goal,
        m.stadium,
        t1.team_id AS team1_id,
        t1.name AS team1_name,
        t1.logon AS team1_logo,
        t2.team_id AS team2_id,
        t2.name AS team2_name,
        t2.logon AS team2_logo
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    WHERE m.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $match = $result->fetch_assoc();
    echo "<h3>Match Found:</h3>";
    echo "<pre>" . print_r($match, true) . "</pre>";
} else {
    echo "<p style='color: red;'>No match found with ID: $match_id</p>";
}

// Test weekly_fixtures table
echo "<h3>Weekly Fixtures Check:</h3>";
$wfSql = "SELECT * FROM weekly_fixtures WHERE match_id = ?";
$wfStmt = $conn->prepare($wfSql);
$wfStmt->bind_param("i", $match_id);
$wfStmt->execute();
$wfResult = $wfStmt->get_result();

if ($wfResult->num_rows > 0) {
    $wf = $wfResult->fetch_assoc();
    echo "<pre>" . print_r($wf, true) . "</pre>";
} else {
    echo "<p style='color: red;'>No weekly fixture found for match ID: $match_id</p>";
}

// Test team players
if (isset($match)) {
    echo "<h3>Team 1 Players (Team ID: {$match['team1_id']}):</h3>";
    $playersSql = "SELECT * FROM team_members WHERE team_id = ? ORDER BY number ASC";
    $playersStmt = $conn->prepare($playersSql);
    $playersStmt->bind_param("i", $match['team1_id']);
    $playersStmt->execute();
    $playersResult = $playersStmt->get_result();
    
    if ($playersResult->num_rows > 0) {
        while ($player = $playersResult->fetch_assoc()) {
            echo "<p>#{$player['number']} {$player['fname']} {$player['lname']} - Y:{$player['yellow']} R:{$player['red']}</p>";
        }
    } else {
        echo "<p style='color: red;'>No players found for team 1</p>";
    }
    
    echo "<h3>Team 2 Players (Team ID: {$match['team2_id']}):</h3>";
    $playersStmt->bind_param("i", $match['team2_id']);
    $playersStmt->execute();
    $playersResult = $playersStmt->get_result();
    
    if ($playersResult->num_rows > 0) {
        while ($player = $playersResult->fetch_assoc()) {
            echo "<p>#{$player['number']} {$player['fname']} {$player['lname']} - Y:{$player['yellow']} R:{$player['red']}</p>";
        }
    } else {
        echo "<p style='color: red;'>No players found for team 2</p>";
    }
}

$conn->close();
?>

<h3>Test Links:</h3>
<a href="view_match.php?match_id=1">View Match 1</a><br>
<a href="debug_match.php?match_id=1">Debug Match 1</a><br>
<a href="get_team_players.php?team_id=1&match_id=1">Test Get Team Players (Team 1)</a><br>
