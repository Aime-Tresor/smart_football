<?php
session_start();

$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize POST data
    $id = (int)($_POST['id'] ?? 0);
    $member_id = (int)($_POST['member_id'] ?? 0);
    $post = $_POST['post'] ?? '';
    $team_from = (int)($_POST['team_from'] ?? 0);
    $team_to = (int)($_POST['team_to'] ?? 0);
    $status = (int)($_POST['status'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error'] = "Invalid transfer ID.";
        header("Location: ../transfer.php");
        exit();
    }

    if ($team_from == $team_to) {
        $_SESSION['error'] = "Team From and Team To cannot be the same.";
        header("Location: ../transfer_edit.php?id=$id");
        exit();
    }

    // Prepare the UPDATE statement
    $sql = "UPDATE transfer SET member_id=?, post=?, team_from=?, team_to=?, status=? WHERE id=?";
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        $_SESSION['error'] = "Prepare failed: " . mysqli_error($con);
        header("Location: ../transfer_edit.php?id=$id");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "isiiii", $member_id, $post, $team_from, $team_to, $status, $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Transfer updated successfully.";
        header("Location: ../transfer.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating transfer: " . mysqli_stmt_error($stmt);
        header("Location: ../transfer_edit.php?id=$id");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../transfer.php");
    exit();
}
