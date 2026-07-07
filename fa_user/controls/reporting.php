<?php
require 'init.php';

$week = $_POST['week'];
// same validation
$sql1 = 'SELECT * FROM match_day_reports WHERE week=?';
$stmt = $connection->prepare($sql1);
$stmt->execute([$week]);
$row = $stmt->rowCount();
if ($row > 0) {

// Remove ibihano Byatanzwe Previous Day
$sql1 = 'UPDATE `team_member` SET red=0 WHERE red = 1';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
$sql1 = 'UPDATE `team_member` SET red=1 WHERE red = 2';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
$sql1 = 'UPDATE `team_member` SET red=2 WHERE red = 3';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
$sql1 = 'UPDATE `team_member` SET red=3 WHERE red = 4';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
$sql1 = 'UPDATE `team_member` SET double_yellow=0 WHERE double_yellow = 1';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
$sql1 = 'UPDATE `team_member` SET yellow=0 WHERE yellow= 5';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
// Remove ibihano Byatanzwe Previous Day

// Set Status That MatchDay Terminated

$sql1 = 'UPDATE `calender` SET status="terminated" WHERE week = ?';
$stmt = $connection->prepare($sql1);
$stmt->execute([$week]);

// Set Status of Next game
$NextWeek = $week + 1;
$sql1 = 'UPDATE `calender` SET status="next" WHERE week = ?';
$stmt = $connection->prepare($sql1);
$stmt->execute([$NextWeek]);

// Reset All Referee
$sql1 = 'UPDATE `referee` SET status=0';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 

// Red Card Roles
$sql1 = 'UPDATE `team_member` SET red=2 WHERE red=3';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
// Double Yellow Card Roles
$sql1 = 'UPDATE `team_member` SET double_yellow=1 WHERE double_yellow=2';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 
// Yellow Card Roles
$sql1 = 'UPDATE `team_member` SET yellow=5 WHERE yellow=6';
$stmt = $connection->prepare($sql1);
$stmt->execute(); 

// Update referee Access Code
$sql1 = 'UPDATE `weekly_fixtures` SET access_code=NULL';
$stmt = $connection->prepare($sql1);
$stmt->execute();

header("Location: ../fixtureReport.php");
}
else {
    header("Location: ../fixtureReport.php");
    $_SESSION['error'] = "<span class='text-danger'>No report</span>";
}


