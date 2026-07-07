<?php
session_start();

// Connect using mysqli
$con = mysqli_connect("localhost", "root", "", "fa_db");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $post = $_POST['post'];
    $team_from = $_POST['team_from'];
    $team_to = $_POST['team_to'];
    $status = $_POST['status']; 
    $requestDate = date('Y-m-d');

  
    if ($team_from == $team_to) {
        $_SESSION['error'] = "Team From and Team To cannot be the same.";
        header("Location:../transfer_form.php");
        exit();
    }

    $sql = "INSERT INTO transfer (team_from, team_to, status, requestDate, member_id, post) 
            VALUES ('$team_from', '$team_to', '$status', '$requestDate', '$member_id', '$post')";

    if (mysqli_query($con, $sql)) {
        $_SESSION['success'] = "Transfer request submitted successfully.";
        header("Location: ../transfer_form.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($con);
        header("Location:../transfer_form.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location:../transfer_form.php");
    exit();
}
