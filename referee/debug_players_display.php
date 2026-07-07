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

echo "<h1>Debug Players Display</h1>";

// Get a test match
$match_sql = "
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
    LIMIT 1
";

$match_result = $conn->query($match_sql);

if ($match_result && $match_result->num_rows > 0) {
    $match = $match_result->fetch_assoc();
    
    echo "<h2>Match Information:</h2>";
    echo "<p>Match ID: {$match['id']}</p>";
    echo "<p>Team 1: {$match['team1_name']} (ID: {$match['team1_id']})</p>";
    echo "<p>Team 2: {$match['team2_name']} (ID: {$match['team2_id']})</p>";
    
    // Test the getPlayersWithCards function
    function getPlayersWithCards($conn, $team_name) {
        $players = [];
        $sql = "
            SELECT 
                member_id,
                fname,
                lname,
                number,
                yellow,
                double_yellow,
                red
            FROM team_members
            WHERE team = ?
            ORDER BY fname, lname
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $team_name);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cards = [];
                // Yellow cards
                for ($i = 0; $i < (int)$row['yellow']; $i++) {
                    $cards[] = 'yellow';
                }
                // Double yellow cards = red
                for ($i = 0; $i < (int)$row['double_yellow']; $i++) {
                    $cards[] = 'red';
                }
                // Direct red cards
                for ($i = 0; $i < (int)$row['red']; $i++) {
                    $cards[] = 'red';
                }

                $players[] = [
                    'player_id' => $row['member_id'],
                    'name' => $row['fname'] . ' ' . $row['lname'],
                    'fname' => $row['fname'],
                    'lname' => $row['lname'],
                    'number' => $row['number'],
                    'cards' => $cards
                ];
            }
        }
        return $players;
    }
    
    // Test Team 1 players
    echo "<h3>Team 1 Players ({$match['team1_name']}):</h3>";
    $team1Players = getPlayersWithCards($conn, $match['team1_name']);
    echo "<p>Players found: " . count($team1Players) . "</p>";
    
    if (count($team1Players) > 0) {
        echo "<ul>";
        foreach ($team1Players as $player) {
            echo "<li>#{$player['number']} {$player['name']} (ID: {$player['player_id']}) - Cards: " . count($player['cards']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>No players found for team: {$match['team1_name']}</p>";
        
        // Check what teams exist in team_members
        echo "<h4>Available teams in team_members table:</h4>";
        $teams_sql = "SELECT DISTINCT team FROM team_members LIMIT 10";
        $teams_result = $conn->query($teams_sql);
        if ($teams_result) {
            echo "<ul>";
            while ($team_row = $teams_result->fetch_assoc()) {
                echo "<li>{$team_row['team']}</li>";
            }
            echo "</ul>";
        }
    }
    
    // Test Team 2 players
    echo "<h3>Team 2 Players ({$match['team2_name']}):</h3>";
    $team2Players = getPlayersWithCards($conn, $match['team2_name']);
    echo "<p>Players found: " . count($team2Players) . "</p>";
    
    if (count($team2Players) > 0) {
        echo "<ul>";
        foreach ($team2Players as $player) {
            echo "<li>#{$player['number']} {$player['name']} (ID: {$player['player_id']}) - Cards: " . count($player['cards']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>No players found for team: {$match['team2_name']}</p>";
    }
    
} else {
    echo "<p style='color: red;'>No matches found in database</p>";
}

$conn->close();
?>
