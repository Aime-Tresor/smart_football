<?php
session_start();
require '../../app/database.php';

if (isset($_POST['submit'])) {
  $fname = $_POST['fname'];
  $lname  = $_POST['lname'];
  $post = $_POST['post'];
  $member_id = $_POST['member_id'];
  $contract_duration = $_POST['contract_duration'];
  $contract_value = $_POST['contract_value'];
 
  try { 
        $sql = 'UPDATE `team_members` SET `fname`=?, `lname`=?, `post`=?, `contract_duration`=?, `contract_value`=? WHERE member_id =?';
        $stmt = $connection->prepare($sql);
        if($stmt->execute([$fname,$lname,$post, $contract_duration,$contract_value,$member_id]))
        {
            $_SESSION['msg'] = '<script>swal("Good job!", "Staff updated Succesfully", "success");</script>';
            header("Location: ../team_member.php?staff_id=$member_id");
      }   
}
catch (PDOException $e) {
  echo $e->getMessage();

   //header("Location: ../team_member.php?error");
  }
}
if (isset($_POST['delete'])) {
    $member_id = $_POST['member_id'];
    $sql = 'DELETE FROM `team_members` WHERE member_id =?';
    $stmt = $connection->prepare($sql);
    if($stmt->execute([$member_id]))
    {
        $_SESSION['msg'] = '<script>swal("Good job!", "Staff Deleted Successfully ", "success");</script>';
        header("Location: ../team_member.php");
  } 
}
?>