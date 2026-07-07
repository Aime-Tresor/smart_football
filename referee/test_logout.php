<?php
session_start();

// Set up test session if not exists
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
    $_SESSION['referee_name'] = 'Test Referee';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Test - Referee Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .test-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .test-header {
            margin-bottom: 30px;
        }
        
        .test-header h1 {
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-size: 2.2em;
        }
        
        .test-header p {
            color: #6c757d;
            margin: 0;
            font-size: 1.1em;
        }
        
        .session-info {
            background: #e8f5e8;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .session-info h3 {
            color: #2e7d32;
            margin: 0 0 15px 0;
        }
        
        .session-data {
            text-align: left;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            color: #495057;
        }
        
        .logout-options {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .logout-btn.primary {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        
        .logout-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .logout-btn.secondary {
            background: #6c757d;
            color: white;
        }
        
        .logout-btn.secondary:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        
        .navigation-links {
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
        
        .nav-link {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .icon {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🚪 Logout Test</h1>
            <p>Test the session logout functionality</p>
        </div>
        
        <div class="session-info">
            <h3>📋 Current Session Status</h3>
            <div class="session-data">
                <strong>Session Active:</strong> <?= isset($_SESSION['referee_id']) ? 'Yes' : 'No' ?><br>
                <strong>Referee ID:</strong> <?= $_SESSION['referee_id'] ?? 'Not set' ?><br>
                <strong>Referee Name:</strong> <?= $_SESSION['referee_name'] ?? 'Not set' ?><br>
                <strong>Session ID:</strong> <?= session_id() ?><br>
                <strong>Time:</strong> <?= date('Y-m-d H:i:s') ?>
            </div>
        </div>
        
        <div class="logout-options">
            <a href="logout.php" class="logout-btn primary" onclick="return confirm('Are you sure you want to logout? This will redirect you to the role selection page.')">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
                Logout Now
            </a>
            
            <button onclick="location.reload()" class="logout-btn secondary">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                </svg>
                Refresh Page
            </button>
        </div>
        
        <div class="navigation-links">
            <a href="index.php" class="nav-link">🏠 Dashboard</a>
            <a href="matches.php" class="nav-link">⚽ Matches</a>
            <a href="test_cards.php" class="nav-link">🟨🟥 Card Test</a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px; border: 1px solid #ffeaa7;">
            <h4 style="color: #856404; margin: 0 0 10px 0;">💡 Test Instructions</h4>
            <p style="color: #856404; margin: 0; font-size: 14px; line-height: 1.5;">
                1. Click "Logout Now" to test the logout functionality<br>
                2. You should be redirected to <code>logrole.php</code><br>
                3. Your session should be completely destroyed<br>
                4. Use "Refresh Page" to check session status after logout
            </p>
        </div>
    </div>
</body>
</html>
