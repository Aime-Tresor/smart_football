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
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 1; // Default to match 1

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Standalone Match Management - Referee Dashboard</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Match Container - 3 Div Layout */
        .match-container {
            display: grid;
            grid-template-columns: 1fr 300px 1fr;
            gap: 20px;
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            min-height: calc(100vh - 100px);
        }

        /* Team Divs */
        .team-div {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .team-div:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .team-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .team-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            display: block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 3px solid #fff;
        }

        .team-name {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .team-click-hint {
            font-size: 14px;
            color: #6c757d;
            font-style: italic;
            margin-top: 10px;
        }

        /* Middle Score Div */
        .score-div {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 2px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }

        .match-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #ecf0f1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .score-display {
            font-size: 48px;
            font-weight: 900;
            margin: 20px 0;
            color: #fff;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .vs-text {
            font-size: 24px;
            font-weight: 300;
            margin: 0 20px;
            color: #bdc3c7;
        }

        /* Player List Styles */
        .players-list {
            margin-top: 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.5s ease;
            opacity: 0;
        }

        .players-list.show {
            max-height: 600px;
            opacity: 1;
        }

        .player-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .player-card:hover {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-color: #2196f3;
            transform: translateX(5px);
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .player-number {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .player-details {
            flex: 1;
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

        .player-cards {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .card-btn {
            background: none;
            border: 2px solid;
            border-radius: 6px;
            width: 30px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .card-btn.yellow {
            border-color: #ffc107;
            color: #ffc107;
        }

        .card-btn.yellow:hover {
            background: #ffc107;
            color: white;
        }

        .card-btn.red {
            border-color: #dc3545;
            color: #dc3545;
        }

        .card-btn.red:hover {
            background: #dc3545;
            color: white;
        }

        .card-count {
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
            padding: 2px 6px;
            border-radius: 10px;
            background: #f8f9fa;
            color: #495057;
        }

        /* Message Styles */
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
        @media (max-width: 992px) {
            .match-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .score-div {
                order: -1;
            }
        }
    </style>
</head>
<body>
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['card_message'])): ?>
        <div class="message success" id="message">
            <?= htmlspecialchars($_SESSION['card_message']) ?>
        </div>
        <?php unset($_SESSION['card_message']); ?>
    <?php endif; ?>

    <!-- New 3-Div Layout -->
    <div class="match-container">
        <!-- Team 1 Div -->
        <div class="team-div" id="team1-div" onclick="toggleTeamPlayers(1)">
            <div class="team-header">
                <img src="<?= htmlspecialchars($match['team1_logo'] ?: 'assets/images/default-team.png') ?>" 
                     alt="<?= htmlspecialchars($match['team1_name']) ?>" class="team-logo">
                <div class="team-name"><?= htmlspecialchars($match['team1_name']) ?></div>
                <div class="team-click-hint">Click to view players</div>
            </div>
            
            <div class="players-list" id="team1-players">
                <!-- Players will be loaded here -->
            </div>
        </div>

        <!-- Middle Score Div -->
        <div class="score-div">
            <div class="match-title">Match Score</div>
            <div class="score-display">
                <?= $match['team1_goal'] ?> <span class="vs-text">VS</span> <?= $match['team2_goal'] ?>
            </div>
            <div style="margin-top: 20px; color: #bdc3c7; font-size: 14px;">
                <?= date('M d, Y', strtotime($match['match_date'])) ?> at <?= date('H:i', strtotime($match['match_time'])) ?>
            </div>
        </div>

        <!-- Team 2 Div -->
        <div class="team-div" id="team2-div" onclick="toggleTeamPlayers(2)">
            <div class="team-header">
                <img src="<?= htmlspecialchars($match['team2_logo'] ?: 'assets/images/default-team.png') ?>" 
                     alt="<?= htmlspecialchars($match['team2_name']) ?>" class="team-logo">
                <div class="team-name"><?= htmlspecialchars($match['team2_name']) ?></div>
                <div class="team-click-hint">Click to view players</div>
            </div>
            
            <div class="players-list" id="team2-players">
                <!-- Players will be loaded here -->
            </div>
        </div>
    </div>

    <script>
    // Global variables
    let currentMatchId = <?= $match_id ?>;
    let currentTeam1Id = <?= $match['team1_id'] ?>;
    let currentTeam2Id = <?= $match['team2_id'] ?>;
    let team1Players = [];
    let team2Players = [];

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, initializing...');
        loadTeamPlayers();
        
        // Auto-hide messages
        const message = document.getElementById('message');
        if (message) {
            setTimeout(() => {
                message.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => message.remove(), 300);
            }, 3000);
        }
    });

    // Load team players from database
    function loadTeamPlayers() {
        console.log('Loading players for teams:', currentTeam1Id, currentTeam2Id);
        
        // Load Team 1 players
        fetch(`get_team_players.php?team_id=${currentTeam1Id}&match_id=${currentMatchId}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                team1Players = data;
                console.log('Team 1 players loaded:', team1Players.length);
            })
            .catch(error => {
                console.error('Error loading team 1 players:', error);
                showMessage('Error loading team 1 players', 'error');
            });

        // Load Team 2 players
        fetch(`get_team_players.php?team_id=${currentTeam2Id}&match_id=${currentMatchId}`)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                team2Players = data;
                console.log('Team 2 players loaded:', team2Players.length);
            })
            .catch(error => {
                console.error('Error loading team 2 players:', error);
                showMessage('Error loading team 2 players', 'error');
            });
    }

    // Toggle team players display
    function toggleTeamPlayers(teamNumber) {
        console.log('Toggling team players for team:', teamNumber);
        const playersContainer = document.getElementById(`team${teamNumber}-players`);
        
        if (!playersContainer) {
            console.error('Players container not found for team:', teamNumber);
            return;
        }
        
        const players = teamNumber === 1 ? team1Players : team2Players;
        
        if (playersContainer.classList.contains('show')) {
            playersContainer.classList.remove('show');
        } else {
            if (players && players.length > 0) {
                displayPlayers(players, playersContainer);
                playersContainer.classList.add('show');
            } else {
                playersContainer.innerHTML = '<div style="text-align: center; color: #6c757d; padding: 20px;">Loading players...</div>';
                playersContainer.classList.add('show');
                loadTeamPlayers();
            }
        }
    }

    // Display players in container
    function displayPlayers(players, container) {
        if (!players || players.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #6c757d; padding: 20px;">No players found</div>';
            return;
        }

        let playersHtml = '';
        players.forEach(player => {
            const yellowCount = parseInt(player.yellow) || 0;
            const redCount = parseInt(player.red) || 0;
            
            playersHtml += `
                <div class="player-card" data-player-id="${player.member_id}">
                    <div class="player-info">
                        <div class="player-number">${player.number || '?'}</div>
                        <div class="player-details">
                            <div class="player-name">${player.fname || 'Unknown'} ${player.lname || 'Player'}</div>
                            <div class="player-position">${player.position || 'Player'}</div>
                        </div>
                        <div class="player-cards">
                            <button class="card-btn yellow" onclick="giveCard(${player.member_id}, 'yellow')" title="Give Yellow Card">
                                🟨
                            </button>
                            <span class="card-count">${yellowCount}</span>
                            <button class="card-btn red" onclick="giveCard(${player.member_id}, 'red')" title="Give Red Card">
                                🟥
                            </button>
                            <span class="card-count">${redCount}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = playersHtml;
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
                loadTeamPlayers();
                setTimeout(() => {
                    const team1Container = document.getElementById('team1-players');
                    const team2Container = document.getElementById('team2-players');
                    
                    if (team1Container.classList.contains('show')) {
                        displayPlayers(team1Players, team1Container);
                    }
                    if (team2Container.classList.contains('show')) {
                        displayPlayers(team2Players, team2Container);
                    }
                }, 500);
            } else {
                showMessage(data.message || 'Error giving card', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while giving the card.', 'error');
        });
    }

    // Show message to user
    function showMessage(message, type) {
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        document.body.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.style.animation = 'slideIn 0.3s ease-out reverse';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }
    </script>
</body>
</html>
