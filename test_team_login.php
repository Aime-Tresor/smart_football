<?php
session_start();
require 'app/database.php';

// Get all teams for testing
$sql = "SELECT team_id, name, username, password FROM team ORDER BY team_id";
$stmt = $connection->prepare($sql);
$stmt->execute();
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Login System Test</title>
    <link rel="stylesheet" href="assets/css/style.default.css">
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
        
        .info-section {
            background: #e8f5e8;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            color: #2e7d32;
            margin: 0 0 15px 0;
        }
        
        .teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .team-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .team-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .team-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .team-info {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-family: monospace;
            font-size: 14px;
        }
        
        .password-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .password-plain {
            background: #fff3cd;
            color: #856404;
        }
        
        .password-md5 {
            background: #d4edda;
            color: #155724;
        }
        
        .test-form {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
            color: white;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .navigation-links {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #e9ecef;
            margin-top: 30px;
        }
        
        .nav-link {
            display: inline-block;
            margin: 0 15px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🔐 Team Login System Test</h1>
            <p>Enhanced Authentication with Mixed Password Support</p>
        </div>
        
        <div class="test-content">
            <div class="info-section">
                <h3>🎯 System Features</h3>
                <ul>
                    <li><strong>Dual Password Support:</strong> Handles both plain text and MD5 encrypted passwords</li>
                    <li><strong>Automatic Upgrade:</strong> Plain text passwords are automatically upgraded to MD5</li>
                    <li><strong>Enhanced Security:</strong> Better error handling and input validation</li>
                    <li><strong>Backward Compatibility:</strong> Works with existing plain text passwords</li>
                    <li><strong>Logging:</strong> Security events are logged for monitoring</li>
                </ul>
            </div>
            
            <?php if (isset($_SESSION['Team_Name'])): ?>
                <div class="alert alert-success">
                    <strong>✅ Login Successful!</strong><br>
                    Welcome, <strong><?= htmlspecialchars($_SESSION['Team_Name']) ?></strong>!<br>
                    Team ID: <?= htmlspecialchars($_SESSION['Team_id']) ?><br>
                    Stadium: <?= htmlspecialchars($_SESSION['stadium']) ?>
                </div>
                <div style="text-align: center; margin: 20px 0;">
                    <a href="teams/" class="btn btn-success">Go to Team Dashboard</a>
                    <a href="?logout=1" class="btn btn-primary">Logout & Test Again</a>
                </div>
            <?php endif; ?>
            
            <?php
            // Handle logout
            if (isset($_GET['logout'])) {
                session_destroy();
                header("Location: test_team_login.php");
                exit();
            }
            ?>
            
            <h3>📋 Available Teams for Testing</h3>
            <div class="teams-grid">
                <?php foreach ($teams as $team): ?>
                    <div class="team-card">
                        <div class="team-name"><?= htmlspecialchars($team['name']) ?></div>
                        
                        <?php
                        $password = $team['password'];
                        $is_md5 = (strlen($password) === 32 && ctype_xdigit($password));
                        ?>
                        
                        <div class="password-type <?= $is_md5 ? 'password-md5' : 'password-plain' ?>">
                            <?= $is_md5 ? '🔒 MD5 Encrypted' : '🔓 Plain Text' ?>
                        </div>
                        
                        <div class="team-info">
                            <strong>Username:</strong> <?= htmlspecialchars($team['username']) ?><br>
                            <strong>Password:</strong> <?= $is_md5 ? 'MD5: ' . substr($password, 0, 16) . '...' : $password ?><br>
                            <strong>Team ID:</strong> <?= htmlspecialchars($team['team_id']) ?>
                        </div>
                        
                        <div class="test-form">
                            <form method="post" action="app/team_login.php">
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control" 
                                           value="<?= htmlspecialchars($team['username']) ?>" 
                                           placeholder="Username" readonly>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" 
                                           placeholder="Enter password" required>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    Test Login
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="alert alert-info">
                <h4>🧪 Testing Instructions</h4>
                <ol>
                    <li><strong>Plain Text Teams:</strong> Enter the exact password shown</li>
                    <li><strong>MD5 Teams:</strong> Enter the original password (system will hash and compare)</li>
                    <li><strong>Auto-Upgrade:</strong> Plain text passwords will be automatically upgraded to MD5 after successful login</li>
                    <li><strong>Error Handling:</strong> Try wrong passwords to test error messages</li>
                </ol>
                
                <h5>Expected Behavior:</h5>
                <ul>
                    <li>✅ Plain text passwords should work with direct comparison</li>
                    <li>✅ MD5 passwords should work with hash comparison</li>
                    <li>✅ Wrong passwords should show appropriate error messages</li>
                    <li>✅ Successful login should redirect to team dashboard</li>
                </ul>
            </div>
            
            <div class="navigation-links">
                <a href="teams.php" class="nav-link">🏠 Team Login Page</a>
                <a href="fa_user/teams.php" class="nav-link">⚙️ Admin Panel</a>
                <a href="logrole.php" class="nav-link">🔄 Role Selection</a>
            </div>
        </div>
    </div>
</body>
</html>
