<?php
session_start();
require '../../app/database.php';
try {
    $stmt = $connection->prepare("UPDATE `transfer` SET `status` = ?,`aprovalDate`=? WHERE id = ?");
    if ($stmt->execute([1, date("Y-m-d"), $_POST['id']])) {
        // After Aproval Update Other requests
        $stmt2 = $connection->prepare("UPDATE `transfer` SET `status` = ? WHERE member_id = ? AND `status` = ?");
        $stmt2->execute([2,$_POST['member_id'],0]);
        // After Aproval Update Other requests
        $_SESSION['msg'] = '<script>swal("Good job!", "Transer Request Aprooved", "success");</script>';
        header("Location: ../requests.php");}
} catch (PDOException $e) {
    $_SESSION['msg'] = '<script>swal("Ooops !","Please Ask Your Administrator", "warning");</script>';
    header("Location: ../requests.php");
}