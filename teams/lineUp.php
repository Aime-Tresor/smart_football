<?php require_once 'header.php'; ?>
<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
  
        <!-- ============================================================== -->
        <!-- Notification And Feeds -->
        <!-- ============================================================== -->
        <div class="row">

            <!-- Start Feeds -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Player</h5>
                        <?php
                                   // Suspended players (5+ yellows, or any red/double-yellow) must not be
                                   // selectable to play a match at all - excluded here rather than just
                                   // disabling their "add" button, so they never appear in this list.
                                   $eligiblePlayers = \App\ServiceFactory::teamMemberRepository()
                                       ->findEligiblePlayers((string) $_SESSION['Team_id']);
                                   foreach ($eligiblePlayers as $team):
                                       $team = (object) $team;
                                   ?>
                        <div class="d-flex flex-row comment-row m-t-0">
                            <div class="p-2"><span class="round round-info"><?= $team->number; ?></span></div>
                            <div class="comment-text">
                                <h6 class="font-medium"><?= $team->fname; ?> <?= $team->lname; ?></h6>

                                <div class="comment-footer">
                                    <span class="badge bg-info">Allowed</span>
                                    <span class="action-icons">
                                        <a href="?add=<?= $team->member_id; ?>&week=<?= $_GET['week'] ?>"><i class="fa fa-plus-square-o"></i></a>
                                    </span>
                                    <span class="text-muted float-end"><?= $team->position; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($eligiblePlayers)): ?>
                            <p class="text-muted">No eligible players available (all are suspended).</p>
                        <?php endif; ?>


                    </div>

                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Staff</h5>
                        <?php
                                   $sql = 'SELECT * FROM `team_members` WHERE role_in_team="staff" AND team=?';
                                   $statement = $connection->prepare($sql);
                                   $statement->execute([$_SESSION['Team_id']]);
                                   $Teams = $statement->fetchAll(PDO::FETCH_OBJ);
                                   foreach($Teams as $team):
                                   
                                   ?>
                        <div class="d-flex flex-row comment-row m-t-0">
                            <div class="comment-text">
                                <h6 class="font-medium"><?= $team->fname; ?> <?= $team->lname; ?></h6>

                                <div class="comment-footer">
                                <?php 
                                    if ($team->yellow >= 5) {
                                        echo '<span title="Due to 5 Yellow cards" class="badge bg-danger">Suspend</span>';
                                    }
                                    elseif ($team->double_yellow == 2) {
                                        echo '<span title="Last Days you have Double Yellows" class="badge bg-danger">Suspend</span>';
                                    }
                                    elseif ($team->red >= 2) {
                                        echo '<span title="Have Red Card in Previous Match" class="badge bg-danger">Suspend</span>';
                                    }
                                    else {
                                        echo '<span class="badge bg-info">Allowed</span>';
                                       ?>
                                        <span class="action-icons">
                                        <a href="?add=<?= $team->member_id; ?>&week=<?= $_GET['week'] ?>"><i class="fa fa-plus-square-o"></i></a>
                                    </span>
                                       <?php
                                    }
                                    ?>
                                    <span class="text-muted float-end"><?= $team->post; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Player</h5>
                        <?php
                                   $sql = 'SELECT * FROM `team_members` INNER JOIN match_day_reports ON team_members.role_in_team="player" 
AND match_day_reports.team=? AND match_day_reports.team_member = team_members.member_id AND match_day_reports.week=?';
                                   $statement = $connection->prepare($sql);
                                   $statement->execute([$_SESSION['Team_id'],$_GET['week']]);
                                   $Teams = $statement->fetchAll(PDO::FETCH_OBJ);
                                   foreach($Teams as $team):
                                   ?>
                        <div class="d-flex flex-row comment-row m-t-0">
                            <div class="p-2"><span class="round round-info"><?= $team->number; ?></span></div>
                            <div class="comment-text">
                                <h6 class="font-medium"><?= $team->fname; ?> <?= $team->lname; ?></h6>

                                <div class="comment-footer">
                                <span class="badge bg-success">On squard</span>
                             <span class="action-icons">
                               
                                        <a href="?delete=<?= $team->member_id; ?>&week=<?= $_GET['week'] ?>"><i class="fa fa-minus-square-o"></i></a>
                                    </span>
                                    <span class="text-muted float-end"><?= $team->position; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Staff</h5>
                        <?php
                                  $sql = 'SELECT * FROM `team_members` INNER JOIN match_day_reports ON team_members.role_in_team="staff" 
AND match_day_reports.team=? AND match_day_reports.team_member = team_members.member_id AND match_day_reports.week=?';
                                   $statement = $connection->prepare($sql);
                                   $statement->execute([$_SESSION['Team_id'],$_GET['week']]);
                                   $Teams = $statement->fetchAll(PDO::FETCH_OBJ);
                                   foreach($Teams as $team):
                                   ?>
                        <div class="d-flex flex-row comment-row m-t-0">
                            <div class="comment-text">
                                <h6 class="font-medium"><?= $team->fname; ?> <?= $team->lname; ?></h6>

                                <div class="comment-footer">
                                    <span class="badge bg-success">staff</span>
                                    <span class="action-icons"> <a href="?delete=<?= $team->member_id; ?>&week=<?= $_GET['week'] ?>"><i class="fa fa-minus-square-o"></i></a>
                                    </span>
                                    <span class="text-muted float-end"><?= $team->post; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>

                </div>
            </div>

            <!-- End Feeds -->
        </div>
        <!-- ============================================================== -->
        <!-- End Notification And Feeds -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- End Page Content -->
        <!-- ============================================================== -->
    </div>
    <?php
if (isset($_GET['add']) && isset($_GET['week'])) {
    $mambre = $_GET['add'];
    $week = $_GET['week'];
    try {
        $sql = "SELECT team_member,week FROM match_day_reports WHERE team_member=? AND week=?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$mambre,$week]);
        $row = $stmt->rowCount();
        if ($row > 0) {
            echo"<script>window.location=' lineUp.php?week=$week&exit'</script>";
        } else {
          $sql = 'INSERT INTO `match_day_reports`(`team_member`, `team`, `week`) VALUES (?,?,?)';
          $stmt = $connection->prepare($sql);
          if($stmt->execute([$mambre,$_SESSION['Team_id'],$week]))
          {
            echo"<script>window.location=' lineUp.php?week=$week'</script>";
        } 
        }
  }
  catch (PDOException $e) {
    echo $e->getMessage();
  
     //header("Location: ../team_member.php?error");
    }
    
}
?>
    <?php
if (isset($_GET['delete']) && isset($_GET['week'])) {
    $mambre = $_GET['delete'];
    $week = $_GET['week'];
    try {
       
          $sql = 'DELETE FROM match_day_reports WHERE team_member=? AND team=? AND week=?';
          $stmt = $connection->prepare($sql);
          if($stmt->execute([$mambre,$_SESSION['Team_id'],$week]))
          {
            echo"<script>window.location=' lineUp.php?week=$week&remove'</script>";
        } 
  }
  catch (PDOException $e) {
    echo $e->getMessage();
  
     //header("Location: ../team_member.php?error");
    }
    
}
?>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <?php require 'footer.php'; ?>