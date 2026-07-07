<?php
session_start();
require '../../app/database.php';

if (isset($_POST['submit'])) {
    $number =  $_POST['number'];
    $position = $_POST['position'];
    $team_id = $_SESSION['Team_id'];
    $contract_duration = $_POST['contract_duration'];
    $contract_value = $_POST['contract_value'];
    $id = $_POST['id'];
    $member_id = $_POST['member_id'];

    try {
        $sql = "SELECT number,team FROM team_member WHERE team=? AND number=?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$team_id, $number]);
        $row = $stmt->rowCount();
        if ($row > 0) {
            $_SESSION['msg'] = '<script>swal("Warning!", "Number ' . $number . ' Arleady Token", "warning");</script>';
            header("Location: ../requests.php");
        } else {
            $sql = 'UPDATE `team_member` SET `number` = ? ,`position`=?, `team`=?, `contract_duration`=?,`contract_value`=? WHERE `member_id`=?';
            $stmt = $connection->prepare($sql);
            if ($stmt->execute([$number, $position, $team_id, $contract_duration, $contract_value, $member_id])) {

                $stmt2 = $connection->prepare("UPDATE `transfer` SET `status` = ? WHERE id = ?");
                $stmt2->execute([3, $id]);

                $_SESSION['msg'] = '<script>swal("Good job!", "Player Added Succesfully", "success");</script>';
                header("Location: ../requests.php");
            }
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = '<script>swal("Ooops !","Please Ask Your Administrator", "warning");</script>';
        header("Location: ../requests.php");
    }
}
