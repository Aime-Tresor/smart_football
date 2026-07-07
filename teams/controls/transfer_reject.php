<?php
session_start();
require '../../app/database.php';
try {
    $stmt = $connection->prepare("UPDATE `transfer` SET `status`,`rejectDate`=? = ? WHERE id = ?");
    if ($stmt->execute([2, date("Y-m-d"), $_POST['id']])) {
        $_SESSION['msg'] = '<script>swal("Good job!", "Transer Request Rejected", "success");</script>';
        header("Location: ../requests.php");}
} catch (PDOException $e) {
    $_SESSION['msg'] = '<script>swal("Ooops !","Please Ask Your Administrator", "warning");</script>';
    header("Location: ../requests.php");
}
