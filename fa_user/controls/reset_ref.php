<?php
require 'init.php';
if (isset($_POST['submit'])) {
    $Referee = $_POST['select1'];
    $Official = $_POST['select4'];
    $match_id = $_POST['match_id'];

  try {
    $sql = 'UPDATE referee SET status=0 WHERE referee_id=? OR referee_id=?';
    $stmt = $connection->prepare($sql);
    if($stmt->execute([$Referee,$Official]))
    {

        $sql="DELETE FROM weekly_fixtures WHERE match_id=?";
        $statement = $connection->prepare($sql);
        if($statement->execute([$match_id]))
        {
            header("Location: ../fixture.php?set=$match_id");
        }      
  } 
}
catch (PDOException $e) {
  //echo $e->getMessage();
header("Location: ../fixture.php?error");
  }
   
 }
?>