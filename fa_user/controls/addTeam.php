<?php
session_start();
require 'init.php';

if (isset($_POST['submit'])) {
    $team_name = $_POST['team_name'];
    $stadium = $_POST['stadium'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $logon = $_FILES['logon']['name'];
    $temp_n = $_FILES['logon']['tmp_name'];
    $targetDir = "../../Logo/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $store = $targetDir . basename($logon);
    move_uploaded_file($temp_n, $store);

    try {
        // Check if team name already exists
        $checkNameSql = "SELECT COUNT(*) FROM team WHERE name = :name";
        $checkNameStmt = $connection->prepare($checkNameSql);
        $checkNameStmt->execute([':name' => $team_name]);
        $nameExists = $checkNameStmt->fetchColumn();

        // Check if username already exists
        $checkUserSql = "SELECT COUNT(*) FROM team WHERE username = :username";
        $checkUserStmt = $connection->prepare($checkUserSql);
        $checkUserStmt->execute([':username' => $username]);
        $usernameExists = $checkUserStmt->fetchColumn();

        if ($nameExists) {
            $_SESSION['error'] = "Team name already exists.";
            header("Location: ../teams.php?error=name_exists");
            exit;
        }

        if ($usernameExists) {
            $_SESSION['error'] = "Username already exists.";
            header("Location: ../teams.php?error=username_exists");
            exit;
        }

        // MD5 hash password
        $hashedPassword = md5($password);

        // Insert new team
        $sql = 'INSERT INTO `team`(`name`, `logon`, `stadium`, `username`, `password`) 
                VALUES (:name, :logon, :stadium, :username, :password)';
        $stmt = $connection->prepare($sql);
        if ($stmt->execute([
            ':name' => $team_name,
            ':logon' => $logon,
            ':stadium' => $stadium,
            ':username' => $username,
            ':password' => $hashedPassword
        ])) {
            $_SESSION['success'] = "Team registered successfully.";
            header("Location: ../teams.php?registered");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../teams.php?error");
        exit;
    }
}
?>
