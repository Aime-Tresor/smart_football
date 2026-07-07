<?php
session_start();
require 'database.php';
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
 $sql1 = 'SELECT * FROM fa_user WHERE username=? AND password = ?';
 $stmt = $connection->prepare($sql1);
 $stmt->execute([$username,$password]);
 $row = $stmt->rowCount();
 if ($row > 0) {
 while($data=$stmt->fetch(PDO::FETCH_ASSOC))  {                          
        $_SESSION['fa_user'] = $data['username'];
        header("Location: ../fa_user/");
 }       
 }
 else
 {
    $_SESSION['msg'] = '<script>swal("Error!", "Wrong Username or Password", "fail");</script>';
    header("Location: ../");
 }
}