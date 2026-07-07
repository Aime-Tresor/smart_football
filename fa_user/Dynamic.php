<?php
  require '../app/database.php';
  if (isset($_POST['refeere_id'])) {
    $refeere_id = $_POST['refeere_id'];
    $sql = 'UPDATE referee SET status=1 WHERE referee_id=:refeere_id';
    $statement = $connection->prepare($sql);
    if ($statement->execute([
      ':refeere_id' => $refeere_id
      ])) {
        $sql = 'SELECT * FROM referee WHERE status=0';
        $statement = $connection->prepare($sql);
        $statement->execute();
        $Referees = $statement->fetchAll(PDO::FETCH_OBJ);
        ?>
<select id="select4" name="select4" class="form-control form-control-line">
  <option value="none" selected="" disabled="">Official referee</option>

         <?php foreach($Referees as $Referee): ?>
    <option value="<?= $Referee->referee_id; ?>"><?= $Referee->fname; ?> <?= $Referee->lname; ?></option>
    <?php endforeach; ?>
         </select>

         <?php
         }
  }
?>
<?php 
  if (isset($_POST['official'])) {
    $official = $_POST['official'];
    $sql = 'UPDATE referee SET status=1 WHERE referee_id=:official';
    $statement = $connection->prepare($sql);
    if ($statement->execute([
      ':official' => $official
      ])) {
         }
  }
?>