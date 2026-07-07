<?php
// Test script to debug player loading issues
session_start();

// Set fallback referee ID
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Player Loading Debug Test</h2>";

// Test parameters
$team_id = 4; // Kiyovu fc
$match_id = 1;

echo "<h3>Testing with Team ID: $team_id, Match ID: $match_id</h3>";

// Get team name
$team_sql = "SELECT name FROM team WHERE team_id = ?";
$team_stmt = $conn->prepare($team_sql);
$team_stmt->bind_param("i", $team_id);
$team_stmt->execute();
$team_result = $team_stmt->get_result();

if ($team_result->num_rows === 0) {
    echo "<p style='color: red;'>❌ Team not found for team_id: $team_id</p>";
    exit();
}

$team_name = $team_result->fetch_assoc()['name'];
echo "<p style='color: green;'>✅ Team name found: <strong>$team_name</strong></p>";

// Test the corrected query
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
        tm.role_in_team,
        tm.team
    FROM team_members tm
    WHERE tm.team = ? AND tm.role_in_team = 'player'
    ORDER BY tm.number ASC, tm.fname ASC, tm.lname ASC
";

$players_stmt = $conn->prepare($players_sql);
if (!$players_stmt) {
    echo "<p style='color: red;'>❌ Failed to prepare players query: " . $conn->error . "</p>";
    exit();
}

// Bind team_id as string since team_members.team column stores team_id as string
$players_stmt->bind_param("s", $team_id);
if (!$players_stmt->execute()) {
    echo "<p style='color: red;'>❌ Failed to execute players query: " . $players_stmt->error . "</p>";
    exit();
}

$players_result = $players_stmt->get_result();
echo "<p style='color: green;'>✅ Players query executed successfully</p>";
echo "<p><strong>Players found: " . $players_result->num_rows . "</strong></p>";

if ($players_result->num_rows > 0) {
    echo "<h3>Players List:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Number</th><th>Position</th><th>Team</th><th>Yellow</th><th>Red</th><th>Role</th></tr>";
    
    while ($row = $players_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['member_id'] . "</td>";
        echo "<td>" . $row['fname'] . " " . $row['lname'] . "</td>";
        echo "<td>" . ($row['number'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['position'] ?: 'N/A') . "</td>";
        echo "<td>" . $row['team'] . "</td>";
        echo "<td>" . $row['yellow'] . "</td>";
        echo "<td>" . $row['red'] . "</td>";
        echo "<td>" . $row['role_in_team'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No players found for this team</p>";
    
    // Let's check what's in the team_members table
    echo "<h3>All team_members records:</h3>";
    $all_sql = "SELECT member_id, fname, lname, team, role_in_team FROM team_members";
    $all_result = $conn->query($all_sql);
    
    if ($all_result && $all_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Team</th><th>Role</th></tr>";
        
        while ($row = $all_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['member_id'] . "</td>";
            echo "<td>" . $row['fname'] . " " . $row['lname'] . "</td>";
            echo "<td>" . $row['team'] . "</td>";
            echo "<td>" . $row['role_in_team'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test the API endpoint
echo "<h3>Testing API Endpoint:</h3>";
$api_url = "get_team_players.php?team_id=$team_id&match_id=$match_id";
echo "<p>API URL: <a href='$api_url' target='_blank'>$api_url</a></p>";

$conn->close();
?>
