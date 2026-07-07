<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $id = (int)$_POST['id'];
    $status = (int)$_POST['status'];
    $today = date('Y-m-d');

    // Reset all optional dates first
    $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";

    // Set the correct date field based on status
    // Status mapping: 0=Pending, 1=Requested, 2=Rejected, 3=Completed
    if ($status === 1) {
        // Requested - keep requestDate as is, clear others
        $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";
    } elseif ($status === 2) {
        // Rejected - set reject date
        $datesUpdate = "rejectDate = '$today', aprovalDate = NULL, completeDate = NULL";
    } elseif ($status === 3) {
        // Completed - set complete date
        $datesUpdate = "completeDate = '$today', aprovalDate = NULL, rejectDate = NULL";
    } elseif ($status === 0) {
        // Pending - clear all dates except request date
        $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";
    }

    $update = "UPDATE transfer SET status = $status, $datesUpdate WHERE id = $id";

    if (mysqli_query($con, $update)) {
        $_SESSION['success'] = "Transfer updated successfully. Status changed to: " .
            ($status == 0 ? 'Pending' : ($status == 1 ? 'Requested' : ($status == 2 ? 'Rejected' : 'Completed')));
    } else {
        $_SESSION['error'] = "Error updating transfer: " . mysqli_error($con);
    }

    header("Location: ../transfer.php");
    exit;
}

?>