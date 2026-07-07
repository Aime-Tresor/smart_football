<?php
// Test script to debug match data
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Match Data Debug Test</h2>";

// Get all matches
echo "<h3>Available Matches:</h3>";
$matches_sql = "
    SELECT
        m.id,
        m.match_date,
        m.match_time,
        m.status,
        m.team1_goal,
        m.team2_goal,
        t1.name AS team1_name,
        t2.name AS team2_name
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    ORDER BY m.id
";

$matches_result = $conn->query($matches_sql);

if ($matches_result && $matches_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Match ID</th><th>Date</th><th>Time</th><th>Team 1</th><th>Team 2</th><th>Score</th><th>Status</th><th>Action</th></tr>";
    
    while ($row = $matches_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['match_date'] . "</td>";
        echo "<td>" . $row['match_time'] . "</td>";
        echo "<td>" . $row['team1_name'] . "</td>";
        echo "<td>" . $row['team2_name'] . "</td>";
        echo "<td>" . ($row['team1_goal'] ?? 0) . " - " . ($row['team2_goal'] ?? 0) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td><a href='view_match.php?match_id=" . $row['id'] . "' target='_blank'>View Match</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No matches found</p>";
}

// Get all teams
echo "<h3>Available Teams:</h3>";
$teams_sql = "SELECT team_id, name, logon FROM team ORDER BY team_id";
$teams_result = $conn->query($teams_sql);

if ($teams_result && $teams_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Team ID</th><th>Name</th><th>Logo</th></tr>";
    
    while ($row = $teams_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['team_id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['logon'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No teams found</p>";
}

$conn->close();
?>
