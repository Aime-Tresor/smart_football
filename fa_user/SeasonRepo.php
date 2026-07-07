<?php require_once 'header.php'; ?>

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->

        <div class="row">
            <!-- Column -->
            <div class="col-lg-8 d-flex align-items-stretch">
                <div class="card w-100">
                    <div class="card-body">

                        <div class="d-flex">
                            <div>
                                <h5 class="card-title">All Season records</h5>
                            </div>
                            <div class="auto">
                                <?php
                            if (isset($_SESSION['error'])) {
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            }
                            ?>
                            </div>
                        </div>
                        <div class="message-center" style="height: 450px !important;">
                        <div class="table-responsive no-wrap">
                            <table class="table vm no-th-brd pro-of-month">
                                <tbody>
                                    <?php
                                        $sql = 'SELECT * FROM `calender` where status="terminated"';
                                        $statement = $connection->prepare($sql);
                                        $statement->execute();
                                        $Teams = $statement->fetchAll(PDO::FETCH_OBJ);
                                        foreach($Teams as $team):
                                            $week = $team->week;
                                        ?>
                                    <tr class="active">
                                        <td style="text-align: right;">
                                            <?php
                                            $sql1 = 'SELECT team_id,name,logon FROM team WHERE name=:name';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([':name' => $team->home]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC)){
                                            $logo = $data['logon'];
                                            $team_a_id = $data['team_id'];
                                         }
                                            ?>
                                            <span class="round"><img src="../Logo/<?= $logo; ?>" alt="user" width="50"
                                                    height="50">
                                            </span>

                                        </td>
                                        <td style="text-align: left;">
                                            <h4 class="mt-2"><a href="?team=<?= $team_a_id; ?>"><?= $team->home; ?></a>
                                            </h4>
                                            <span class="mail-desc">Cards </span>
                                            <?php
                                            $sql1 = 'SELECT COUNT(card) AS totalYellow FROM match_day_reports WHERE card="Yellow" AND week=? AND team=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team->week,$team_a_id]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $yellow = $data['totalYellow'];      
                                            ?>
                                            <?php
                                            $sql1 = 'SELECT COUNT(card) AS doubleCard FROM match_day_reports WHERE card="Double" AND week=? AND team=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team->week,$team_a_id]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $Double = $data['doubleCard'];      
                                            ?>
                                            <?php
                                            $sql1 = 'SELECT COUNT(card) AS totalred FROM match_day_reports WHERE card="Red" AND week=? AND team=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team->week,$team_a_id]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $redcard = $data['totalred'];      
                                            ?>
                                            <span class="badge text-info"
                                                style="background-color:#ffff1a;"><?= $yellow; ?> </span>
                                            <span class="badge badge-danger"><?= $redcard + $Double; ?></span>
                                        </td>

                                        <td style="text-align: center;">
                                            <?php
                                            $sql1 = 'SELECT SUM(goal) AS T_goal FROM match_day_reports WHERE team=? AND week=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team_a_id,$team->week]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $T_goal = $data['T_goal'];
                                            ?>
                                            <h4><?= $T_goal; ?></h4>

                                        </td>
                                        <td style="text-align: center;">
                                            <h6>VS</h6>

                                        </td>
                                        <?php
                                            $sql1 = 'SELECT team_id,name,logon FROM team WHERE name=:name';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([':name' => $team->away]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC)){
                                            $logo = $data['logon'];
                                            $team_b_id = $data['team_id'];
                                        }
                                            ?>
                                        <td style="text-align: center;">
                                            <?php
                                            $sql1 = 'SELECT SUM(goal) AS T_goal FROM match_day_reports WHERE team=? AND week=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team_b_id,$team->week]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $T_goal = $data['T_goal'];
                                            ?>
                                            <h4><?= $T_goal; ?></h4>

                                        </td>

                                        <td style="text-align: right;">
                                            <h4 class="mt-2"><a href="?team=<?= $team_b_id; ?>"><?= $team->away; ?></a>
                                            </h4>

                                            <?php
                                            $sql1 = 'SELECT COUNT(card) AS totalYellow FROM match_day_reports WHERE card="Yellow" AND week=? AND team=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team->week,$team_b_id]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $yellow = $data['totalYellow'];
                                            ?>
                                            <?php
                                            $sql1 = 'SELECT COUNT(card) AS doubleCard FROM match_day_reports WHERE card="Double" AND week=? AND team=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team->week,$team_b_id]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $Double = $data['doubleCard'];      
                                            ?>
                                            <?php
                                            $sql1 = 'SELECT COUNT(card) AS totalred FROM match_day_reports WHERE card="Red" AND week=? AND team=?';
                                            $stmt = $connection->prepare($sql1);
                                            $stmt->execute([$team->week,$team_b_id]);
                                            while($data=$stmt->fetch(PDO::FETCH_ASSOC))
                                            $redcard = $data['totalred'];      
                                            ?>
                                            <span class="badge badge-danger"><?= $redcard + $Double; ?></span>
                                            <span class="badge text-info"
                                                style="background-color:#ffff1a;"><?= $yellow; ?></span>
                                            <span class="mail-desc">Cards </span>
                                        </td>
                                        <td style="text-align: left;">
                                            <span class="round"><img src="../Logo/<?= $logo; ?>" alt="user" width="50"
                                                    height="50">
                                            </span>

                                        </td>
                                        <td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Column -->
            <!-- Column -->
            <div class="col-lg-4 col-md-12">
                <?php if (isset($_GET['team'])) {
                $team_id = $_GET['team'];
                ?>
                <div class="card card-body mailbox">
                    <h5 class="card-title">team Squard</h5>
                    <div class="message-center" style="height: 420px !important;">
                        <?php
                    $sql = 'SELECT * FROM team_member INNER JOIN match_day_reports ON 
                    team_member.member_id = match_day_reports.team_member AND match_day_reports.week=? 
                    AND match_day_reports.team=? AND team_member.role_in_team="player"';
                    $statement = $connection->prepare($sql);
                    $statement->execute([$team->week,$team_id]);
                    $Teams = $statement->fetchAll(PDO::FETCH_OBJ);
                    foreach($Teams as $team):
                    ?>
                        <a href="?member=<?= $team->member_id; ?>">
                            <span class="round"><?= $team->number; ?></span>
                            <div class="mail-contnet">
                                <h6 class="text-dark font-medium mb-0"><?= $team->fname; ?> <?= $team->lname; ?></h6>
                                <span class="mail-desc">Cards:
                                    <?php 
                                    if ($team->card == 'Yellow') {
                                    echo ' <span class="badge badge-warning">1</span>';
                                    }
                                    elseif ($team->card == 'Double') {
                                    echo ' <span class="badge badge-warning">2</span>';
                                    }
                                    elseif ($team->card == 'Red') {
                                    echo '<span class="badge badge-danger">1</span>';
                                    }
                                    else
                                    {
                                        echo '';
                                    }
                            
                                ?>
                                    <?= $team->card_min; ?> Min
                                </span>
                                <span class="mail-desc">Goals:
                                    <span class="badge badge-info"><?= $team->goal; ?></span>
                                    <?= $team->goal_min; ?> Min
                                </span>

                            </div>
                        </a>
                        <?php endforeach; ?>
                        <br>
                        <h5 class="card-title">Staff</h5>
                        <?php
                    $sql = 'SELECT * FROM team_member INNER JOIN match_day_reports ON 
                    team_member.member_id = match_day_reports.team_member AND match_day_reports.week=? 
                    AND match_day_reports.team=? AND team_member.role_in_team="staff"';
                    $statement = $connection->prepare($sql);
                    $statement->execute([$team->week,$team_id]);
                    $Teams = $statement->fetchAll(PDO::FETCH_OBJ);
                    foreach($Teams as $team):
                    ?>
                        <a href="?member=<?= $team->member_id; ?>">
                            <span class="round"><?= $team->post; ?></span>
                            <div class="mail-contnet">
                                <h6 class="text-dark font-medium mb-0"><?= $team->fname; ?> <?= $team->lname; ?></h6>
                                <span class="mail-desc">Cards:
                                    <?php 
                                    if ($team->card == 'Yellow') {
                                    echo ' <span class="badge badge-warning">1</span>';
                                    }
                                    elseif ($team->card == 'Double') {
                                    echo ' <span class="badge badge-warning">2</span>';
                                    }
                                    elseif ($team->card == 'Red') {
                                    echo '<span class="badge badge-danger">1</span>';
                                    }
                                    else
                                    {
                                        echo '';
                                    }
                            
                                ?>
                                    <?= $team->card_min; ?> Min
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>

                </div>

                <?php }
                elseif(isset($_GET['member']))
                {
                 $member = $_GET['member'];
                    ?>
                <div class="card card-body mailbox">
                    <div class="m-4">
                        <?php
                            $sql1 = 'SELECT * FROM team_member WHERE member_id=?';
                            $stmt = $connection->prepare($sql1);
                            $stmt->execute([$member]);
                            while($data=$stmt->fetch(PDO::FETCH_ASSOC)){
                            $fname = $data['fname'];
                            $lname = $data['lname'];
                            $number = $data['number'];
                            $post= $data['post'];
                        }
                            ?>
                        <div class="mail-contnet">
                            <h6 class="text-dark font-medium mb-0"> <span
                                    class="badge badge-info"><?= $number; ?></span>
                                <?= $fname; ?> <?= $lname; ?></h6>
                        </div>
                        <hr>
                    </div>
                    <form class="form-horizontal mx-2" method="post" action="controls/fa_punishs.php">
                        <input type="hidden" name="member_id" value="<?= $member ?>">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <select name="match" class="form-control form-control-line">
                                        <option value="" selected disabled>Choose Punishment</option>
                                        <option value="1">Miss One Match</option>
                                        <option value="2">Miss Two Match</option>
                                        <option value="3">Miss Three Match</option>
                                        <option value="4">Miss Four Match</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <div class="col-sm-12">
                                <button name="punish" class="btn btn-success">Punishs</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php }
                else {
                ?>
                <div class="card card-body mailbox">
                    <center class="mt-4">
                        <img src="../Logo/Ferwafa_logo.png" class="img-circle" width="150" />
                        <h4 class="card-title mt-2">Primus National League</h4>
                        <div class="row text-center justify-content-md-center">
                            <div class="col-2"></div>
                            <div class="col-2"></div>

                        </div>
                    </center>
                </div>
                <?php 
              } ?>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- End Notification And Feeds -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- End Page Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <?php require 'footer.php'; ?>