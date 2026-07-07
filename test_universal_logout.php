<?php
session_start();

// Set up test sessions for different user types
if (isset($_GET['set_session'])) {
    $session_type = $_GET['set_session'];
    
    // Clear existing session
    $_SESSION = array();
    
    switch ($session_type) {
        case 'referee':
            $_SESSION['referee_id'] = 1;
            $_SESSION['referee_name'] = 'Test Referee';
            break;
        case 'team':
            $_SESSION['Team_id'] = 4;
            $_SESSION['Team_Name'] = 'Test Team FC';
            $_SESSION['stadium'] = 'Test Stadium';
            break;
        case 'fa_user':
            $_SESSION['fa_user'] = 'test_fa_user';
            $_SESSION['fa_user_name'] = 'Test FA User';
            break;
        case 'admin':
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Test Admin';
            break;
    }
    
    header("Location: test_universal_logout.php");
    exit();
}

// Handle logout test
if (isset($_GET['test_logout'])) {
    $logout_type = $_GET['test_logout'];
    
    switch ($logout_type) {
        case 'universal':
            header("Location: universal_logout.php");
            break;
        case 'referee':
            header("Location: referee/logout.php");
            break;
        case 'team':
            header("Location: teams/logout.php");
            break;
        case 'fa_user':
            header("Location: fa_user/logout.php");
            break;
    }
    exit();
}

// Detect current session type
$current_session = 'None';
$session_details = [];

if (isset($_SESSION['referee_id'])) {
    $current_session = 'Referee';
    $session_details = [
        'Type' => 'Referee Dashboard',
        'ID' => $_SESSION['referee_id'],
        'Name' => $_SESSION['referee_name'] ?? 'Unknown'
    ];
} elseif (isset($_SESSION['Team_id'])) {
    $current_session = 'Team';
    $session_details = [
        'Type' => 'Team Dashboard',
        'ID' => $_SESSION['Team_id'],
        'Name' => $_SESSION['Team_Name'] ?? 'Unknown',
        'Stadium' => $_SESSION['stadium'] ?? 'Unknown'
    ];
} elseif (isset($_SESSION['fa_user'])) {
    $current_session = 'FA User';
    $session_details = [
        'Type' => 'FA User Dashboard',
        'Username' => $_SESSION['fa_user'],
        'Name' => $_SESSION['fa_user_name'] ?? 'Unknown'
    ];
} elseif (isset($_SESSION['admin_id'])) {
    $current_session = 'Admin';
    $session_details = [
        'Type' => 'Admin Dashboard',
        'ID' => $_SESSION['admin_id'],
        'Name' => $_SESSION['admin_name'] ?? 'Unknown'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universal Logout System Test</title>
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
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
        
        .session-status {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .session-status h3 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
        }
        
        .session-info {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            font-family: monospace;
            font-size: 14px;
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
        }
        
        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
            color: white;
            text-decoration: none;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            color: #212529;
            text-decoration: none;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
            color: white;
            text-decoration: none;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
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
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .icon {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🚪 Universal Logout System Test</h1>
            <p>Test logout functionality across all dashboard sections</p>
        </div>
        
        <div class="test-content">
            <div class="session-status">
                <h3>📊 Current Session Status</h3>
                <div class="session-info">
                    <strong>Session Type:</strong> <?= $current_session ?> 
                    <span class="status-badge <?= $current_session !== 'None' ? 'status-active' : 'status-inactive' ?>">
                        <?= $current_session !== 'None' ? 'Active' : 'Inactive' ?>
                    </span><br>
                    <strong>Session ID:</strong> <?= session_id() ?><br>
                    <strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?><br>
                    
                    <?php if (!empty($session_details)): ?>
                        <br><strong>Session Details:</strong><br>
                        <?php foreach ($session_details as $key => $value): ?>
                            <strong><?= htmlspecialchars($key) ?>:</strong> <?= htmlspecialchars($value) ?><br>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="test-section">
                <h3>🎭 Set Test Session</h3>
                <p>Choose a session type to test logout functionality:</p>
                <div class="button-grid">
                    <a href="?set_session=referee" class="btn btn-primary">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        Set Referee Session
                    </a>
                    <a href="?set_session=team" class="btn btn-success">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h3v4h2v-7.5c0-.83.67-1.5 1.5-1.5S12 9.67 12 10.5V18h2v-4h3v4h2V9.5c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2V18H4z"/>
                        </svg>
                        Set Team Session
                    </a>
                    <a href="?set_session=fa_user" class="btn btn-info">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Set FA User Session
                    </a>
                    <a href="?set_session=admin" class="btn btn-warning">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                        </svg>
                        Set Admin Session
                    </a>
                </div>
            </div>
            
            <?php if ($current_session !== 'None'): ?>
            <div class="test-section">
                <h3>🧪 Test Logout Methods</h3>
                <p>Test different logout methods with current session:</p>
                <div class="button-grid">
                    <a href="?test_logout=universal" class="btn btn-danger" onclick="return confirm('Test Universal Logout? You will be redirected to logrole.php')">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Universal Logout
                    </a>
                    <a href="?test_logout=referee" class="btn btn-primary" onclick="return confirm('Test Referee Logout? You will be redirected to logrole.php')">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Referee Logout
                    </a>
                    <a href="?test_logout=team" class="btn btn-success" onclick="return confirm('Test Team Logout? You will be redirected to logrole.php')">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Team Logout
                    </a>
                    <a href="?test_logout=fa_user" class="btn btn-info" onclick="return confirm('Test FA User Logout? You will be redirected to logrole.php')">
                        <svg class="icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        FA User Logout
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <h4>🎯 Testing Instructions</h4>
                <ol>
                    <li><strong>Set Session:</strong> Click one of the "Set Session" buttons to simulate login</li>
                    <li><strong>Test Logout:</strong> Click any logout method to test redirection</li>
                    <li><strong>Verify Redirect:</strong> Confirm you're redirected to <code>logrole.php</code></li>
                    <li><strong>Check Session:</strong> Return to this page to verify session is cleared</li>
                </ol>
                
                <h5>Expected Results:</h5>
                <ul>
                    <li>✅ All logout methods should redirect to <code>logrole.php</code></li>
                    <li>✅ Session should be completely destroyed</li>
                    <li>✅ No session data should remain after logout</li>
                    <li>✅ Universal logout should work for any session type</li>
                </ul>
            </div>
            
            <div style="text-align: center; padding: 20px; border-top: 1px solid #e9ecef; margin-top: 30px;">
                <a href="logrole.php" class="btn btn-primary">🏠 Go to Role Selection</a>
                <a href="referee/" class="btn btn-info">👨‍⚖️ Referee Dashboard</a>
                <a href="teams/" class="btn btn-success">⚽ Team Dashboard</a>
                <a href="fa_user/" class="btn btn-warning">🏛️ FA User Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
