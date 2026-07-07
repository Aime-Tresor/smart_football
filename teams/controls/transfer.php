<?php
session_start();
require '../../app/database.php';

$post = $_POST['post'];
$member_id = $_POST['member_id'];
$team_to = $_POST['team_from'];
$team_from= $_SESSION['Team_id'];


try {
    $sql = "SELECT member_id,team_from,team_to FROM `transfer` WHERE member_id=? AND team_from=? AND team_to=?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$member_id, $team_from, $team_to]);
    $row = $stmt->rowCount();
    if ($row > 0) {
        $_SESSION['msg'] = '<script>swal("Warning!", "Arleady Transfer request Pending", "warning");</script>';
        header("Location: ../transfer.php?team=$team_from");
    } else {
        $sql = 'INSERT INTO `transfer`(`team_from`, `team_to`, `member_id`, `post`) 
        VALUES (?,?,?,?)';
        $stmt = $connection->prepare($sql);
        if ($stmt->execute([$team_from, $team_to, $member_id, $post])) {
            $_SESSION['msg'] = '<script>swal("Good job!", "Transer Request Succesfully", "success");</script>';
            header("Location: ../transfer.php?team=$team_from");
        }
    }
} catch (PDOException $e) {
    $_SESSION['msg'] = '<script>swal("Ooops !","Please Ask Your Administrator", "warning");</script>';
    header("Location: ../transfer.php?team=$team_from");
}
