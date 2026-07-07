<?php
header('Content-Type: application/json');

try {
    $conn = new mysqli("localhost", "root", "", "fa_db");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $sql = "
        SELECT m.id, m.status, m.team1_goal, m.team2_goal, m.match_date, m.match_time,
               t1.name AS team1_name, t2.name AS team2_name
        FROM `match` m
        JOIN `team` t1 ON m.team1_id = t1.team_id
        JOIN `team` t2 ON m.team2_id = t2.team_id
        ORDER BY 
            CASE 
                WHEN m.status = 'live' THEN 1
                WHEN m.status = 'upcoming' THEN 2
                WHEN m.status = 'completed' THEN 3
                ELSE 4
            END, m.match_date, m.match_time";

    $result = $conn->query($sql);
    $matches = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $matches[] = [
                'id' => (int)$row['id'],
                'status' => $row['status'],
                'team1_name' => $row['team1_name'],
                'team2_name' => $row['team2_name'],
                'team1_goal' => $row['team1_goal'] !== null ? (int)$row['team1_goal'] : null,
                'team2_goal' => $row['team2_goal'] !== null ? (int)$row['team2_goal'] : null,
                'match_date' => $row['match_date'],
                'match_time' => $row['match_time']
            ];
        }
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'matches' => $matches,
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
