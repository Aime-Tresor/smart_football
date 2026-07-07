<?php
require 'init.php';

$week = 1;

$sql = 'SELECT * FROM match_day_reports WHERE week=?';
$stmt = $connection->prepare($sql);
$stmt->execute([$week]);
$connection->beginTransaction();
$Teams = $stmt->fetchAll(PDO::FETCH_OBJ);
 foreach($Teams as $team){

      $member = $team->team_member;
      $card = $team->card;
$sql1 ="UPDATE `team_members` SET red=7,yellow=7 WHERE member_id ='{$member}';";
$connection->exec($sql1);

  }
 $connection->commit();