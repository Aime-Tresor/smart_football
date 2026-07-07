<?php
session_start();
require 'init.php';

if (isset($_POST['submit'])) {
    $team_id = $_POST['team_id'];
    $team_name = $_POST['team_name'];
    $stadium  = $_POST['stadium'];
    $username = $_POST['username'];
    $password = md5($_POST['password']); 
    $logon = $_FILES['logon']['name'];
    $temp_n = $_FILES['logon']['tmp_name'];

    // Use old image if no new one uploaded
    if (empty($logon)) {
        $stmt = $connection->prepare("SELECT logon FROM team WHERE team_id = :team_id");
        $stmt->execute([':team_id' => $team_id]);
        $logon = $stmt->fetchColumn();
    } else {
        $store = "../../Logo/" . basename($logon);
        move_uploaded_file($temp_n, $store);
    }

    // 🔎 Check if team name or username already exists (for another team)
    $checkSql = "SELECT COUNT(*) FROM team WHERE (name = :name OR username = :username) AND team_id != :team_id";
    $checkStmt = $connection->prepare($checkSql);
    $checkStmt->execute([
        ':name' => $team_name,
        ':username' => $username,
        ':team_id' => $team_id
    ]);
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        $_SESSION['error'] = "Team name or username already exists.";
        header("Location: ../teams.php?error=exists");
        exit;
    }

    // ✅ Proceed with update
    try {
        $sql = "UPDATE `team` SET `name` = :name, `logon` = :logon, `stadium` = :stadium,
                `username` = :username, `password` = :password
                WHERE team_id = :team_id";
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            ':name' => $team_name,
            ':logon' => $logon,
            ':stadium' => $stadium,
            ':username' => $username,
            ':password' => $password,
            ':team_id' => $team_id
        ]);

        $_SESSION['success'] = "User updated successfully.";
        header("Location: ../teams.php?updated");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "User not updated: " . $e->getMessage();
        header("Location: ../teams.php?error");
        exit;
    }
}
?>
