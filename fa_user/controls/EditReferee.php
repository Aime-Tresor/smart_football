<?php
session_start();
require 'init.php';
if (isset($_POST['submit'])) { 
  $referee_id = $_POST['referee_id'];
  $fname = $_POST['fname'];
  $lname  = $_POST['lname'];
  $email =  $_POST['email'];
  $profile=$_FILES['profile']['name'];
  $temp_n=$_FILES['profile']['tmp_name'];
  $store ="../../Profile/" .basename($_FILES['profile']['name']);
  move_uploaded_file($temp_n, $store);

  try {
    $sql = 'UPDATE referee SET fname=?, lname=?, image=? ,email=? WHERE referee_id=?';
    $stmt = $connection->prepare($sql);
    if($stmt->execute([$fname, $lname, $profile, $email,$referee_id]))
    {
    $_SESSION['success'] = "Referee info updated.";
    header("Location: ../referee.php?updated");
  } 
}
catch (PDOException $e) {
  //echo $e->getMessage();
  $_SESSION['error'] = "Referee info did not updated .";
header("Location: ../referee.php?error");
  }
   
 }
?>