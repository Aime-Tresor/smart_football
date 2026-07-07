<?php
// Script to create the individual_goals table
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Creating individual_goals Table</h2>";

// Create the individual_goals table
$createTableSql = "
CREATE TABLE IF NOT EXISTS `individual_goals` (
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
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ SUCCESS: individual_goals table created successfully!</p>";
    
    // Verify table was created
    $checkTableSql = "SHOW TABLES LIKE 'individual_goals'";
    $result = $conn->query($checkTableSql);
    
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table verification: individual_goals table exists</p>";
        
        // Show table structure
        echo "<h3>Table Structure Created:</h3>";
        $describeTableSql = "DESCRIBE individual_goals";
        $describeResult = $conn->query($describeTableSql);
        
        if ($describeResult) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>";
            echo "<tr style='background: #007bff; color: white;'><th style='padding: 12px;'>Field</th><th style='padding: 12px;'>Type</th><th style='padding: 12px;'>Null</th><th style='padding: 12px;'>Key</th><th style='padding: 12px;'>Default</th><th style='padding: 12px;'>Extra</th></tr>";
            
            while ($row = $describeResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td style='padding: 10px;'>" . $row['Field'] . "</td>";
                echo "<td style='padding: 10px;'>" . $row['Type'] . "</td>";
                echo "<td style='padding: 10px;'>" . $row['Null'] . "</td>";
                echo "<td style='padding: 10px;'>" . $row['Key'] . "</td>";
                echo "<td style='padding: 10px;'>" . ($row['Default'] ?? 'NULL') . "</td>";
                echo "<td style='padding: 10px;'>" . $row['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Test inserting a sample goal
        echo "<h3>Testing Goal Insertion:</h3>";
        
        // Get a sample match for testing
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
                              VALUES (?, ?, NULL, '45', 'regular', 'Test goal - table creation verification', 1)";
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
            echo "<p style='color: orange;'>⚠️ No matches found for testing, but table creation was successful</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error: Table was not created properly</p>";
    }
    
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ ERROR: Failed to create table</p>";
    echo "<p style='color: red;'>Error details: " . $conn->error . "</p>";
}

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 20px 0;'>";
echo "<h3 style='color: #155724; margin-top: 0;'>✅ Next Steps:</h3>";
echo "<ol style='color: #155724;'>";
echo "<li><strong>Table Created:</strong> The individual_goals table is now ready</li>";
echo "<li><strong>Test Goal Entry:</strong> Go to <a href='view_match.php?match_id=1' target='_blank' style='color: #155724; font-weight: bold;'>view_match.php?match_id=1</a></li>";
echo "<li><strong>Try Adding Goals:</strong> Use the enhanced goal entry interface</li>";
echo "<li><strong>Verify Results:</strong> Goals should now save without errors</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin: 20px 0;'>";
echo "<h4 style='color: #856404; margin-top: 0;'>📋 What This Table Does:</h4>";
echo "<ul style='color: #856404; margin-bottom: 0;'>";
echo "<li><strong>goal_id:</strong> Unique identifier for each goal</li>";
echo "<li><strong>match_id:</strong> Links to the match where the goal was scored</li>";
echo "<li><strong>team_id:</strong> Which team scored the goal</li>";
echo "<li><strong>player_id:</strong> Which player scored (optional)</li>";
echo "<li><strong>goal_minute:</strong> When the goal was scored (e.g., '45', '90+2')</li>";
echo "<li><strong>goal_type:</strong> Type of goal (regular, penalty, free kick, own goal)</li>";
echo "<li><strong>description:</strong> Additional details about the goal</li>";
echo "<li><strong>created_by:</strong> Which referee added the goal</li>";
echo "</ul>";
echo "</div>";

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
