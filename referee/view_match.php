<?php
session_start();

// Dummy login fallback for testing
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$referee_id = $_SESSION['referee_id'];
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

if ($match_id <= 0) {
    die("Invalid match ID.");
}

// Fetch match and team info
$sql = "
    SELECT
        m.id,
        m.match_date,
        m.match_time,
        m.status,
        m.team1_goal,
        m.team2_goal,
        m.stadium,
        t1.team_id AS team1_id,
        t1.name AS team1_name,
        t1.logon AS team1_logo,
        t2.team_id AS team2_id,
        t2.name AS team2_name,
        t2.logon AS team2_logo
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    WHERE m.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Match not found.");
}
$match = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Match Management - Referee Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <script src="assets/js/nav.js" defer></script>
    <script src="assets/js/dropdown.js" defer></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .match-container {
            display: grid;
            grid-template-columns: 1fr 300px 1fr;
            gap: 20px;
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            min-height: calc(100vh - 100px);
        }

        .team-div {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .team-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .team-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            display: block;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 4px solid #fff;
        }

        .team-name {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .players-section-title {
            font-size: 18px;
            font-weight: 600;
            color: white;
            margin: 20px 0 15px 0;
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .players-section-title:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
        }

        .players-list {
            max-height: 0;
            opacity: 0;
            transition: all 0.5s ease;
            overflow: hidden;
        }

        .players-list.show {
            max-height: 600px;
            opacity: 1;
            margin-top: 15px;
            overflow-y: auto;
        }

        .player-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 18px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .player-card:hover {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-color: #2196f3;
            transform: translateY(-3px);
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .player-number {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .player-name {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .player-position {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        .card-btn {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 3px solid;
            border-radius: 12px;
            width: 50px;
            height: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-btn.yellow {
            border-color: #ffc107;
            color: #ffc107;
        }

        .card-btn.yellow:hover {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: white;
        }

        .card-btn.red {
            border-color: #dc3545;
            color: #dc3545;
        }

        .card-btn.red:hover {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .cards-received {
            display: flex;
            gap: 4px;
            margin-top: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .card-icon {
            font-size: 16px;
            display: inline-block;
        }

        .score-div {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .match-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #ecf0f1;
        }

        .score-display {
            margin: 20px 0;
            color: #fff;
        }

        .team-score {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .team-score-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .team-name-small {
            font-size: 14px;
            font-weight: 600;
            color: #bdc3c7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .score-number {
            font-size: 48px;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .quick-goal-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(40,167,69,0.3);
        }

        .quick-goal-btn:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.4);
        }

        .vs-text {
            font-size: 24px;
            font-weight: 300;
            color: #bdc3c7;
        }

        .match-details {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .match-time, .match-status {
            font-size: 14px;
            color: #bdc3c7;
            margin: 5px 0;
        }

        .goal-entry {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .goal-entry h3 {
            margin: 0 0 20px 0;
            color: #ecf0f1;
            text-align: center;
            font-size: 18px;
        }

        .goal-entry-tabs {
            display: flex;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            background: rgba(255,255,255,0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            background: transparent;
            color: #bdc3c7;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .tab-btn:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .goal-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .goal-input {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.95);
            color: #2c3e50;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .goal-input:focus {
            outline: none;
            background: rgba(255,255,255,1);
            box-shadow: 0 0 0 2px rgba(40,167,69,0.3);
        }

        .goal-input::placeholder {
            color: #6c757d;
        }

        .goal-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .goal-btn:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }

        .goal-btn:active {
            transform: translateY(0);
        }

        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
        }

        .message.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .message.error {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .match-container {
                grid-template-columns: 1fr 280px 1fr;
                gap: 15px;
                padding: 15px;
            }

            .score-number {
                font-size: 36px;
            }

            .team-name-small {
                font-size: 12px;
            }
        }

        @media (max-width: 992px) {
            .match-container {
                grid-template-columns: 1fr;
                gap: 20px;
                max-width: 600px;
            }

            .score-div {
                order: -1;
            }

            .team-score {
                flex-direction: column;
                gap: 15px;
            }

            .vs-text {
                font-size: 20px;
                margin: 10px 0;
            }

            .team-score-section {
                flex-direction: row;
                gap: 15px;
            }

            .score-number {
                font-size: 32px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .match-container {
                padding: 10px;
                margin: 10px auto;
            }

            .team-div, .score-div {
                padding: 20px;
            }

            .goal-entry-tabs {
                flex-direction: column;
            }

            .tab-btn {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include "sidebar.php"; ?>
    
    <div class="main-content">
        <?php include "header.php"; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['card_message'])): ?>
            <div class="message success" id="message">
                <?= htmlspecialchars($_SESSION['card_message']) ?>
            </div>
            <?php unset($_SESSION['card_message']); ?>
        <?php endif; ?>

        <!-- 3-Div Layout -->
        <div class="match-container">
            <!-- Team 1 Div -->
            <div class="team-div" id="team1-div">
                <div class="team-header">
                    <img src="../Logo/<?= htmlspecialchars($match['team1_logo'] ?: 'default-team.png') ?>"
                         alt="<?= htmlspecialchars($match['team1_name']) ?>" class="team-logo">
                    <div class="team-name"><?= htmlspecialchars($match['team1_name']) ?></div>
                </div>

                <div class="players-section-title" onclick="togglePlayersList('team1')" id="team1-title">
                    👥 Team Players
                </div>
                <div class="players-list" id="team1-players">
                    <!-- Players will be loaded here when clicked -->
                </div>
            </div>

            <!-- Middle Score Div -->
            <div class="score-div">
                <div class="match-info">
                    <div class="match-title">Match Score</div>
                    <div class="score-display">
                        <div class="team-score">
                            <div class="team-score-section">
                                <div class="team-name-small"><?= htmlspecialchars($match['team1_name']) ?></div>
                                <div class="score-number" id="team1-score"><?= $match['team1_goal'] ?? 0 ?></div>
                                <button class="quick-goal-btn" onclick="showQuickGoalModal(<?= $match['team1_id'] ?>, '<?= htmlspecialchars($match['team1_name']) ?>')">+</button>
                            </div>
                            <span class="vs-text">VS</span>
                            <div class="team-score-section">
                                <div class="team-name-small"><?= htmlspecialchars($match['team2_name']) ?></div>
                                <div class="score-number" id="team2-score"><?= $match['team2_goal'] ?? 0 ?></div>
                                <button class="quick-goal-btn" onclick="showQuickGoalModal(<?= $match['team2_id'] ?>, '<?= htmlspecialchars($match['team2_name']) ?>')">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="match-details">
                        <div class="match-time">
                            <?= date('M d, Y', strtotime($match['match_date'])) ?> at <?= date('H:i', strtotime($match['match_time'])) ?>
                        </div>
                        <div class="match-status">
                            <?= ucfirst($match['status']) ?>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Goal Entry Section -->
                <div class="goal-entry">
                    <h3>⚽ Goal Entry</h3>
                    <div class="goal-entry-tabs">
                        <button type="button" class="tab-btn active" onclick="switchTab('quick')">Quick Entry</button>
                        <button type="button" class="tab-btn" onclick="switchTab('detailed')">Detailed Entry</button>
                    </div>

                    <!-- Quick Goal Entry -->
                    <div id="quick-entry" class="tab-content active">
                        <form class="goal-form" id="quickGoalForm">
                            <input type="hidden" name="match_id" value="<?= $match_id ?>">
                            <select name="team_id" class="goal-input" required id="quickTeamSelect">
                                <option value="">Select Scoring Team</option>
                                <option value="<?= $match['team1_id'] ?>"><?= htmlspecialchars($match['team1_name']) ?></option>
                                <option value="<?= $match['team2_id'] ?>"><?= htmlspecialchars($match['team2_name']) ?></option>
                            </select>
                            <input type="number" name="minute" class="goal-input" placeholder="Goal Minute (e.g., 45)" min="1" max="120" required>
                            <button type="submit" class="goal-btn">⚽ Add Goal</button>
                        </form>
                    </div>

                    <!-- Detailed Goal Entry -->
                    <div id="detailed-entry" class="tab-content">
                        <form class="goal-form" id="detailedGoalForm">
                            <input type="hidden" name="match_id" value="<?= $match_id ?>">
                            <select name="team_id" class="goal-input" required id="detailedTeamSelect">
                                <option value="">Select Scoring Team</option>
                                <option value="<?= $match['team1_id'] ?>"><?= htmlspecialchars($match['team1_name']) ?></option>
                                <option value="<?= $match['team2_id'] ?>"><?= htmlspecialchars($match['team2_name']) ?></option>
                            </select>
                            <select name="player_id" class="goal-input" id="detailedPlayerSelect">
                                <option value="">Select Player (Optional)</option>
                            </select>
                            <input type="text" name="minute" class="goal-input" placeholder="Goal Minute (e.g., 45, 90+2)" required>
                            <select name="goal_type" class="goal-input">
                                <option value="regular">Regular Goal</option>
                                <option value="penalty">Penalty</option>
                                <option value="free_kick">Free Kick</option>
                                <option value="own_goal">Own Goal</option>
                            </select>
                            <textarea name="description" class="goal-input" placeholder="Goal description (optional)" rows="2"></textarea>
                            <button type="submit" class="goal-btn">⚽ Add Detailed Goal</button>
                            <a href="record_card.php?match_id=<?php echo $match_id; ?>" class="btn btn-danger">
                            <i class="fas fa-square"></i> Record Card
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Team 2 Div -->
            <div class="team-div" id="team2-div">
                <div class="team-header">
                    <img src="../Logo/<?= htmlspecialchars($match['team2_logo'] ?: 'default-team.png') ?>"
                         alt="<?= htmlspecialchars($match['team2_name']) ?>" class="team-logo">
                    <div class="team-name"><?= htmlspecialchars($match['team2_name']) ?></div>
                </div>

                <div class="players-section-title" onclick="togglePlayersList('team2')" id="team2-title">
                    👥 Team Players
                </div>
                <div class="players-list" id="team2-players">
                    <!-- Players will be loaded here when clicked -->
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Goal Modal -->
    <div id="quickGoalModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="quickGoalModalTitle">Quick Goal Entry</h3>
                <span class="modal-close" onclick="closeQuickGoalModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="modalQuickGoalForm">
                    <input type="hidden" name="match_id" value="<?= $match_id ?>">
                    <input type="hidden" id="modalTeamId" name="team_id" value="">

                    <div class="form-group">
                        <label>Team:</label>
                        <div id="modalTeamName" class="team-display"></div>
                    </div>

                    <div class="form-group">
                        <label for="modalGoalMinute">Goal Minute:</label>
                        <input type="number" id="modalGoalMinute" name="minute" class="form-control"
                               placeholder="Enter minute (1-120)" min="1" max="120" required>
                    </div>

                    <div class="form-group">
                        <label for="modalGoalPlayer">Player (Optional):</label>
                        <select id="modalGoalPlayer" name="player_id" class="form-control">
                            <option value="">Select Player...</option>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeQuickGoalModal()">Cancel</button>
                        <button type="submit" class="btn-save">⚽ Add Goal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
        }

        .modal-close {
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
            opacity: 0.8;
        }

        .modal-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .team-display {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #1976d2;
            text-align: center;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-save {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-1px);
        }
    </style>

    <script>
    // Global variables
    let currentMatchId = <?= $match_id ?>;
    let currentTeam1Id = <?= $match['team1_id'] ?>;
    let currentTeam2Id = <?= $match['team2_id'] ?>;
    let team1Players = [];
    let team2Players = [];

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        loadTeamPlayers();
        setupGoalForm();

        // Auto-hide messages
        const message = document.getElementById('message');
        if (message) {
            setTimeout(() => {
                message.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => message.remove(), 300);
            }, 3000);
        }
    });

    // Toggle players list visibility
    function togglePlayersList(teamKey) {
        const playersContainer = document.getElementById(teamKey + '-players');
        const titleElement = document.getElementById(teamKey + '-title');

        if (playersContainer.classList.contains('show')) {
            // Hide players
            playersContainer.classList.remove('show');
        } else {
            // Show players
            playersContainer.classList.add('show');

            // Load players if not already loaded
            const players = teamKey === 'team1' ? team1Players : team2Players;
            if (players.length === 0) {
                loadTeamPlayers();
            } else {
                // Display existing players
                displayPlayers(players, playersContainer);
            }
        }
    }

    // Load team players from database
    function loadTeamPlayers() {
        console.log('Loading players for teams:', currentTeam1Id, currentTeam2Id);

        // Load Team 1 players
        fetch(`get_team_players.php?team_id=${currentTeam1Id}&match_id=${currentMatchId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Team 1 players data:', data);
                if (data.success) {
                    team1Players = data.players;
                    console.log('Team 1 players loaded:', team1Players.length);
                    // Display Team 1 players if container is visible
                    const team1Container = document.getElementById('team1-players');
                    if (team1Container && team1Container.classList.contains('show')) {
                        displayPlayers(team1Players, team1Container);
                    }
                } else {
                    console.error('Failed to load team 1 players:', data.message);
                    showMessage(data.message || 'Error loading team 1 players', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading team 1 players:', error);
                showMessage('Error loading team 1 players', 'error');
            });

        // Load Team 2 players
        fetch(`get_team_players.php?team_id=${currentTeam2Id}&match_id=${currentMatchId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Team 2 players data:', data);
                if (data.success) {
                    team2Players = data.players;
                    console.log('Team 2 players loaded:', team2Players.length);
                    // Display Team 2 players if container is visible
                    const team2Container = document.getElementById('team2-players');
                    if (team2Container && team2Container.classList.contains('show')) {
                        displayPlayers(team2Players, team2Container);
                    }
                } else {
                    console.error('Failed to load team 2 players:', data.message);
                    showMessage(data.message || 'Error loading team 2 players', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading team 2 players:', error);
                showMessage('Error loading team 2 players: ' + error.message, 'error');
            });
    }

    // Display players in container
    function displayPlayers(players, container) {
        console.log('Displaying players:', players);

        if (!container) {
            console.error('Container not found for displaying players');
            return;
        }

        if (!players || players.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #6c757d; padding: 20px;">No players found</div>';
            return;
        }

        let playersHtml = '';
        players.forEach(player => {
            // Generate cards display
            let cardsDisplay = '';
            if (player.cards && player.cards.length > 0) {
                player.cards.forEach(card => {
                    if (card === 'yellow') {
                        cardsDisplay += '<span class="card-icon" title="Yellow Card">🟨</span>';
                    } else if (card === 'red') {
                        cardsDisplay += '<span class="card-icon" title="Red Card">🟥</span>';
                    }
                });
            }

            playersHtml += `
                <div class="player-card" data-player-id="${player.member_id}">
                    <div class="player-info">
                        <div class="player-number">${player.number || '?'}</div>
                        <div class="player-details">
                            <div class="player-name">${player.fname || 'Unknown'} ${player.lname || 'Player'}</div>
                            <div class="player-position">${player.position || 'Player'}</div>
                        </div>
                    </div>
                    <div class="cards-received">
                        ${cardsDisplay || '<div style="text-align: center; color: #6c757d;">No cards</div>'}
                    </div>
                    <div class="card-actions">
                        <button class="card-btn yellow" onclick="giveCard(${player.member_id}, 'yellow')" title="Give Yellow Card">
                            🟨
                        </button>
                        <button class="card-btn red" onclick="giveCard(${player.member_id}, 'red')" title="Give Red Card">
                            🟥
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = playersHtml;
        console.log('Players displayed successfully');
    }

    // Give card to player
    function giveCard(playerId, cardType) {
        if (!confirm(`Are you sure you want to give a ${cardType} card to this player?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('player_id', playerId);
        formData.append('match_id', currentMatchId);
        formData.append('card', cardType);
        formData.append('ajax', '1');

        fetch('save_card.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                // Reload players to show updated cards immediately
                loadTeamPlayers();
            } else {
                showMessage(data.message || 'Error giving card', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while giving the card.', 'error');
        });
    }

    // Setup goal forms
    function setupGoalForm() {
        setupQuickGoalForm();
        setupDetailedGoalForm();
    }

    // Setup quick goal form
    function setupQuickGoalForm() {
        const quickForm = document.getElementById('quickGoalForm');
        if (!quickForm) return;

        quickForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('save_goal_simple.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    showMessage(data.message, 'success');
                    updateScoreDisplay();
                    this.reset();
                } else {
                    showMessage(data.message || 'Error adding goal', 'error');
                }
            })
            .catch(error => {
                console.error('Detailed error:', error);
                showMessage('Error: ' + error.message, 'error');
            });
        });
    }

    // Setup detailed goal form
    function setupDetailedGoalForm() {
        const detailedForm = document.getElementById('detailedGoalForm');
        if (!detailedForm) return;

        const teamSelect = detailedForm.querySelector('#detailedTeamSelect');
        const playerSelect = detailedForm.querySelector('#detailedPlayerSelect');

        // Load players when team is selected
        teamSelect.addEventListener('change', function() {
            const teamId = this.value;
            playerSelect.innerHTML = '<option value="">Select Player (Optional)</option>';

            if (teamId) {
                const players = teamId == currentTeam1Id ? team1Players : team2Players;
                if (players && players.length > 0) {
                    players.forEach(player => {
                        const option = document.createElement('option');
                        option.value = player.member_id;
                        option.textContent = `#${player.number || '?'} ${player.fname} ${player.lname}`;
                        playerSelect.appendChild(option);
                    });
                }
            }
        });

        // Handle detailed goal form submission
        detailedForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('save_goal_simple.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Detailed form response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log('Detailed form raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Detailed form JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Detailed form parsed data:', data);
                if (data.success) {
                    showMessage(data.message, 'success');
                    updateScoreDisplay();
                    this.reset();
                } else {
                    showMessage(data.message || 'Error adding goal', 'error');
                }
            })
            .catch(error => {
                console.error('Detailed form error:', error);
                showMessage('Error: ' + error.message, 'error');
            });
        });
    }

    // Switch between tabs
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab content
        document.getElementById(tabName + '-entry').classList.add('active');

        // Add active class to clicked tab button
        event.target.classList.add('active');
    }

    // Show quick goal modal for specific team
    function showQuickGoalModal(teamId, teamName) {
        const modal = document.getElementById('quickGoalModal');
        const modalTeamId = document.getElementById('modalTeamId');
        const modalTeamName = document.getElementById('modalTeamName');
        const modalGoalPlayer = document.getElementById('modalGoalPlayer');
        const modalGoalMinute = document.getElementById('modalGoalMinute');

        // Set team information
        modalTeamId.value = teamId;
        modalTeamName.textContent = teamName;

        // Load players for the selected team
        modalGoalPlayer.innerHTML = '<option value="">Select Player (Optional)...</option>';
        const players = teamId == currentTeam1Id ? team1Players : team2Players;
        if (players && players.length > 0) {
            players.forEach(player => {
                const option = document.createElement('option');
                option.value = player.member_id;
                option.textContent = `#${player.number || '?'} ${player.fname} ${player.lname}`;
                modalGoalPlayer.appendChild(option);
            });
        }

        // Show modal and focus on minute input
        modal.style.display = 'flex';
        modalGoalMinute.focus();

        // Setup modal form submission if not already done
        setupModalQuickGoalForm();
    }

    // Close quick goal modal
    function closeQuickGoalModal() {
        const modal = document.getElementById('quickGoalModal');
        modal.style.display = 'none';

        // Reset form
        const form = document.getElementById('modalQuickGoalForm');
        if (form) {
            form.reset();
        }
    }

    // Setup modal quick goal form
    function setupModalQuickGoalForm() {
        const modalForm = document.getElementById('modalQuickGoalForm');
        if (!modalForm || modalForm.hasAttribute('data-setup')) return;

        modalForm.setAttribute('data-setup', 'true');

        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');

            fetch('save_goal_simple.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Modal form response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log('Modal form raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Modal form JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Modal form parsed data:', data);
                if (data.success) {
                    showMessage(data.message, 'success');
                    closeQuickGoalModal();
                    updateScoreDisplay();
                } else {
                    showMessage(data.message || 'Error adding goal', 'error');
                }
            })
            .catch(error => {
                console.error('Modal form error:', error);
                showMessage('Error: ' + error.message, 'error');
            });
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('quickGoalModal');
        if (event.target === modal) {
            closeQuickGoalModal();
        }
    });

    // Update score display after goal is added
    function updateScoreDisplay() {
        // Reload the page to get updated scores from database
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    // Show message to user
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());

        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);

        // Auto-hide after 3 seconds
        setTimeout(() => {
            messageDiv.style.animation = 'slideIn 0.3s ease-out reverse';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }
    </script>
</body>
</html>
