<?php
session_start();
require 'app/database.php';

// Set up test sessions
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}
if (!isset($_SESSION['Team_id'])) {
    $_SESSION['Team_id'] = 4; // Kiyovu fc
    $_SESSION['Team_Name'] = 'Kiyovu fc';
    $_SESSION['logo'] = 'kiyovu.png';
}

// Get sample match data
$match_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
              FROM `match` m 
              JOIN team t1 ON m.team1_id = t1.team_id 
              JOIN team t2 ON m.team2_id = t2.team_id 
              WHERE m.id = 1";
$match_result = $connection->query($match_sql);
$match = $match_result->fetch(PDO::FETCH_ASSOC);

// Get players with cards
$players_sql = "SELECT member_id, fname, lname, number, yellow, double_yellow, red, team 
                FROM team_members 
                WHERE role_in_team = 'player' 
                ORDER BY team, number";
$players_result = $connection->query($players_sql);
$players = $players_result->fetchAll(PDO::FETCH_ASSOC);

// Get recent card history
$cards_sql = "SELECT c.*, tm.fname, tm.lname, tm.number, tm.team,
                     m.match_date, t1.name as team1_name, t2.name as team2_name
              FROM cards c
              LEFT JOIN team_members tm ON c.member_id = tm.member_id
              LEFT JOIN `match` m ON c.match_id = m.id
              LEFT JOIN team t1 ON m.team1_id = t1.team_id
              LEFT JOIN team t2 ON m.team2_id = t2.team_id
              ORDER BY c.created_at DESC
              LIMIT 10";
$cards_result = $connection->query($cards_sql);
$recent_cards = $cards_result->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Management System Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .test-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin: 20px auto;
            max-width: 1200px;
            overflow: hidden;
        }
        
        .test-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .test-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        .feature-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .card-yellow {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #8b6914;
        }
        
        .card-red {
            background: linear-gradient(135deg, #dc3545 0%, #ff4757 100%);
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn-test {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .player-card {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .player-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .card-indicator {
            display: inline-block;
            width: 20px;
            height: 25px;
            border-radius: 3px;
            margin: 0 2px;
            text-align: center;
            line-height: 25px;
            font-size: 12px;
        }
        
        .yellow-card {
            background: #ffd700;
            color: #8b6914;
        }
        
        .red-card {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1><i class="fas fa-futbol"></i> Card Management System</h1>
            <p class="mb-0">Complete Football Card Management & Testing Interface</p>
        </div>
        
        <div class="container-fluid p-4">
            <!-- System Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?= count($players) ?></h3>
                        <p class="mb-0">Total Players</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?= array_sum(array_column($players, 'yellow')) ?></h3>
                        <p class="mb-0">Yellow Cards</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?= array_sum(array_column($players, 'red')) + array_sum(array_column($players, 'double_yellow')) ?></h3>
                        <p class="mb-0">Red Cards</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?= count($recent_cards) ?></h3>
                        <p class="mb-0">Recent Cards</p>
                    </div>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Referee Interface</h5>
                            <p class="card-text">Issue yellow and red cards during matches with automatic rule enforcement.</p>
                            <a href="referee/view_match.php?match_id=1" class="btn btn-primary btn-test">
                                <i class="fas fa-whistle"></i> Referee Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Team Interface</h5>
                            <p class="card-text">View player card status, suspension information, and disciplinary history.</p>
                            <a href="teams/player_cards.php" class="btn btn-success btn-test">
                                <i class="fas fa-eye"></i> Team Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Card Testing</h5>
                            <p class="card-text">Test card issuance functionality with different scenarios and validations.</p>
                            <a href="referee/test_cards.php" class="btn btn-warning btn-test">
                                <i class="fas fa-vial"></i> Test Cards
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Rules -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-book"></i> Card Management Rules</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-warning"><i class="fas fa-square"></i> Yellow Card Rules</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> First yellow card: Player continues</li>
                                        <li><i class="fas fa-check text-success"></i> Second yellow card: Automatic red card</li>
                                        <li><i class="fas fa-times text-danger"></i> Cannot issue yellow to player with red card</li>
                                        <li><i class="fas fa-times text-danger"></i> Maximum 2 yellow cards per player</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger"><i class="fas fa-square"></i> Red Card Rules</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Direct red card: Immediate ejection</li>
                                        <li><i class="fas fa-check text-success"></i> Double yellow = red card</li>
                                        <li><i class="fas fa-times text-danger"></i> Cannot issue red to player with existing red</li>
                                        <li><i class="fas fa-times text-danger"></i> Only one red card per player</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Players -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Sample Players</h5>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach (array_slice($players, 0, 10) as $player): 
                                $totalRed = $player['red'] + $player['double_yellow'];
                                $isSuspended = $player['yellow'] >= 5 || $totalRed > 0;
                            ?>
                            <div class="player-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#<?= $player['number'] ?> <?= htmlspecialchars($player['fname'] . ' ' . $player['lname']) ?></strong>
                                        <br><small class="text-muted">Team ID: <?= $player['team'] ?></small>
                                    </div>
                                    <div>
                                        <?php for ($i = 0; $i < $player['yellow']; $i++): ?>
                                            <span class="card-indicator yellow-card">🟨</span>
                                        <?php endfor; ?>
                                        <?php for ($i = 0; $i < $totalRed; $i++): ?>
                                            <span class="card-indicator red-card">🟥</span>
                                        <?php endfor; ?>
                                        <?php if ($player['yellow'] == 0 && $totalRed == 0): ?>
                                            <span class="badge bg-success">Clean</span>
                                        <?php endif; ?>
                                        <?php if ($isSuspended): ?>
                                            <span class="badge bg-danger">Suspended</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Recent Card History</h5>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($recent_cards)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No card history found. Start issuing cards to see history here.
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_cards as $card): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                                    <div>
                                        <strong>
                                            <?php
                                            $cardIcon = $card['card_type'] === 'yellow' ? '🟨' : 
                                                       ($card['card_type'] === 'red' ? '🟥' : '🟨🟥');
                                            echo $cardIcon;
                                            ?>
                                            #<?= $card['number'] ?> <?= htmlspecialchars($card['fname'] . ' ' . $card['lname']) ?>
                                        </strong>
                                        <br><small class="text-muted">
                                            <?= ucfirst(str_replace('_', ' ', $card['card_type'])) ?> Card
                                            <?php if ($card['match_date']): ?>
                                                - <?= date('M d, Y', strtotime($card['match_date'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('H:i', strtotime($card['created_at'])) ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Instructions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Testing Instructions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6><i class="fas fa-user-shield text-primary"></i> Referee Testing</h6>
                                    <ol>
                                        <li>Click "Referee Dashboard"</li>
                                        <li>Select a match to view</li>
                                        <li>Issue yellow/red cards to players</li>
                                        <li>Verify automatic red card for 2 yellows</li>
                                        <li>Test validation rules</li>
                                    </ol>
                                </div>
                                <div class="col-md-4">
                                    <h6><i class="fas fa-users text-success"></i> Team Testing</h6>
                                    <ol>
                                        <li>Click "Team Dashboard"</li>
                                        <li>View player card statistics</li>
                                        <li>Check suspension status</li>
                                        <li>Review card history</li>
                                        <li>Test player details modal</li>
                                    </ol>
                                </div>
                                <div class="col-md-4">
                                    <h6><i class="fas fa-vial text-warning"></i> Card Testing</h6>
                                    <ol>
                                        <li>Click "Test Cards"</li>
                                        <li>Try different card scenarios</li>
                                        <li>Test with different players</li>
                                        <li>Verify database updates</li>
                                        <li>Check error handling</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
