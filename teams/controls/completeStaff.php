<?php
session_start();
require '../../app/database.php';

if (isset($_POST['submit'])) {
    $team_id = $_SESSION['Team_id'];
    $post =  $_POST['post'];
    $contract_duration = $_POST['contract_duration'];
    $contract_value = $_POST['contract_value'];
    $id = $_POST['id'];
    $member_id = $_POST['member_id'];

    try {
        $sql = 'UPDATE `team_member` SET `post` = ?, `team`=?, `contract_duration`=?,`contract_value`=? WHERE `member_id`=?';
        $stmt = $connection->prepare($sql);
        if ($stmt->execute([$post, $team_id, $contract_duration, $contract_value, $member_id])) {

            $stmt2 = $connection->prepare("UPDATE `transfer` SET `status` = ?,`completeDate`=? WHERE id = ?");
            $stmt2->execute([3, date("Y-m-d"), $id]);

            $_SESSION['msg'] = '<script>swal("Good job!", "Player Added Succesfully", "success");</script>';
            header("Location: ../requests.php");
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = '<script>swal("Ooops !","Please Ask Your Administrator", "warning");</script>';
        header("Location: ../requests.php");
    }
}
