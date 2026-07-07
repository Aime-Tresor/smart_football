<?php
session_start();

$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid transfer ID.";
    header("Location: transfer.php");
    exit;
}

$id = (int)$_GET['id'];

$sql = "DELETE FROM transfer WHERE id = $id";
if (mysqli_query($con, $sql)) {
    $_SESSION['success'] = "Transfer record deleted successfully.";
} else {
    $_SESSION['error'] = "Error deleting record: " . mysqli_error($con);
}

header("Location: transfer.php");
exit;
?>