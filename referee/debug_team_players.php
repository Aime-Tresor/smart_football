<?php
session_start();

// Set a test referee ID if not set
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Debug Team Players</h1>";

// Get a test match
$match_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
              FROM `match` m 
              JOIN team t1 ON m.team1_id = t1.team_id 
              JOIN team t2 ON m.team2_id = t2.team_id 
              LIMIT 1";
$match_result = $conn->query($match_sql);

if ($match_result && $match_result->num_rows > 0) {
    $match = $match_result->fetch_assoc();
    
    echo "<h2>Test Match Found:</h2>";
    echo "<p>Match ID: {$match['id']}</p>";
    echo "<p>Team 1: {$match['team1_name']} (ID: {$match['team1_id']})</p>";
    echo "<p>Team 2: {$match['team2_name']} (ID: {$match['team2_id']})</p>";
    
    // Test Team 1 players
    echo "<h3>Team 1 Players Test:</h3>";
    $team1_sql = "SELECT * FROM team_members WHERE team = ? AND role_in_team = 'player'";
    $team1_stmt = $conn->prepare($team1_sql);
    $team1_stmt->bind_param("s", $match['team1_name']);
    $team1_stmt->execute();
    $team1_result = $team1_stmt->get_result();
    
    echo "<p>Query: SELECT * FROM team_members WHERE team = '{$match['team1_name']}' AND role_in_team = 'player'</p>";
    echo "<p>Players found: " . $team1_result->num_rows . "</p>";
    
    if ($team1_result->num_rows > 0) {
        echo "<ul>";
        while ($player = $team1_result->fetch_assoc()) {
            echo "<li>#{$player['number']} {$player['fname']} {$player['lname']} (Y:{$player['yellow']}, R:{$player['red']})</li>";
        }
        echo "</ul>";
    }
    
    // Test Team 2 players
    echo "<h3>Team 2 Players Test:</h3>";
    $team2_sql = "SELECT * FROM team_members WHERE team = ? AND role_in_team = 'player'";
    $team2_stmt = $conn->prepare($team2_sql);
    $team2_stmt->bind_param("s", $match['team2_name']);
    $team2_stmt->execute();
    $team2_result = $team2_stmt->get_result();
    
    echo "<p>Query: SELECT * FROM team_members WHERE team = '{$match['team2_name']}' AND role_in_team = 'player'</p>";
    echo "<p>Players found: " . $team2_result->num_rows . "</p>";
    
    if ($team2_result->num_rows > 0) {
        echo "<ul>";
        while ($player = $team2_result->fetch_assoc()) {
            echo "<li>#{$player['number']} {$player['fname']} {$player['lname']} (Y:{$player['yellow']}, R:{$player['red']})</li>";
        }
        echo "</ul>";
    }
    
    // Test the API calls
    echo "<h3>API Test Links:</h3>";
    echo "<p><a href='get_team_players.php?team_id={$match['team1_id']}&match_id={$match['id']}' target='_blank'>Test Team 1 API</a></p>";
    echo "<p><a href='get_team_players.php?team_id={$match['team2_id']}&match_id={$match['id']}' target='_blank'>Test Team 2 API</a></p>";
    
} else {
    echo "<p style='color: red;'>No matches found in database</p>";
}

// Also check team_members table structure
echo "<h3>Team Members Table Structure:</h3>";
$structure_sql = "DESCRIBE team_members";
$structure_result = $conn->query($structure_sql);

if ($structure_result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>
