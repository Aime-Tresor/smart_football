<?php
require 'init.php';
if (isset($_POST['punish'])) {
  $member_id = $_POST['member_id'];
  $match = $_POST['match'];

  try {
    $sql = 'UPDATE `team_member` SET `red`=? WHERE member_id=?';
    $stmt = $connection->prepare($sql);
    if($stmt->execute([$match, $member_id]))
    {
       header("Location: ../fixtureReport.php");
  } 
}
catch (PDOException $e) {
  echo $e->getMessage();

  // header("Location: ../fixtureReport.php?error");
  }
   
 }
?>