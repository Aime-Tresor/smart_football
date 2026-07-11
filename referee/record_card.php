<?php
session_start();
require '../app/database.php';

if (!isset($_SESSION['referee_id'])) {
    header('Location: ../referee.php');
    exit;
}

$referee_id = $_SESSION['referee_id'];
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

// Handle card submission
if ($_POST && isset($_POST['submit_card'])) {
    $player_id = $_POST['player_id'];
    $team_id = $_POST['team_id'];
    $card_type = $_POST['card_type'];
    $card_minute = $_POST['card_minute'];
    $offense_description = trim($_POST['offense_description'] ?? '');
    $article_code = $_POST['article_code'] ?? 'ART-1';

    if (empty($player_id) || empty($card_type) || empty($card_minute)) {
        $error_msg = "Player, card type, and minute are required.";
    } else {
        try {
            // Insert card record
            $stmt = $connection->prepare("
                INSERT INTO match_day_reports (team_member, team, card, card_min, week)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$player_id, $team_id, $card_type, $card_minute, $match['week']]);

            // If RED CARD, create discipline case automatically
            if ($card_type === 'red') {
                if (empty($offense_description)) {
                    $error_msg = "Red card requires offense description.";
                } else {
                    // Determine sanction based on offense
                    $sanction = 'To be determined';
                    switch (strtolower($offense_description)) {
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
                        default:
                            $sanction = '2 game suspension';
                            $article_code = 'ART-1';
                    }

                    $stmt = $connection->prepare("
                        INSERT INTO ai_discipline_cases (team_id, member_id, offence_description, article_code, sanction, status, created_at)
                        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                    ");
                    $stmt->execute([$team_id, $player_id, $offense_description, $article_code, $sanction]);

                    $success_msg = "Red card recorded! Discipline case created automatically.";
                }
            } else {
                $success_msg = "Card recorded successfully.";
            }
        } catch (Exception $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
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
    <title>Record Card - Referee</title>
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
        .card-header h4 {
            margin: 0;
            font-weight: 700;
        }
        .match-info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .match-info h5 {
            margin: 0;
            color: #333;
        }
        .match-teams {
            font-size: 1.2rem;
            font-weight: 600;
            color: #667eea;
            margin: 10px 0;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 8px;
        }
        .card-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .card-btn {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        .card-btn.yellow {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .card-btn.red {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .card-btn.selected {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .offense-card {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .offense-card.red {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .player-select {
            display: grid;
            gap: 10px;
        }
        .player-option {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .player-option:hover {
            background: #f5f5f5;
            border-color: #667eea;
        }
        .player-option strong {
            display: block;
            color: #333;
        }
        .player-option small {
            color: #666;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Back Button -->
            <a href="view_match.php?match_id=<?php echo $match_id; ?>" class="btn btn-light mb-3">
                <i class="fas fa-arrow-left"></i> Back to Match
            </a>

            <!-- Match Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-football-ball me-2"></i>Record Card & Offense</h4>
                </div>
                <div class="card-body">
                    <!-- Match Info -->
                    <div class="match-info">
                        <h5>Match Details</h5>
                        <div class="match-teams">
                            <img src="../Logo/<?php echo $match['team1_id']; ?>.png" alt="Team 1" height="30" class="me-2">
                            <?php echo htmlspecialchars($match['team1_name']); ?> 
                            vs 
                            <img src="../Logo/<?php echo $match['team2_id']; ?>.png" alt="Team 2" height="30" class="ms-2 me-2">
                            <?php echo htmlspecialchars($match['team2_name']); ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($match['match_date'])); ?> 
                            | <i class="fas fa-clock"></i> <?php echo $match['match_time']; ?>
                            | <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($match['stadium']); ?>
                        </small>
                    </div>

                    <!-- Alert Messages -->
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

                    <!-- Card Entry Form -->
                    <form method="POST">
                        <!-- Select Team -->
                        <div class="form-group mb-4">
                            <label><strong>Select Team</strong></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="team_id" id="team1" value="<?php echo $match['team1_id']; ?>" checked>
                                <label class="btn btn-outline-primary" for="team1">
                                    <?php echo htmlspecialchars($match['team1_name']); ?>
                                </label>

                                <input type="radio" class="btn-check" name="team_id" id="team2" value="<?php echo $match['team2_id']; ?>">
                                <label class="btn btn-outline-primary" for="team2">
                                    <?php echo htmlspecialchars($match['team2_name']); ?>
                                </label>
                            </div>
                        </div>

                        <!-- Select Player -->
                        <div class="form-group mb-4">
                            <label><strong>Select Player</strong></label>
                            <select class="form-select" name="player_id" id="playerSelect" required>
                                <option value="">-- Choose a player --</option>
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
                                    echo htmlspecialchars($player['fname'] . ' ' . $player['lname']);
                                    echo ' (No. ' . $player['number'] . ', ' . htmlspecialchars($player['position']) . ')';
                                    echo '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Card Type Selection -->
                        <div class="form-group mb-4">
                            <label><strong>Card Type</strong></label>
                            <div class="card-type-selector">
                                <input type="radio" class="btn-check" name="card_type" id="cardYellow" value="yellow" checked>
                                <label class="card-btn yellow" for="cardYellow">
                                    <i class="fas fa-square fa-2x"></i><br>Yellow Card
                                </label>

                                <input type="radio" class="btn-check" name="card_type" id="cardRed" value="red">
                                <label class="card-btn red" for="cardRed">
                                    <i class="fas fa-square fa-2x"></i><br>Red Card
                                </label>
                            </div>
                        </div>

                        <!-- Card Minute -->
                        <div class="form-group mb-4">
                            <label><strong>Minute of Card</strong></label>
                            <input type="number" class="form-control" name="card_minute" min="1" max="120" placeholder="e.g., 45, 90+3" required>
                        </div>

                        <!-- Offense Description (Red Card Only) -->
                        <div class="form-group mb-4" id="offenseGroup" style="display: none;">
                            <label><strong>Offense Description</strong></label>
                            <div class="offense-card red">
                                <p class="small mb-3">
                                    <i class="fas fa-info-circle"></i> Red cards require a detailed offense description for discipline purposes.
                                </p>
                                <select class="form-select mb-2" id="offensePreset" onchange="setOffenseText()">
                                    <option value="">-- Select common offense --</option>
                                    <option value="Violent conduct">Violent conduct</option>
                                    <option value="Spitting">Spitting</option>
                                    <option value="Abusive language">Abusive language or gesture</option>
                                    <option value="Serious foul play">Serious foul play</option>
                                    <option value="Second yellow card">Second yellow card</option>
                                    <option value="Other">Other (describe below)</option>
                                </select>
                                <textarea class="form-control" name="offense_description" id="offenseText" rows="3" placeholder="Describe the offense in detail..."></textarea>
                            </div>
                        </div>

                        <!-- Article Code (Red Card Only) -->
                        <div class="form-group mb-4" id="articleGroup" style="display: none;">
                            <label><strong>Article Code</strong></label>
                            <select class="form-select" name="article_code">
                                <option value="ART-1">Article 1 - General Misconduct</option>
                                <option value="ART-14">Article 14 - Abusive Language</option>
                                <option value="ART-15">Article 15 - Violent Conduct</option>
                                <option value="ART-16">Article 16 - Spitting</option>
                                <option value="ART-17">Article 17 - Serious Foul Play</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-sm-flex">
                            <button type="submit" name="submit_card" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Record Card
                            </button>
                            <a href="view_match.php?match_id=<?php echo $match_id; ?>" class="btn btn-secondary btn-lg">
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
// Show/hide offense section based on card type
document.getElementById('cardYellow').addEventListener('change', function() {
    document.getElementById('offenseGroup').style.display = 'none';
    document.getElementById('articleGroup').style.display = 'none';
});

document.getElementById('cardRed').addEventListener('change', function() {
    document.getElementById('offenseGroup').style.display = 'block';
    document.getElementById('articleGroup').style.display = 'block';
});

// Update card button styling
document.querySelectorAll('input[name="card_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.card-btn').forEach(btn => btn.classList.remove('selected'));
        this.nextElementSibling.classList.add('selected');
    });
});

// Set offense text from preset
function setOffenseText() {
    const preset = document.getElementById('offensePreset').value;
    document.getElementById('offenseText').value = preset;
}

// Filter players by selected team
document.querySelectorAll('input[name="team_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Could add AJAX here to filter players by team
    });
});

// Initial styling
document.getElementById('cardYellow').nextElementSibling.classList.add('selected');
</script>
</body>
</html>
