<?php
session_start();

// Set a test referee ID if not set
if (!isset($_SESSION['referee_id'])) {
    $_SESSION['referee_id'] = 1;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get a test match
$match_sql = "
    SELECT
        m.id,
        t1.team_id AS team1_id,
        t1.name AS team1_name,
        t2.team_id AS team2_id,
        t2.name AS team2_name
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    LIMIT 1
";

$match_result = $conn->query($match_sql);
$match = $match_result->fetch_assoc();

// Simple function to get players
function getSimplePlayers($conn, $team_name) {
    $sql = "SELECT member_id, fname, lname, number FROM team_members WHERE team = ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $team_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
    return $players;
}

$team1Players = getSimplePlayers($conn, $match['team1_name']);
$team2Players = getSimplePlayers($conn, $match['team2_name']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Players Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .team-section { border: 1px solid #ccc; margin: 20px 0; padding: 20px; }
        .player { background: #f0f0f0; margin: 5px 0; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Simple Players Display Test</h1>
    
    <div class="team-section">
        <h2>Team 1: <?= htmlspecialchars($match['team1_name']) ?></h2>
        <p>Players found: <?= count($team1Players) ?></p>
        
        <?php if (count($team1Players) > 0): ?>
            <?php foreach ($team1Players as $player): ?>
                <div class="player">
                    #<?= $player['number'] ?: 'N/A' ?> - <?= htmlspecialchars($player['fname'] . ' ' . $player['lname']) ?>
                    (ID: <?= $player['member_id'] ?>)
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: red;">No players found for team: <?= htmlspecialchars($match['team1_name']) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="team-section">
        <h2>Team 2: <?= htmlspecialchars($match['team2_name']) ?></h2>
        <p>Players found: <?= count($team2Players) ?></p>
        
        <?php if (count($team2Players) > 0): ?>
            <?php foreach ($team2Players as $player): ?>
                <div class="player">
                    #<?= $player['number'] ?: 'N/A' ?> - <?= htmlspecialchars($player['fname'] . ' ' . $player['lname']) ?>
                    (ID: <?= $player['member_id'] ?>)
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: red;">No players found for team: <?= htmlspecialchars($match['team2_name']) ?></p>
        <?php endif; ?>
    </div>
    
    <h3>Database Debug Info:</h3>
    <p>Team 1 Name: "<?= htmlspecialchars($match['team1_name']) ?>"</p>
    <p>Team 2 Name: "<?= htmlspecialchars($match['team2_name']) ?>"</p>
    
    <h4>Sample team_members records:</h4>
    <?php
    $sample_sql = "SELECT team, fname, lname FROM team_members LIMIT 5";
    $sample_result = $conn->query($sample_sql);
    if ($sample_result) {
        echo "<ul>";
        while ($row = $sample_result->fetch_assoc()) {
            echo "<li>Team: '{$row['team']}' - Player: {$row['fname']} {$row['lname']}</li>";
        }
        echo "</ul>";
    }
    ?>
</body>
</html>

<?php $conn->close(); ?>
