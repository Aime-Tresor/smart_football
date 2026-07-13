<?php
require 'init.php';
include "mail.php";

if (!isset($_SESSION['fa_user']) || empty($_SESSION['fa_user'])) {
    header('Location: ../../login.php');
    exit;
}

if (isset($_POST['submit'])) {
    $Referee = (int) $_POST['select1'];
    $Official = (int) $_POST['select4'];
    $match_id = (int) $_POST['match_id'];

    // Match day info for the notification email comes from `match` (joined
    // to `team` for names) - the `calender` table this used to read from is
    // a different, unrelated table (and is empty), so that lookup never
    // actually found anything.
    $sql1 = 'SELECT m.week, m.stadium, m.match_date, m.match_time, t1.name AS home, t2.name AS away
             FROM `match` m
             JOIN team t1 ON m.team1_id = t1.team_id
             JOIN team t2 ON m.team2_id = t2.team_id
             WHERE m.id = ?';
    $stmt = $connection->prepare($sql1);
    $stmt->execute([$match_id]);
    $matchInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$matchInfo) {
        $_SESSION['error'] = 'Match not found.';
        header('Location: ../fixture.php');
        exit;
    }

    $sql1 = 'SELECT email FROM referee WHERE referee_id = ?';
    $stmt = $connection->prepare($sql1);
    $stmt->execute([$Official]);
    $email = $stmt->fetchColumn();

    // Guard against creating a second assignment for the same match (no
    // unique constraint on weekly_fixtures.match_id).
    $existsStmt = $connection->prepare('SELECT COUNT(*) FROM weekly_fixtures WHERE match_id = ?');
    $existsStmt->execute([$match_id]);
    if ($existsStmt->fetchColumn() > 0) {
        $_SESSION['error'] = 'Referees are already assigned to this match.';
        header("Location: ../fixture.php?set=$match_id");
        exit;
    }

    $access_Code = rand(10, 1000000);

    try {
        $sql = 'INSERT INTO weekly_fixtures(match_id, referee, official, access_code) VALUES (?,?,?,?)';
        $stmt = $connection->prepare($sql);
        $stmt->execute([$match_id, $Referee, $Official, $access_Code]);

        // The assignment itself is what matters and has already been saved
        // above - the notification email is a best-effort side effect and
        // must never make a successful assignment look like it failed.
        if ($email) {
            $subject = 'Rwanda Primus national League';
            $message = '<h3>Match Day ' . htmlspecialchars($matchInfo['week']) . ' Fixture</h3>'
                . '<h4>' . htmlspecialchars($matchInfo['home']) . '<small> VS </small>' . htmlspecialchars($matchInfo['away']) . '</h4>'
                . '<strong>Stadium: </strong>' . htmlspecialchars($matchInfo['stadium']) . '<br>'
                . '<strong>Date: </strong>' . htmlspecialchars($matchInfo['match_date']) . '<br>'
                . '<strong>Time: </strong>' . htmlspecialchars($matchInfo['match_time']) . ' <br>'
                . 'Match Access Code: <strong>' . $access_Code . '</strong>';

            try {
                send_mail($email, $subject, $message);
            } catch (\Throwable $e) {
                error_log('setReferee.php: notification email failed: ' . $e->getMessage());
            }
        }

        $_SESSION['success'] = 'Referees assigned successfully.';
        header("Location: ../fixture.php?set=$match_id");
        exit;
    } catch (PDOException $e) {
        error_log('setReferee.php: failed to assign referees: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to assign referees: ' . $e->getMessage();
        header('Location: ../fixture.php');
        exit;
    }
}
?>
