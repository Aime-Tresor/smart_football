<?php
session_start();
require '../../app/database.php';

if (isset($_POST['submit'])) {
  $fname = $_POST['fname'];
  $lname  = $_POST['lname'];
  $number =  $_POST['number'];
  $position = $_POST['position'];
  $role = 'player';
  $team_id = $_POST['team_id'];
  $contract_duration = $_POST['contract_duration'];
  $contract_value = $_POST['contract_value'];

  try {
    $sql = "SELECT number,team FROM team_members WHERE team=? AND number=?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$team_id, $number]);
    $row = $stmt->rowCount();
    if ($row > 0) {
      $_SESSION['msg'] = '<script>swal("Warning!", "Number ' . $number . ' Arleady Token", "warning");</script>';
      header("Location: ../team_member.php");
    } else {
      $sql = 'INSERT INTO `team_members`(`fname`, `lname`, `number`,role_in_team,`position`, `team`,`contract_duration`,`contract_value`) 
        VALUES (?,?,?,?,?,?,?,?)';
      $stmt = $connection->prepare($sql);
      if ($stmt->execute([$fname, $lname, $number, $role, $position, $team_id, $contract_duration, $contract_value])) {
        $_SESSION['msg'] = '<script>swal("Good job!", "Player updated Succesfully", "success");</script>';

        header("Location: ../team_member.php?player");
      }
    }
  } catch (PDOException $e) {
    echo $e->getMessage();

    //header("Location: ../team_member.php?error");
  }
}
