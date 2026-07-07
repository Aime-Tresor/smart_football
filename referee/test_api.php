<?php
session_start();

// Set a test referee ID if not set
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

echo "<h1>Test get_team_players.php API</h1>";

// Get a test match
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$match_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
              FROM `match` m 
              JOIN team t1 ON m.team1_id = t1.team_id 
              JOIN team t2 ON m.team2_id = t2.team_id 
              LIMIT 1";
$match_result = $conn->query($match_sql);

if ($match_result && $match_result->num_rows > 0) {
    $match = $match_result->fetch_assoc();
    
    echo "<h2>Testing with Match ID: {$match['id']}</h2>";
    echo "<p>Team 1: {$match['team1_name']} (ID: {$match['team1_id']})</p>";
    echo "<p>Team 2: {$match['team2_name']} (ID: {$match['team2_id']})</p>";
    
    // Test Team 1
    echo "<h3>Team 1 API Test:</h3>";
    $team1_url = "get_team_players.php?team_id={$match['team1_id']}&match_id={$match['id']}";
    echo "<p>URL: <a href='$team1_url' target='_blank'>$team1_url</a></p>";
    
    // Test Team 2
    echo "<h3>Team 2 API Test:</h3>";
    $team2_url = "get_team_players.php?team_id={$match['team2_id']}&match_id={$match['id']}";
    echo "<p>URL: <a href='$team2_url' target='_blank'>$team2_url</a></p>";
    
    // Test with cURL
    echo "<h3>cURL Test Results:</h3>";
    
    // Test Team 1 with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/smart-football/referee/" . $team1_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $team1_response = curl_exec($ch);
    $team1_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h4>Team 1 Response (HTTP $team1_http_code):</h4>";
    echo "<pre>" . htmlspecialchars($team1_response) . "</pre>";
    
    // Test Team 2 with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost/smart-football/referee/" . $team2_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    $team2_response = curl_exec($ch);
    $team2_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<h4>Team 2 Response (HTTP $team2_http_code):</h4>";
    echo "<pre>" . htmlspecialchars($team2_response) . "</pre>";
    
} else {
    echo "<p style='color: red;'>No matches found in database</p>";
}

$conn->close();
?>
