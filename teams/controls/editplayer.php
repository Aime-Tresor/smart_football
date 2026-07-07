<?php
session_start();
require '../../app/database.php';

if (isset($_POST['submit'])) {
  $fname = $_POST['fname'];
  $lname  = $_POST['lname'];
  $position = $_POST['position'];
  $number =  $_POST['number'];
  $member_id = $_POST['member_id'];
  $contract_duration = $_POST['contract_duration'];
  $contract_value = $_POST['contract_value'];
  $team_id = $_POST['team_id'];

  try {

    $sql = "SELECT number,team,member_id FROM team_members WHERE team=? AND number=? AND member_id != ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$team_id, $number, $member_id]);
    $row = $stmt->rowCount();
    if ($row > 0) {
      $_SESSION['msg'] = '<script>swal("Warning!", "Number '. $number .' Arleady Token", "warning");</script>';
      header("Location: ../team_member.php?player_id=$member_id");
    } else {
      $sql = 'UPDATE `team_members` SET `fname`=?, `lname`=?, `number`=?, `position`=?, `contract_duration`=?, `contract_value`=? WHERE member_id =?';
      $stmt = $connection->prepare($sql);
      if ($stmt->execute([$fname, $lname, $number, $position, $contract_duration, $contract_value, $member_id])) {
        $_SESSION['msg'] = '<script>swal("Good job!", "Player updated Succesfully here", "success");</script>';
        header("Location: ../team_member.php?player_id=$member_id");
      }
    }
  } catch (PDOException $e) {
    echo $e->getMessage();

    //header("Location: ../team_member.php?error");
  }
}
if (isset($_POST['delete'])) {
  $member_id = $_POST['member_id'];
  $sql = 'DELETE FROM `team_members` WHERE member_id =?';
  $stmt = $connection->prepare($sql);
  if ($stmt->execute([$member_id])) {
    $_SESSION['msg'] = '<script>swal("Good job!", "Player Deleted Successfully ", "success");</script>';
    header("Location: ../team_member.php");
  }
}
