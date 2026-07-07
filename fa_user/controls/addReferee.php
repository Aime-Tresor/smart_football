<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'init.php';

if (isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $lname  = $_POST['lname'];
    $email =  $_POST['email'];
    $password=$_POST['password'];
    $profile = $_FILES['profile']['name'];
    $temp_n = $_FILES['profile']['tmp_name'];
    $targetDir = "../../Profile/";

    // Ensure directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Check if email already exists
    $checkSql = "SELECT COUNT(*) FROM referee WHERE email = :email";
    $checkStmt = $connection->prepare($checkSql);
    $checkStmt->execute([':email' => $email]);
    $emailExists = $checkStmt->fetchColumn();

    if ($emailExists) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: ../referee.php?error=email_exists");
        exit;
    }

    $store = $targetDir . basename($profile);
    move_uploaded_file($temp_n, $store);

    try {
        $status = 'active'; // Add default status
        $sql = 'INSERT INTO `referee`(`fname`, `lname`, `image`, `email`,`password`,`status`) 
                VALUES (:fname, :lname, :image, :email,:password, :status)';
        $stmt = $connection->prepare($sql);
        if ($stmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':image' => $profile,
            ':email' => $email,
            ':password'=>$password,
            ':status' => $status
        ])) {
            $_SESSION['success'] = "Referee has been saved.";
            header("Location: ../referee.php?registered");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Referee was not saved: " . $e->getMessage();
        header("Location: ../referee.php?error");
        exit;
    }
}
?>
