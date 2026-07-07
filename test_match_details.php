<?php
// Test file to verify match details functionality
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Match Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        h1 {
            color: #198754;
            text-align: center;
            margin-bottom: 30px;
        }

        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
        }

        .test-section h2 {
            color: #0d6efd;
            margin-top: 0;
        }

        .match-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .test-match-card {
            background: #f8f9fa;
            border: 2px solid #198754;
            border-radius: 15px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .test-match-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .match-info {
            text-align: center;
        }

        .teams {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
        }

        .team {
            flex: 1;
            text-align: center;
        }

        .score {
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
            margin: 0 20px;
        }

        .btn {
            background: #198754;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin: 5px;
        }

        .btn:hover {
            background: #157347;
        }

        .api-test {
            background: #e7f3ff;
            border: 2px solid #0d6efd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .response-display {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-futbol"></i> Match Details System Test</h1>

        <div class="test-section">
            <h2><i class="fas fa-list"></i> Available Completed Matches</h2>
            <p>Click on any completed match below to test the match details popup:</p>
            
            <div class="match-grid">
                <?php
                $conn = new mysqli("localhost", "root", "", "fa_db");
                if ($conn->connect_error) {
                    echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
                } else {
                    $sql = "
                        SELECT 
                            m.id,
                            m.week,
                            m.match_date,
                            m.match_time,
                            m.stadium,
                            m.team1_goal,
                            m.team2_goal,
                            m.status,
                            t1.name AS team1_name,
                            t2.name AS team2_name
                        FROM `match` m
                        JOIN `team` t1 ON m.team1_id = t1.team_id
                        JOIN `team` t2 ON m.team2_id = t2.team_id
                        WHERE m.status = 'completed'
                        ORDER BY m.match_date DESC, m.match_time DESC
                        LIMIT 6
                    ";
                    
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $matchDateTime = new DateTime($row['match_date'] . ' ' . $row['match_time']);
                            echo "
                            <div class='test-match-card' onclick='testMatchDetails({$row['id']})'>
                                <div class='match-info'>
                                    <div><strong>Week {$row['week']}</strong></div>
                                    <div class='teams'>
                                        <div class='team'>{$row['team1_name']}</div>
                                        <div class='score'>{$row['team1_goal']} - {$row['team2_goal']}</div>
                                        <div class='team'>{$row['team2_name']}</div>
                                    </div>
                                    <div style='font-size: 0.9rem; color: #6c757d;'>
                                        {$matchDateTime->format('M d, Y H:i')}<br>
                                        {$row['stadium']}
                                    </div>
                                    <div style='margin-top: 10px; color: #198754; font-weight: bold;'>
                                        <i class='fas fa-info-circle'></i> Click for Details
                                    </div>
                                </div>
                            </div>
                            ";
                        }
                    } else {
                        echo "<p style='color: orange;'>No completed matches found. You can create some test matches or change match status to 'completed' in the database.</p>";
                    }
                    $conn->close();
                }
                ?>
            </div>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-code"></i> API Testing</h2>
            <p>Test the match details API directly:</p>
            
            <div class="api-test">
                <label for="matchIdInput"><strong>Match ID:</strong></label>
                <input type="number" id="matchIdInput" placeholder="Enter match ID" style="padding: 8px; margin: 0 10px; border: 1px solid #ccc; border-radius: 4px;">
                <button class="btn" onclick="testAPI()">Test API</button>
                <button class="btn" onclick="clearResponse()" style="background: #6c757d;">Clear</button>
                
                <div id="apiResponse" class="response-display" style="display: none;"></div>
            </div>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-check-circle"></i> Features to Test</h2>
            <ul style="line-height: 1.8;">
                <li><strong>Match Information:</strong> Team names, scores, date, time, stadium</li>
                <li><strong>Cards Issued:</strong> Yellow and red cards given to players</li>
                <li><strong>Goals Scored:</strong> Individual goal details with players and times</li>
                <li><strong>Match Officials:</strong> Referee and assistant referees</li>
                <li><strong>Responsive Design:</strong> Modal adapts to different screen sizes</li>
                <li><strong>Error Handling:</strong> Graceful handling of missing data</li>
            </ul>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-database"></i> Database Requirements</h2>
            <p>The system uses these database tables:</p>
            <ul>
                <li><strong>match:</strong> Basic match information</li>
                <li><strong>team:</strong> Team names and logos</li>
                <li><strong>cards:</strong> Individual card records</li>
                <li><strong>team_members:</strong> Player information</li>
                <li><strong>weekly_fixtures:</strong> Referee assignments</li>
                <li><strong>referee:</strong> Referee information</li>
                <li><strong>individual_goals:</strong> Goal details (optional)</li>
            </ul>
        </div>
    </div>

    <script>
        // Test match details popup
        function testMatchDetails(matchId) {
            // Simulate the same function from index.php
            console.log('Testing match details for match ID:', matchId);
            
            // Create a simple modal for testing
            let modal = document.getElementById('testModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'testModal';
                modal.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.7); z-index: 2000; display: flex;
                    align-items: center; justify-content: center;
                `;
                document.body.appendChild(modal);
            }
            
            modal.innerHTML = `
                <div style="background: white; padding: 30px; border-radius: 15px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; color: #198754;">Match Details - ID: ${matchId}</h2>
                        <button onclick="closeTestModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                    </div>
                    <div id="testModalContent">
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #198754;"></i>
                            <p>Loading match details...</p>
                        </div>
                    </div>
                </div>
            `;
            
            modal.style.display = 'flex';
            
            // Fetch match details
            fetch('get_match_details.php?match_id=' + matchId)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('testModalContent');
                    if (data.success) {
                        content.innerHTML = `
                            <div style="text-align: center; margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                                <h3>${data.match.team1_name} ${data.match.team1_goal} - ${data.match.team2_goal} ${data.match.team2_name}</h3>
                                <p>${data.match.match_date} at ${data.match.match_time} | ${data.match.stadium}</p>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <h4 style="color: #198754;"><i class="fas fa-square" style="color: #ffc107;"></i> Cards (${data.match.cards.length})</h4>
                                    ${data.match.cards.length > 0 ? 
                                        data.match.cards.map(card => `
                                            <div style="padding: 8px; border-bottom: 1px solid #eee;">
                                                <span style="background: ${card.card_type === 'yellow' ? '#ffc107' : '#dc3545'}; color: ${card.card_type === 'yellow' ? '#000' : '#fff'}; padding: 2px 6px; border-radius: 3px; font-size: 12px; font-weight: bold;">${card.card_type.toUpperCase()}</span>
                                                #${card.number || '?'} ${card.fname} ${card.lname} (${card.team_name})
                                            </div>
                                        `).join('') : 
                                        '<p style="color: #6c757d; font-style: italic;">No cards issued</p>'
                                    }
                                </div>
                                <div>
                                    <h4 style="color: #198754;"><i class="fas fa-futbol"></i> Goals (${data.match.goals.length})</h4>
                                    ${data.match.goals.length > 0 ? 
                                        data.match.goals.map(goal => `
                                            <div style="padding: 8px; border-bottom: 1px solid #eee;">
                                                <strong>${goal.player_name || 'Unknown Player'}</strong><br>
                                                <small style="color: #6c757d;">${goal.team_name} - ${goal.goal_minute}'</small>
                                            </div>
                                        `).join('') : 
                                        '<p style="color: #6c757d; font-style: italic;">No goal details available</p>'
                                    }
                                </div>
                            </div>
                            <div style="margin-top: 20px;">
                                <h4 style="color: #198754;"><i class="fas fa-user-tie"></i> Match Officials</h4>
                                ${data.match.officials ? `
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                        ${data.match.officials.referee ? `<div><strong>Referee:</strong> ${data.match.officials.referee}</div>` : ''}
                                        ${data.match.officials.assistant1 ? `<div><strong>Assistant 1:</strong> ${data.match.officials.assistant1}</div>` : ''}
                                        ${data.match.officials.assistant2 ? `<div><strong>Assistant 2:</strong> ${data.match.officials.assistant2}</div>` : ''}
                                    </div>
                                ` : '<p style="color: #6c757d; font-style: italic;">No referee information available</p>'}
                            </div>
                        `;
                    } else {
                        content.innerHTML = `
                            <div style="text-align: center; color: #dc3545;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 15px;"></i>
                                <p>Error: ${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('testModalContent').innerHTML = `
                        <div style="text-align: center; color: #dc3545;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 15px;"></i>
                            <p>Failed to load match details</p>
                        </div>
                    `;
                });
        }

        function closeTestModal() {
            const modal = document.getElementById('testModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function testAPI() {
            const matchId = document.getElementById('matchIdInput').value;
            if (!matchId) {
                alert('Please enter a match ID');
                return;
            }

            const responseDiv = document.getElementById('apiResponse');
            responseDiv.style.display = 'block';
            responseDiv.textContent = 'Loading...';

            fetch('get_match_details.php?match_id=' + matchId)
                .then(response => response.json())
                .then(data => {
                    responseDiv.textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    responseDiv.textContent = 'Error: ' + error.message;
                });
        }

        function clearResponse() {
            document.getElementById('apiResponse').style.display = 'none';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('testModal');
            if (modal && event.target === modal) {
                closeTestModal();
            }
        });
    </script>
</body>
</html>
