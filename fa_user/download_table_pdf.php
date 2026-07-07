<?php
// download_table_pdf.php - Generate and download table as HTML/PDF
// This version generates HTML that can be converted to PDF by the browser

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'fa_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get team card stats
function getTeamCardStats($conn, $matchId, $teamId) {
    $sql = "
        SELECT c.card_type, COUNT(*) AS total
        FROM cards c
        JOIN team_members tm ON c.member_id = tm.member_id
        WHERE c.match_id = ? AND tm.team = ?
        GROUP BY c.card_type
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['yellow' => 0, 'double_yellow' => 0, 'red' => 0];
    }

    $stmt->bind_param("ii", $matchId, $teamId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cards = ['yellow' => 0, 'double_yellow' => 0, 'red' => 0];
    while ($row = $result->fetch_assoc()) {
        $cards[$row['card_type']] = $row['total'];
    }
    $stmt->close();

    return $cards;
}

// Get all matches with team information
$sqlMatches = "
    SELECT 
        m.*,
        t1.name AS team1_name,
        t1.logon AS team1_logo,
        t2.name AS team2_name,
        t2.logon AS team2_logo
    FROM `match` m
    JOIN `team` t1 ON m.team1_id = t1.team_id
    JOIN `team` t2 ON m.team2_id = t2.team_id
    ORDER BY m.match_date DESC, m.match_time DESC
";

$resultMatches = $conn->query($sqlMatches);
if (!$resultMatches) {
    die("Query failed: " . $conn->error);
}

// Check if we want to force download or display
$forceDownload = isset($_GET['download']) ? $_GET['download'] : 'yes';

if ($forceDownload === 'yes') {
    // Set headers for download
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="Match_Report_' . date('Y-m-d') . '.html"');
} else {
    // Just display in browser for testing
    header('Content-Type: text/html; charset=UTF-8');
}

// Start HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Match Report with Cards</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            color: #666;
            margin: 5px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #ddd;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-size: 9px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .match-teams {
            font-weight: bold;
        }
        .cards-info {
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Smart Football - Match Report</h1>
        <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Match</th>
                <th>Week</th>
                <th>Stadium</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Score</th>
                <th>Cards (Team 1)</th>
                <th>Cards (Team 2)</th>
            </tr>
        </thead>
        <tbody>
<?php

while ($row = $resultMatches->fetch_assoc()) {
    $matchId = $row['id'];
    $team1Id = $row['team1_id'];
    $team2Id = $row['team2_id'];
    $team1Name = $row['team1_name'];
    $team2Name = $row['team2_name'];

    // Get card stats for each team
    $cardsTeam1 = getTeamCardStats($conn, $matchId, $team1Id);
    $cardsTeam2 = getTeamCardStats($conn, $matchId, $team2Id);

    // Format match teams
    $matchTeams = htmlspecialchars($team1Name . ' vs ' . $team2Name);

    // Format score
    $score = '-';
    if ($row['status'] === 'live' || $row['status'] === 'completed') {
        $score = ($row['team1_goal'] ?? 0) . ' - ' . ($row['team2_goal'] ?? 0);
    }

    // Format cards for team 1
    $team1CardsText = '';
    if ($cardsTeam1['yellow'] > 0) $team1CardsText .= 'Yellow: ' . $cardsTeam1['yellow'] . ' ';
    if ($cardsTeam1['double_yellow'] > 0) $team1CardsText .= 'Double Yellow: ' . $cardsTeam1['double_yellow'] . ' ';
    if ($cardsTeam1['red'] > 0) $team1CardsText .= 'Red: ' . $cardsTeam1['red'] . ' ';
    if (empty($team1CardsText)) $team1CardsText = 'No cards';

    // Format cards for team 2
    $team2CardsText = '';
    if ($cardsTeam2['yellow'] > 0) $team2CardsText .= 'Yellow: ' . $cardsTeam2['yellow'] . ' ';
    if ($cardsTeam2['double_yellow'] > 0) $team2CardsText .= 'Double Yellow: ' . $cardsTeam2['double_yellow'] . ' ';
    if ($cardsTeam2['red'] > 0) $team2CardsText .= 'Red: ' . $cardsTeam2['red'] . ' ';
    if (empty($team2CardsText)) $team2CardsText = 'No cards';

    echo '<tr>';
    echo '<td class="match-teams">' . $matchTeams . '</td>';
    echo '<td class="text-center">' . htmlspecialchars($row['week']) . '</td>';
    echo '<td>' . htmlspecialchars($row['stadium']) . '</td>';
    echo '<td class="text-center">' . htmlspecialchars($row['match_date']) . '</td>';
    echo '<td class="text-center">' . htmlspecialchars($row['match_time']) . '</td>';
    echo '<td class="text-center">' . htmlspecialchars(ucfirst($row['status'])) . '</td>';
    echo '<td class="text-center">' . htmlspecialchars($score) . '</td>';
    echo '<td class="cards-info">' . htmlspecialchars($team1CardsText) . '</td>';
    echo '<td class="cards-info">' . htmlspecialchars($team2CardsText) . '</td>';
    echo '</tr>';
}

$conn->close();
?>
        </tbody>
    </table>
</body>
</html>
