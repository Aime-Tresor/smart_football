<?php
session_start();

// Set up test referee session
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get a test match
$match_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
              FROM `match` m 
              JOIN team t1 ON m.team1_id = t1.team_id 
              JOIN team t2 ON m.team2_id = t2.team_id 
              LIMIT 1";
$match_result = $conn->query($match_sql);
$match = $match_result->fetch_assoc();

// Get test players
$players_sql = "SELECT member_id, fname, lname, yellow, double_yellow, red, team 
                FROM team_members 
                WHERE role_in_team = 'player' 
                LIMIT 6";
$players_result = $conn->query($players_sql);
$players = [];
while ($row = $players_result->fetch_assoc()) {
    $players[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card System Test - Referee Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/card-actions.js" defer></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 20px;
        }
        
        .test-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .test-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .test-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .test-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .test-content {
            padding: 30px;
        }
        
        .match-info {
            background: #f8f9ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .match-info h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .team-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e9ecef;
        }
        
        .team-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .instructions {
            background: #e8f5e8;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .instructions h3 {
            color: #2e7d32;
            margin: 0 0 15px 0;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
            color: #2e7d32;
        }
        
        .status-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .referee-info {
            color: #856404;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="test-container" data-match-id="<?= htmlspecialchars($match['id'] ?? '1') ?>">
        <div class="test-header">
            <h1>🟨🟥 Card System Test</h1>
            <p>Enhanced Referee Card Management System</p>
        </div>
        
        <div class="test-content">
            <div class="status-info">
                <div class="referee-info">
                    👨‍⚖️ Logged in as Referee ID: <?= $_SESSION['referee_id'] ?> | 
                    ⚽ Test Match: <?= htmlspecialchars($match['team1_name'] ?? 'Team A') ?> vs <?= htmlspecialchars($match['team2_name'] ?? 'Team B') ?>
                </div>
            </div>
            
            <div class="instructions">
                <h3>🎯 Test Instructions</h3>
                <ul>
                    <li><strong>Yellow Card (🟨):</strong> Click to issue first yellow. Second yellow automatically becomes red.</li>
                    <li><strong>Red Card (🟥):</strong> Click to issue direct red card.</li>
                    <li><strong>Confirmations:</strong> Each card action requires confirmation.</li>
                    <li><strong>Validation:</strong> System prevents invalid card combinations.</li>
                    <li><strong>Real-time Updates:</strong> Card displays update immediately after issuance.</li>
                </ul>
            </div>
            
            <?php if ($match): ?>
            <div class="match-info">
                <h3>📋 Match Details</h3>
                <p><strong>Match ID:</strong> <?= htmlspecialchars($match['id']) ?></p>
                <p><strong>Teams:</strong> <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($match['status']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($match['match_date']) ?> at <?= htmlspecialchars($match['match_time']) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="players-grid">
                <div class="team-section">
                    <div class="team-title">Test Players</div>
                    
                    <?php if (empty($players)): ?>
                        <p>No players found in database. Please add some players first.</p>
                    <?php else: ?>
                        <?php foreach ($players as $player): ?>
                            <div class="player-info" data-player-id="<?= htmlspecialchars($player['member_id']) ?>">
                                <div class="player-details">
                                    <div class="player-name" data-player-name>
                                        <?= htmlspecialchars($player['fname'] . ' ' . $player['lname']) ?>
                                    </div>
                                    <div class="card-display">
                                        <?php
                                        $yellowCount = (int)$player['yellow'];
                                        $hasDoubleYellow = (int)$player['double_yellow'] > 0;
                                        $hasRed = (int)$player['red'] > 0;
                                        
                                        if ($hasRed) {
                                            echo '<span class="card red" title="Direct Red Card"></span>';
                                        } elseif ($hasDoubleYellow) {
                                            echo '<span class="card red" title="Double Yellow = Red Card"></span>';
                                        } else {
                                            for ($i = 0; $i < $yellowCount; $i++) {
                                                echo '<span class="card yellow" title="Yellow Card"></span>';
                                            }
                                        }
                                        
                                        if ($yellowCount == 0 && !$hasRed && !$hasDoubleYellow) {
                                            echo '<span class="no-cards">No cards</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <form method="post" action="save_card.php" style="display:inline;">
                                        <input type="hidden" name="player_id" value="<?= htmlspecialchars($player['member_id']) ?>">
                                        <input type="hidden" name="match_id" value="<?= htmlspecialchars($match['id'] ?? '1') ?>">
                                        <input type="hidden" name="card" value="yellow">
                                        <button type="submit" class="btn-card yellow" 
                                                <?= ($hasRed || $hasDoubleYellow || $yellowCount >= 2) ? 'disabled' : '' ?> 
                                                title="Issue Yellow Card">
                                            🟨
                                        </button>
                                    </form>
                                    <form method="post" action="save_card.php" style="display:inline;">
                                        <input type="hidden" name="player_id" value="<?= htmlspecialchars($player['member_id']) ?>">
                                        <input type="hidden" name="match_id" value="<?= htmlspecialchars($match['id'] ?? '1') ?>">
                                        <input type="hidden" name="card" value="red">
                                        <button type="submit" class="btn-card red" 
                                                <?= ($hasRed || $hasDoubleYellow) ? 'disabled' : '' ?> 
                                                title="Issue Red Card">
                                            🟥
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <p><strong>💡 Pro Tip:</strong> Open browser developer tools (F12) to see AJAX requests and responses in the Network tab.</p>
                <p><a href="view_match.php?match_id=<?= htmlspecialchars($match['id'] ?? '1') ?>" style="color: #667eea; text-decoration: none; font-weight: 500;">
                    🔗 View Full Match Interface
                </a></p>
            </div>
        </div>
    </div>
</body>
</html>
