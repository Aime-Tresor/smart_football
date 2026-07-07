<?php
require 'init.php';
include "mail.php";
if (isset($_POST['submit'])) {
    $Referee = $_POST['select1'];
    $Official = $_POST['select4'];
    $match_id = $_POST['match_id'];

        $sql1 = 'SELECT * FROM calender WHERE id=?';
        $stmt = $connection->prepare($sql1);
        $stmt->execute([$match_id]);
        while($data=$stmt->fetch(PDO::FETCH_ASSOC)){
        $home = $data['home'];
        $away = $data['away'];
        $stadium = $data['stadium'];
        $date = $data['date'];
        $time = $data['time'];
        $week = $data['week'];
      }

        $sql1 = 'SELECT * FROM referee WHERE referee_id=?';
        $stmt = $connection->prepare($sql1);
        $stmt->execute([$Official]);
        while($data=$stmt->fetch(PDO::FETCH_ASSOC))
        $email = $data['email'];

        $access_Code = (rand(10,1000000)); 

  try {
    $sql = 'INSERT INTO weekly_fixtures(match_id, referee, official, access_code) VALUES (?,?,?,?)';
    $stmt = $connection->prepare($sql);
    if($stmt->execute([$match_id,$Referee,$Official,$access_Code]))
    {
     send_mail($recipient,$subject,$message);
      $recipient =  $email;
      $subject = "Rwanda Primus national League";
      $message ="";
      $message .= '<h3>Match Day '.$week.' Fixture</h3>';
      $message .= '<h4>'.$home. '<small> VS </small>'.$away.'</h4>';
      $message .= '<strong>Stadium: </strong>'.$stadium.'<br>';
      $message .= '<strong>Date: </strong>'.$date.'<br>';
      $message .= '<strong>Time: </strong>'.$time.' <br>';
      $message .= 'Match Access Code: <strong>'.$access_Code.'</strong>';
      if(send_mail($recipient,$subject,$message))
      {    
        header("Location: ../fixture.php?set=$match_id");
          }
      else {      
        header("Location: ../fixture.php?Email_error");
          }  

  } 
}
catch (PDOException $e) {
  //echo $e->getMessage();
header("Location: ../fixture.php?error");
  }
   
 }
?>