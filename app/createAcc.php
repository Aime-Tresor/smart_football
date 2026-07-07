<?php
session_start();
require 'database.php';
if (isset($_POST['submit'])) {
  $names = $_POST['names'];
  $username = $_POST['username'];
  $password = $_POST['password'];

  try {
    $sql = 'INSERT INTO `fa_user`(`names`, `username`, `password`) VALUES (?,?,?)';
    $statement = $connection->prepare($sql);
    if($statement->execute([$names,$username,$password]))
    {    
        $_SESSION['success'] = '<script>swal("Good job!", "Account Created Successful", "Success");</script>';
        echo"<script>window.location=' ../create.php'</script>";
    }
  } 
catch (PDOException $e) {
    $_SESSION['success'] = '<script>swal("Error!", "Check Your Username", "fail");</script>';
    header("Location: ../create.php");
  }
}
?>