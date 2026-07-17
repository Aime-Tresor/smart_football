<?php
session_start();
require '../app/database.php';

if (!isset($_SESSION['Referee_id'])) {
    header('Location: ../app/referee_login.php');
    exit;
}

$match_id = $_GET['match_id'] ?? null;
if (!$match_id) {
    die('Match ID required');
}

// Get match details
$stmt = $connection->prepare("
    SELECT m.*, t1.name as team1_name, t2.name as team2_name
    FROM `match` m
    JOIN team t1 ON m.team1_id = t1.team_id
    JOIN team t2 ON m.team2_id = t2.team_id
    WHERE m.id = ?
");
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    die('Match not found');
}

$success_msg = $error_msg = '';

// Handle combined goal + card submission
if ($_POST && isset($_POST['add_detailed_goal'])) {
    $goal_team = $_POST['goal_team'] ?? null;
    $goal_player = $_POST['goal_player'] ?? null;
    $goal_minute = $_POST['goal_minute'] ?? null;
    $goal_type = $_POST['goal_type'] ?? 'Regular Goal';
    $goal_description = trim($_POST['goal_description'] ?? '');

    // Handle multiple cards
    $card_players = $_POST['card_player'] ?? [];
    $card_teams = $_POST['card_team'] ?? [];
    $card_types = $_POST['card_type'] ?? [];
    $card_minutes = $_POST['card_minute'] ?? [];
    $card_offenses = $_POST['card_offense'] ?? [];

    try {
        // Save goal
        if ($goal_team && $goal_minute) {
            $stmt = $connection->prepare("
                INSERT INTO match_day_reports (team_member, team, goal, goal_min, week)
                VALUES (?, ?, 1, ?, ?)
            ");
            $stmt->execute([$goal_player, $goal_team, $goal_minute, $match['week']]);
            $success_msg = "Goal recorded successfully.";
        }

        // Save cards and create discipline cases
        foreach ($card_players as $index => $player_id) {
            if (empty($player_id) || empty($card_teams[$index])) continue;

            $card_type = $card_types[$index] ?? 'yellow';
            $card_minute = $card_minutes[$index] ?? 0;
            $offense = $card_offenses[$index] ?? '';
            $team_id = $card_teams[$index];

            // Insert card record
            $stmt = $connection->prepare("
                INSERT INTO match_day_reports (team_member, team, card, card_min, week)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$player_id, $team_id, $card_type, $card_minute, $match['week']]);

            // If RED CARD, create discipline case
            if ($card_type === 'red' && !empty($offense)) {
                // Determine sanction based on offense
                $sanction = '2 game suspension';
                $article_code = 'ART-1';
                
                switch (strtolower($offense)) {
                    case 'violent conduct':
                        $sanction = '5 game suspension';
                        $article_code = 'ART-15';
                        break;
                    case 'spitting':
                        $sanction = '10 game suspension';
                        $article_code = 'ART-16';
                        break;
                    case 'abusive language':
                        $sanction = '3 game suspension';
                        $article_code = 'ART-14';
                        break;
                    case 'serious foul play':
                        $sanction = '4 game suspension';
                        $article_code = 'ART-17';
                        break;
                }

                $stmt = $connection->prepare("
                    INSERT INTO ai_discipline_cases (team_id, member_id, offence_description, article_code, sanction, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([$team_id, $player_id, $offense, $article_code, $sanction]);
            }
        }

        $success_msg .= " " . (count(array_filter($card_players)) > 0 ? count(array_filter($card_players)) . " card(s) recorded." : "");
        
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Get players from both teams
$stmt = $connection->prepare("
    SELECT member_id, fname, lname, number, position, team
    FROM team_members
    WHERE team IN (?, ?)
    ORDER BY team, fname
");
$stmt->execute([$match['team1_id'], $match['team2_id']]);
$players = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Report - <?php echo htmlspecialchars($match['team1_name']); ?> vs <?php echo htmlspecialchars($match['team2_name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        .match-info {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-section h5 {
            color: #333;
            font-weight: 700;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .card-entry-row {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-add-card {
            background: #28a745;
            border: none;
            padding: 8px 16px;
        }
        .btn-add-card:hover {
            background: #218838;
        }
        .btn-remove-card {
            background: #dc3545;
            border: none;
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Back Button -->
            <a href="matches.php" class="btn btn-light mb-3">
                <i class="fas fa-arrow-left"></i> Back to Matches
            </a>

            <!-- Match Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-football-ball me-2"></i>Match Report</h4>
                </div>
                <div class="card-body">
                    <div class="match-info">
                        <h5>
                            <?php echo htmlspecialchars($match['team1_name']); ?> 
                            <span class="badge bg-info"><?php echo $match['team1_goal']; ?></span>
                            vs
                            <span class="badge bg-info"><?php echo $match['team2_goal']; ?></span>
                            <?php echo htmlspecialchars($match['team2_name']); ?>
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($match['match_date'])); ?> 
                            | <i class="fas fa-clock"></i> <?php echo $match['match_time']; ?>
                            | <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($match['stadium']); ?>
                        </small>
                    </div>

                    <!-- Messages -->
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="matchReportForm">
                        <!-- GOAL ENTRY SECTION -->
                        <div class="form-section">
                            <h5><i class="fas fa-futbol me-2"></i>Goal Entry</h5>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Scoring Team</strong></label>
                                    <select class="form-select" name="goal_team" required>
                                        <option value="">Select Team</option>
                                        <option value="<?php echo $match['team1_id']; ?>"><?php echo htmlspecialchars($match['team1_name']); ?></option>
                                        <option value="<?php echo $match['team2_id']; ?>"><?php echo htmlspecialchars($match['team2_name']); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Goal Scorer</strong></label>
                                    <select class="form-select" name="goal_player">
                                        <option value="">Select Player (Optional)</option>
                                        <?php
                                        $current_team = '';
                                        foreach ($players as $player) {
                                            $team_name = $player['team'] == $match['team1_id'] ? $match['team1_name'] : $match['team2_name'];
                                            if ($current_team !== $team_name) {
                                                if ($current_team) echo '</optgroup>';
                                                echo '<optgroup label="' . htmlspecialchars($team_name) . '">';
                                                $current_team = $team_name;
                                            }
                                            echo '<option value="' . $player['member_id'] . '">';
                                            echo htmlspecialchars($player['fname'] . ' ' . $player['lname'] . ' (No. ' . $player['number'] . ')');
                                            echo '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Goal Minute</strong></label>
                                    <input type="text" class="form-control" name="goal_minute" placeholder="e.g., 45, 90+2" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Goal Type</strong></label>
                                    <select class="form-select" name="goal_type">
                                        <option value="Regular Goal">Regular Goal</option>
                                        <option value="Own Goal">Own Goal</option>
                                        <option value="Penalty">Penalty</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Goal Description</strong></label>
                                <textarea class="form-control" name="goal_description" rows="2" placeholder="Optional details about the goal"></textarea>
                            </div>
                        </div>

                        <!-- CARD ENTRY SECTION -->
                        <div class="form-section">
                            <h5><i class="fas fa-square me-2"></i>Card Entry</h5>
                            <p class="text-muted mb-3">Record cards given during the match. Red cards will automatically create discipline cases.</p>

                            <div id="cardsContainer">
                                <!-- Cards will be added here -->
                            </div>

                            <button type="button" class="btn btn-add-card" id="addCardBtn">
                                <i class="fas fa-plus me-2"></i>Add Card
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" name="add_detailed_goal" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Match Report (Goals & Cards)
                            </button>
                            <a href="matches.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let cardCount = 0;

document.getElementById('addCardBtn').addEventListener('click', function() {
    cardCount++;
    const cardsContainer = document.getElementById('cardsContainer');
    
    const cardRow = document.createElement('div');
    cardRow.className = 'card-entry-row';
    cardRow.id = 'card-' + cardCount;
    cardRow.innerHTML = `
        <div class="row mb-2">
            <div class="col-md-6">
                <label class="form-label">Team</label>
                <select class="form-select" name="card_team[]" required>
                    <option value="">Select Team</option>
                    <option value="<?php echo $match['team1_id']; ?>"><?php echo htmlspecialchars($match['team1_name']); ?></option>
                    <option value="<?php echo $match['team2_id']; ?>"><?php echo htmlspecialchars($match['team2_name']); ?></option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Player</label>
                <select class="form-select player-select" name="card_player[]" required>
                    <option value="">Select Player</option>
                    <?php
                    $current_team = '';
                    foreach ($players as $player) {
                        $team_name = $player['team'] == $match['team1_id'] ? $match['team1_name'] : $match['team2_name'];
                        if ($current_team !== $team_name) {
                            if ($current_team) echo '</optgroup>';
                            echo '<optgroup label="' . htmlspecialchars($team_name) . '">';
                            $current_team = $team_name;
                        }
                        echo '<option value="' . $player['member_id'] . '">';
                        echo htmlspecialchars($player['fname'] . ' ' . $player['lname'] . ' (No. ' . $player['number'] . ')');
                        echo '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="row mb-2">
            <div class="col-md-4">
                <label class="form-label">Card Type</label>
                <select class="form-select" name="card_type[]" required onchange="toggleOffenseField(this)">
                    <option value="yellow">Yellow Card</option>
                    <option value="red">Red Card</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Minute</label>
                <input type="text" class="form-control" name="card_minute[]" placeholder="e.g., 45" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">
                    Offense <span class="text-danger offense-only" style="display:none;">*</span>
                </label>
                <select class="form-select offense-field" name="card_offense[]" style="display:none;">
                    <option value="">Select (Red card only)</option>
                    <option value="Violent conduct">Violent conduct</option>
                    <option value="Spitting">Spitting</option>
                    <option value="Abusive language">Abusive language</option>
                    <option value="Serious foul play">Serious foul play</option>
                </select>
            </div>
        </div>
        
        <button type="button" class="btn btn-remove-card" onclick="removeCard('card-${cardCount}')">
            <i class="fas fa-trash"></i> Remove
        </button>
    `;
    
    cardsContainer.appendChild(cardRow);
});

function removeCard(cardId) {
    const card = document.getElementById(cardId);
    if (card) card.remove();
}

function toggleOffenseField(selectElement) {
    const row = selectElement.closest('.card-entry-row');
    const offenseField = row.querySelector('.offense-field');
    const offenseLabel = row.querySelector('.offense-only');
    
    if (selectElement.value === 'red') {
        offenseField.style.display = 'block';
        offenseLabel.style.display = 'inline';
        offenseField.required = true;
    } else {
        offenseField.style.display = 'none';
        offenseLabel.style.display = 'none';
        offenseField.required = false;
    }
}

// Add initial card field on page load
document.addEventListener('DOMContentLoaded', function() {
    // Start empty - referee clicks "Add Card" when needed
});
</script>
</body>
</html>
