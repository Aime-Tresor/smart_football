<?php
// Test script for goal saving functionality
session_start();

// Set fallback referee ID
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

echo "<h2>Goal Saving Test</h2>";

// Test the save_goal.php endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Testing Goal Save...</h3>";
    
    // Simulate the AJAX call to save_goal.php
    $postData = [
        'match_id' => $_POST['match_id'],
        'team_id' => $_POST['team_id'],
        'player_id' => $_POST['player_id'] ?: null,
        'minute' => $_POST['minute'],
        'goal_type' => $_POST['goal_type'] ?: 'regular',
        'description' => $_POST['description'] ?: '',
        'ajax' => '1'
    ];
    
    // Create a context for the HTTP request
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($postData)
        ]
    ]);
    
    // Call save_goal.php
    $response = file_get_contents('http://localhost/smart-football/referee/save_goal.php', false, $context);
    
    echo "<h4>Response from save_goal.php:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
    
    // Try to decode JSON response
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        if ($jsonResponse['success']) {
            echo "<p style='color: green; font-weight: bold;'>✅ Goal saved successfully!</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($jsonResponse['message']) . "</p>";
            if (isset($jsonResponse['new_score'])) {
                echo "<p><strong>New Score:</strong> Team 1: {$jsonResponse['new_score']['team1']}, Team 2: {$jsonResponse['new_score']['team2']}</p>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Error: " . htmlspecialchars($jsonResponse['message']) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Could not decode JSON response</p>";
    }
}

// Database connection for form data
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get available matches
$matchesSql = "
    SELECT 
        m.id,
        m.team1_id,
        m.team2_id,
        t1.name as team1_name,
        t2.name as team2_name,
        m.team1_goal,
        m.team2_goal,
        m.status
    FROM `match` m
    JOIN team t1 ON m.team1_id = t1.team_id
    JOIN team t2 ON m.team2_id = t2.team_id
    ORDER BY m.id
    LIMIT 5
";

$matchesResult = $conn->query($matchesSql);
?>

<h3>Test Goal Entry Form</h3>
<form method="POST" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div style="margin-bottom: 15px;">
        <label for="match_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Match:</label>
        <select name="match_id" id="match_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <option value="">Select Match...</option>
            <?php if ($matchesResult && $matchesResult->num_rows > 0): ?>
                <?php while ($match = $matchesResult->fetch_assoc()): ?>
                    <option value="<?= $match['id'] ?>">
                        Match #<?= $match['id'] ?>: <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?> 
                        (<?= $match['team1_goal'] ?? 0 ?>-<?= $match['team2_goal'] ?? 0 ?>) - <?= ucfirst($match['status']) ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="team_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Scoring Team:</label>
        <select name="team_id" id="team_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <option value="">Select Team...</option>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="player_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Player (Optional):</label>
        <select name="player_id" id="player_id" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <option value="">Select Player...</option>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="minute" style="display: block; margin-bottom: 5px; font-weight: bold;">Goal Minute:</label>
        <input type="text" name="minute" id="minute" placeholder="e.g., 45, 90+2" required 
               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label for="goal_type" style="display: block; margin-bottom: 5px; font-weight: bold;">Goal Type:</label>
        <select name="goal_type" id="goal_type" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <option value="regular">Regular Goal</option>
            <option value="penalty">Penalty</option>
            <option value="free_kick">Free Kick</option>
            <option value="own_goal">Own Goal</option>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description (Optional):</label>
        <textarea name="description" id="description" rows="3" placeholder="Additional details about the goal..." 
                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
    </div>

    <button type="submit" style="background: linear-gradient(135deg, #28a745, #20c997); color: white; border: none; padding: 12px 24px; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%;">
        ⚽ Test Goal Save
    </button>
</form>

<script>
// JavaScript to populate teams and players based on match selection
document.getElementById('match_id').addEventListener('change', function() {
    const matchId = this.value;
    const teamSelect = document.getElementById('team_id');
    const playerSelect = document.getElementById('player_id');
    
    // Clear existing options
    teamSelect.innerHTML = '<option value="">Select Team...</option>';
    playerSelect.innerHTML = '<option value="">Select Player...</option>';
    
    if (!matchId) return;
    
    // Get match data to populate teams
    const selectedOption = this.options[this.selectedIndex];
    const matchText = selectedOption.textContent;
    
    // Extract team info from the option text (this is a simple approach)
    // In a real implementation, you'd make an AJAX call to get team data
    <?php
    // Reset the result pointer
    $matchesResult = $conn->query($matchesSql);
    if ($matchesResult) {
        echo "const matchData = {";
        while ($match = $matchesResult->fetch_assoc()) {
            echo "'{$match['id']}': {";
            echo "team1_id: {$match['team1_id']}, team1_name: '" . addslashes($match['team1_name']) . "',";
            echo "team2_id: {$match['team2_id']}, team2_name: '" . addslashes($match['team2_name']) . "'";
            echo "},";
        }
        echo "};";
    }
    ?>
    
    if (matchData[matchId]) {
        const match = matchData[matchId];
        teamSelect.innerHTML += `<option value="${match.team1_id}">${match.team1_name}</option>`;
        teamSelect.innerHTML += `<option value="${match.team2_id}">${match.team2_name}</option>`;
    }
});

// Load players when team is selected
document.getElementById('team_id').addEventListener('change', function() {
    const teamId = this.value;
    const playerSelect = document.getElementById('player_id');
    
    playerSelect.innerHTML = '<option value="">Loading players...</option>';
    
    if (!teamId) {
        playerSelect.innerHTML = '<option value="">Select Player...</option>';
        return;
    }
    
    // Load players via AJAX
    fetch(`get_team_players.php?team_id=${teamId}&match_id=1`)
        .then(response => response.json())
        .then(data => {
            playerSelect.innerHTML = '<option value="">Select Player (Optional)...</option>';
            if (data.success && data.players) {
                data.players.forEach(player => {
                    const option = document.createElement('option');
                    option.value = player.member_id;
                    option.textContent = `#${player.number || '?'} ${player.fname} ${player.lname}`;
                    playerSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading players:', error);
            playerSelect.innerHTML = '<option value="">Error loading players</option>';
        });
});
</script>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    color: #2c3e50;
}

h2, h3 {
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

form {
    max-width: 600px;
    margin: 20px auto;
}

button:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8) !important;
    transform: translateY(-1px);
}
</style>

<?php $conn->close(); ?>
