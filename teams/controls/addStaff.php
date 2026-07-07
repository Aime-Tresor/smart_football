<?php
session_start();
require '../../app/database.php';

if (isset($_POST['submit'])) {
  $fname = $_POST['fname'];
  $lname  = $_POST['lname'];
  $post =  $_POST['post'];
  $role = 'staff';
  $team_id = $_POST['team_id'];
  $contract_duration = $_POST['contract_duration'];
  $contract_value = $_POST['contract_value'];

  try {
    $sql = 'INSERT INTO `team_members`(`fname`, `lname`,role_in_team ,`post`, `team`,`contract_duration`, `contract_value`) VALUES (?,?,?,?,?,?,?)';
    $stmt = $connection->prepare($sql);
    if($stmt->execute([$fname,$lname,$role,$post,$team_id,$contract_duration,$contract_value]))
    {
      $_SESSION['msg'] = '<script>swal("Good job!", "Staff updated Succesfully", "success");</script>';

        header("Location: ../team_member.php?staff");
  } 
}
catch (PDOException $e) {
  echo $e->getMessage();

  // header("Location: ../team_member.php?error");
  }
   
 }
?>