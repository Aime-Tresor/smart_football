<?php
session_start();
require 'app/database.php';

// Set up test session for team access
if (!isset($_SESSION['Team_id'])) {
    $_SESSION['Team_id'] = 4; // Use Kiyovu fc for testing
    $_SESSION['Team_Name'] = 'Kiyovu fc';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Name Fix Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .test-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .test-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .test-content {
            padding: 30px;
        }
        
        .test-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .test-section h3 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        
        .test-result {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-family: monospace;
            font-size: 14px;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .query-test {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .query-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
        }
        
        .query-code {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            color: #495057;
            margin-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 5px;
            transition: background 0.2s ease;
        }
        
        .btn:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🔧 Table Name Fix Verification</h1>
            <p>Testing team_members table access after fixes</p>
        </div>
        
        <div class="test-content">
            <div class="test-section">
                <h3>📊 Database Connection Test</h3>
                <?php
                try {
                    $test_query = "SHOW TABLES LIKE 'team_members'";
                    $stmt = $connection->prepare($test_query);
                    $stmt->execute();
                    $table_exists = $stmt->rowCount() > 0;
                    
                    if ($table_exists) {
                        echo '<div class="test-result success">✅ Table "team_members" exists in database</div>';
                    } else {
                        echo '<div class="test-result error">❌ Table "team_members" not found in database</div>';
                    }
                    
                    // Test table structure
                    $structure_query = "DESCRIBE team_members";
                    $stmt = $connection->prepare($structure_query);
                    $stmt->execute();
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="test-result info">📋 Table structure verified - ' . count($columns) . ' columns found</div>';
                    
                } catch (PDOException $e) {
                    echo '<div class="test-result error">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
            
            <div class="test-section">
                <h3>🧪 Query Tests</h3>
                
                <div class="query-test">
                    <div class="query-title">1. Select All Players Test</div>
                    <div class="query-code">SELECT * FROM team_members WHERE role_in_team='player' AND team=?</div>
                    <?php
                    try {
                        $sql = 'SELECT * FROM team_members WHERE role_in_team="player" AND team=?';
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$_SESSION['Team_id']]);
                        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        echo '<div class="test-result success">✅ Query successful - Found ' . count($players) . ' players</div>';
                    } catch (PDOException $e) {
                        echo '<div class="test-result error">❌ Query failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
                
                <div class="query-test">
                    <div class="query-title">2. Select All Staff Test</div>
                    <div class="query-code">SELECT * FROM team_members WHERE role_in_team='staff' AND team=?</div>
                    <?php
                    try {
                        $sql = 'SELECT * FROM team_members WHERE role_in_team="staff" AND team=?';
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$_SESSION['Team_id']]);
                        $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        echo '<div class="test-result success">✅ Query successful - Found ' . count($staff) . ' staff members</div>';
                    } catch (PDOException $e) {
                        echo '<div class="test-result error">❌ Query failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
                
                <div class="query-test">
                    <div class="query-title">3. Update Test (Dry Run)</div>
                    <div class="query-code">UPDATE team_members SET fname=? WHERE member_id=?</div>
                    <?php
                    try {
                        // Test with a non-existent member_id to avoid actual changes
                        $sql = 'UPDATE team_members SET fname=? WHERE member_id=?';
                        $stmt = $connection->prepare($sql);
                        // This will prepare successfully but affect 0 rows
                        $stmt->execute(['Test Name', 99999]);
                        echo '<div class="test-result success">✅ UPDATE query prepared successfully</div>';
                    } catch (PDOException $e) {
                        echo '<div class="test-result error">❌ UPDATE query failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
                
                <div class="query-test">
                    <div class="query-title">4. Insert Test (Dry Run)</div>
                    <div class="query-code">INSERT INTO team_members (fname, lname, role_in_team, team) VALUES (?, ?, ?, ?)</div>
                    <?php
                    try {
                        $sql = 'INSERT INTO team_members (fname, lname, role_in_team, team) VALUES (?, ?, ?, ?)';
                        $stmt = $connection->prepare($sql);
                        // Just prepare, don't execute to avoid adding test data
                        echo '<div class="test-result success">✅ INSERT query prepared successfully</div>';
                    } catch (PDOException $e) {
                        echo '<div class="test-result error">❌ INSERT query failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
                
                <div class="query-test">
                    <div class="query-title">5. Delete Test (Dry Run)</div>
                    <div class="query-code">DELETE FROM team_members WHERE member_id=?</div>
                    <?php
                    try {
                        $sql = 'DELETE FROM team_members WHERE member_id=?';
                        $stmt = $connection->prepare($sql);
                        // Just prepare, don't execute to avoid deleting data
                        echo '<div class="test-result success">✅ DELETE query prepared successfully</div>';
                    } catch (PDOException $e) {
                        echo '<div class="test-result error">❌ DELETE query failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="test-section">
                <h3>📁 Fixed Files Summary</h3>
                <div class="test-result info">
                    <strong>Files that were corrected:</strong><br>
                    • teams/controls/editplayer.php - Fixed table name in SELECT, UPDATE, and DELETE queries<br>
                    • teams/controls/editstaff.php - Fixed table name in UPDATE and DELETE queries<br>
                    • fa_user/controls/test.php - Fixed table name in UPDATE query and column reference
                </div>
                
                <div class="test-result success">
                    <strong>Changes made:</strong><br>
                    • Changed all instances of "team_member" to "team_members"<br>
                    • Fixed column reference from "member" to "member_id" in test.php<br>
                    • Fixed variable reference from $sql to $sql1 in test.php
                </div>
            </div>
            
            <div class="test-section">
                <h3>🔗 Test Navigation</h3>
                <a href="teams/team_member.php" class="btn btn-success">Test Team Members Page</a>
                <a href="teams/" class="btn">Team Dashboard</a>
                <a href="test_team_login.php" class="btn">Test Team Login</a>
                
                <div style="margin-top: 20px;">
                    <div class="test-result info">
                        <strong>Next Steps:</strong><br>
                        1. Visit the Team Members page to test player/staff management<br>
                        2. Try adding, editing, or deleting players/staff<br>
                        3. Verify that all operations work without database errors<br>
                        4. Check that the correct table "team_members" is being used
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
