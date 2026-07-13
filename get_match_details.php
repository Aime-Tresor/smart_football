<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get match ID from request
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

if ($match_id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid match ID']));
}

try {
    // Get basic match information
    $matchSql = "
        SELECT 
            m.*,
            t1.name AS team1_name,
            t1.logon AS team1_logo,
            t2.name AS team2_name,
            t2.logon AS team2_logo
        FROM `match` m
        JOIN `team` t1 ON m.team1_id = t1.team_id
        JOIN `team` t2 ON m.team2_id = t2.team_id
        WHERE m.id = ?
    ";
    
    $matchStmt = $conn->prepare($matchSql);
    $matchStmt->bind_param("i", $match_id);
    $matchStmt->execute();
    $matchResult = $matchStmt->get_result();
    
    if ($matchResult->num_rows === 0) {
        die(json_encode(['success' => false, 'message' => 'Match not found']));
    }
    
    $match = $matchResult->fetch_assoc();
    
    // Get cards issued in this match
    $cardsSql = "
        SELECT
            c.card_id,
            c.card_type,
            c.card_time,
            c.card_reason_title,
            c.card_reason_detail,
            c.ai_summary,
            c.ai_summary_status,
            tm.fname,
            tm.lname,
            tm.number,
            t.name AS team_name
        FROM cards c
        JOIN team_members tm ON c.member_id = tm.member_id
        JOIN team t ON tm.team = t.team_id
        WHERE c.match_id = ? AND c.deleted_at IS NULL
        ORDER BY c.created_at ASC
    ";
    
    $cardsStmt = $conn->prepare($cardsSql);
    $cardsStmt->bind_param("i", $match_id);
    $cardsStmt->execute();
    $cardsResult = $cardsStmt->get_result();
    
    $cards = [];
    while ($row = $cardsResult->fetch_assoc()) {
        $cards[] = $row;
    }
    
    // Get goals scored in this match (try individual_goals table first, then fallback)
    $goals = [];
    
    // Check if individual_goals table exists
    $tableCheckSql = "SHOW TABLES LIKE 'individual_goals'";
    $tableCheckResult = $conn->query($tableCheckSql);
    
    if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
        // Use individual_goals table
        $goalsSql = "
            SELECT 
                ig.goal_minute,
                ig.goal_type,
                ig.description,
                tm.fname AS player_fname,
                tm.lname AS player_lname,
                tm.number,
                t.name AS team_name
            FROM individual_goals ig
            LEFT JOIN team_members tm ON ig.player_id = tm.member_id
            JOIN team t ON ig.team_id = t.team_id
            WHERE ig.match_id = ?
            ORDER BY 
                CASE 
                    WHEN ig.goal_minute REGEXP '^[0-9]+$' THEN CAST(ig.goal_minute AS UNSIGNED)
                    WHEN ig.goal_minute REGEXP '^[0-9]+\\+[0-9]+$' THEN CAST(SUBSTRING_INDEX(ig.goal_minute, '+', 1) AS UNSIGNED) + 100
                    ELSE 999
                END ASC
        ";
        
        $goalsStmt = $conn->prepare($goalsSql);
        $goalsStmt->bind_param("i", $match_id);
        $goalsStmt->execute();
        $goalsResult = $goalsStmt->get_result();
        
        while ($row = $goalsResult->fetch_assoc()) {
            $goals[] = [
                'goal_minute' => $row['goal_minute'],
                'goal_type' => $row['goal_type'],
                'description' => $row['description'],
                'player_name' => trim($row['player_fname'] . ' ' . $row['player_lname']),
                'number' => $row['number'],
                'team_name' => $row['team_name']
            ];
        }
    } else {
        // Fallback: try to get goals from match_day_reports table
        $goalsSql = "
            SELECT 
                mdr.goal_min,
                tm.fname,
                tm.lname,
                tm.number,
                t.name AS team_name
            FROM match_day_reports mdr
            JOIN team_members tm ON mdr.team_member = tm.member_id
            JOIN team t ON mdr.team = t.team_id
            WHERE mdr.week = ? AND mdr.goal = '1'
            ORDER BY CAST(mdr.goal_min AS UNSIGNED) ASC
        ";
        
        $goalsStmt = $conn->prepare($goalsSql);
        $goalsStmt->bind_param("i", $match['week']);
        $goalsStmt->execute();
        $goalsResult = $goalsStmt->get_result();
        
        while ($row = $goalsResult->fetch_assoc()) {
            $goals[] = [
                'goal_minute' => $row['goal_min'],
                'goal_type' => 'regular',
                'description' => '',
                'player_name' => trim($row['fname'] . ' ' . $row['lname']),
                'number' => $row['number'],
                'team_name' => $row['team_name']
            ];
        }
    }
    
    // Get match officials (referee information)
    $officialsSql = "
        SELECT
            r1.fname AS referee_fname,
            r1.lname AS referee_lname,
            r4.fname AS official_fname,
            r4.lname AS official_lname
        FROM weekly_fixtures wf
        LEFT JOIN referee r1 ON wf.referee = r1.referee_id
        LEFT JOIN referee r4 ON wf.official = r4.referee_id
        WHERE wf.match_id = ?
    ";

    $officialsStmt = $conn->prepare($officialsSql);
    $officialsStmt->bind_param("i", $match_id);
    $officialsStmt->execute();
    $officialsResult = $officialsStmt->get_result();

    $officials = null;
    if ($officialsResult->num_rows > 0) {
        $officialRow = $officialsResult->fetch_assoc();
        $officials = [
            'referee' => trim($officialRow['referee_fname'] . ' ' . $officialRow['referee_lname']) ?: null,
            'official' => trim($officialRow['official_fname'] . ' ' . $officialRow['official_lname']) ?: null
        ];
    }
    
    // Format match date and time for display
    $matchDateTime = new DateTime($match['match_date'] . ' ' . $match['match_time']);
    $match['match_date'] = $matchDateTime->format('M d, Y');
    $match['match_time'] = $matchDateTime->format('H:i');
    
    // Prepare response
    $response = [
        'success' => true,
        'match' => [
            'id' => $match['id'],
            'team1_name' => $match['team1_name'],
            'team1_logo' => $match['team1_logo'],
            'team2_name' => $match['team2_name'],
            'team2_logo' => $match['team2_logo'],
            'team1_goal' => $match['team1_goal'] ?? 0,
            'team2_goal' => $match['team2_goal'] ?? 0,
            'match_date' => $match['match_date'],
            'match_time' => $match['match_time'],
            'stadium' => $match['stadium'],
            'week' => $match['week'],
            'season' => $match['season'],
            'status' => $match['status'],
            'cards' => $cards,
            'goals' => $goals,
            'officials' => $officials
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching match details: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
