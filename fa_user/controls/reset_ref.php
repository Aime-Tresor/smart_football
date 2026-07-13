<?php
require 'init.php';

if (!isset($_SESSION['fa_user']) || empty($_SESSION['fa_user'])) {
    header('Location: ../../login.php');
    exit;
}

if (isset($_POST['submit'])) {
    $match_id = (int) $_POST['match_id'];

    try {
        // Clearing a match assignment must only remove that assignment -
        // it must not touch `referee.status` (the referee's account-wide
        // active/inactive flag), which the previous version of this file
        // incorrectly overwrote for both referees on every reset.
        $sql = 'DELETE FROM weekly_fixtures WHERE match_id = ?';
        $statement = $connection->prepare($sql);
        $statement->execute([$match_id]);

        $_SESSION['success'] = 'Referee assignment cleared.';
        header("Location: ../fixture.php?set=$match_id");
        exit;
    } catch (PDOException $e) {
        error_log('reset_ref.php: failed to clear referees: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to clear referee assignment: ' . $e->getMessage();
        header('Location: ../fixture.php');
        exit;
    }
}
?>
