<?php
session_start();
require 'database.php';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password']; // use password input field

    $sql = 'SELECT * FROM referee WHERE email = :email';
    $stmt = $connection->prepare($sql);
    $stmt->execute([':email' => $email]);
    $referee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($referee) {
        // Verify password using password_verify()
        if ($password === $referee['password']) {
            $_SESSION['referee_id'] = $referee['referee_id'];
            $_SESSION['name'] = $referee['fname'] . ' ' . $referee['lname'];
            $_SESSION['image'] = $referee['image'];
            $_SESSION['email'] = $referee['email'];

            header("Location: ../referee/index.php");
            exit();
        } else {
            $_SESSION['login_error'] = '<div class="alert alert-danger">Incorrect password.</div>';
            header("Location: ../referee.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = '<div class="alert alert-danger">Email not found.</div>';
        header("Location: ../referee.php");
        exit();
    }
}
?>
