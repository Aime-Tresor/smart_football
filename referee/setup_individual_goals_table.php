<?php
// Script to set up the individual_goals table if it doesn't exist
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Individual Goals Table Setup</h2>";

// Check if individual_goals table exists
$checkTableSql = "SHOW TABLES LIKE 'individual_goals'";
$result = $conn->query($checkTableSql);

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ individual_goals table already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ individual_goals table does not exist. Creating...</p>";
    
    // Create individual_goals table
    $createTableSql = "
    CREATE TABLE `individual_goals` (
      `goal_id` int(11) NOT NULL AUTO_INCREMENT,
      `match_id` int(11) NOT NULL,
      `team_id` int(11) NOT NULL,
      `player_id` int(11) DEFAULT NULL,
      `goal_minute` varchar(10) DEFAULT NULL,
      `goal_type` enum('regular','penalty','own_goal','free_kick') DEFAULT 'regular',
      `description` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `created_by` int(11) DEFAULT NULL COMMENT 'Referee ID who added the goal',
      PRIMARY KEY (`goal_id`),
      KEY `idx_match_team` (`match_id`, `team_id`),
      KEY `idx_player` (`player_id`),
      KEY `idx_created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    
    if ($conn->query($createTableSql)) {
        echo "<p style='color: green;'>✅ individual_goals table created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
    }
}

// Check table structure
echo "<h3>Table Structure:</h3>";
$describeTableSql = "DESCRIBE individual_goals";
$describeResult = $conn->query($describeTableSql);

if ($describeResult) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $describeResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error describing table: " . $conn->error . "</p>";
}

// Test goal insertion
echo "<h3>Test Goal Insertion:</h3>";

// Get a sample match and team for testing
$testMatchSql = "SELECT m.id as match_id, m.team1_id, m.team2_id, t1.name as team1_name, t2.name as team2_name 
                 FROM `match` m 
                 JOIN team t1 ON m.team1_id = t1.team_id 
                 JOIN team t2 ON m.team2_id = t2.team_id 
                 LIMIT 1";
$testMatchResult = $conn->query($testMatchSql);

if ($testMatchResult && $testMatchResult->num_rows > 0) {
    $testMatch = $testMatchResult->fetch_assoc();
    
    echo "<p><strong>Test Match:</strong> {$testMatch['team1_name']} vs {$testMatch['team2_name']} (Match ID: {$testMatch['match_id']})</p>";
    
    // Try to insert a test goal
    $testInsertSql = "INSERT INTO individual_goals (match_id, team_id, player_id, goal_minute, goal_type, description, created_by) 
                      VALUES (?, ?, NULL, '45', 'regular', 'Test goal for setup verification', 1)";
    $testInsertStmt = $conn->prepare($testInsertSql);
    
    if ($testInsertStmt) {
        $testInsertStmt->bind_param("ii", $testMatch['match_id'], $testMatch['team1_id']);
        
        if ($testInsertStmt->execute()) {
            $testGoalId = $conn->insert_id;
            echo "<p style='color: green;'>✅ Test goal inserted successfully (Goal ID: $testGoalId)</p>";
            
            // Clean up test goal
            $cleanupSql = "DELETE FROM individual_goals WHERE goal_id = ?";
            $cleanupStmt = $conn->prepare($cleanupSql);
            $cleanupStmt->bind_param("i", $testGoalId);
            $cleanupStmt->execute();
            echo "<p style='color: blue;'>🧹 Test goal cleaned up</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Error inserting test goal: " . $testInsertStmt->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error preparing test insert: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No matches found for testing</p>";
}

// Show current goals count
$countGoalsSql = "SELECT COUNT(*) as count FROM individual_goals";
$countResult = $conn->query($countGoalsSql);
if ($countResult) {
    $count = $countResult->fetch_assoc()['count'];
    echo "<p><strong>Current goals in database:</strong> $count</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p>You can now test the goal entry system by:</p>";
echo "<ol>";
echo "<li>Going to <a href='view_match.php?match_id=1' target='_blank'>view_match.php?match_id=1</a></li>";
echo "<li>Trying to add goals using the enhanced goal entry interface</li>";
echo "<li>Checking that goals are saved and scores are updated</li>";
echo "</ol>";

$conn->close();
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    color: #2c3e50;
}

h2, h3 {
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

th {
    background: #007bff;
    color: white;
    padding: 12px;
}

td {
    padding: 10px;
}

p {
    background: rgba(255,255,255,0.9);
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}

a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style>
