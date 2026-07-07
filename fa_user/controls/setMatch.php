<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require 'init.php';

if (isset($_POST['submit'])) {
    $team1 = $_POST['team1'];
    $team2 = $_POST['team2'];
    $stadium = $_POST['stadium'];
    $match_date = $_POST['match_day'];
    $match_time = $_POST['match_time'];


    // Calculate season
    $dateObj = new DateTime($match_date);
    $year = (int)$dateObj->format('Y');
    $month = (int)$dateObj->format('m');
    $season = ($month >= 7) ? "$year - " . ($year + 1) : ($year - 1) . " - $year";

    $week = (int)$dateObj->format('W');
    $status = 'upcoming';
    if (!$match_time || !$match_date || !$team1 || !$team2) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../fixture.php");
        exit;
    }
    if ($team1 == $team2) {
        $_SESSION['error'] = "Team 1 and Team 2 cannot be the same.";
        header("Location: ../fixture.php");
        exit;
    }

    // Check team conflict
    $sqlCheck = "
        SELECT * FROM `match`
        WHERE (
            team1_id = :team1 OR team2_id = :team1 OR
            team1_id = :team2 OR team2_id = :team2
        )
        AND match_date BETWEEN DATE_SUB(:match_date, INTERVAL 3 DAY) AND DATE_ADD(:match_date, INTERVAL 3 DAY)
        AND status = 'upcoming'
        LIMIT 1
    ";
    $stmtCheck = $connection->prepare($sqlCheck);
    $stmtCheck->execute([
        ':team1' => $team1,
        ':team2' => $team2,
        ':match_date' => $match_date,
    ]);

    $existingMatch = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($existingMatch) {
        $_SESSION['error'] = "One of the selected teams already has a match within 3 days of this date.";
        header("Location: ../fixture.php");
        exit;
    }

    // Insert match
    $sqlInsert = "INSERT INTO `match` 
        (team1_id, team2_id, stadium, match_date, match_time, season, status, week)
        VALUES (:team1, :team2, :stadium, :match_date, :match_time, :season, :status, :week)
    ";
    $stmtInsert = $connection->prepare($sqlInsert);
    $stmtInsert->execute([
        ':team1' => $team1,
        ':team2' => $team2,
        ':stadium' => $stadium,
        ':match_date' => $match_date,
        ':match_time' => $match_time,
        ':season' => $season,
        ':status' => $status,
        ':week' => $week,
    ]);

    $_SESSION['success'] = "Match created successfully.";
    header("Location: ../fixture.php?success=1");
    exit;
}
?>
