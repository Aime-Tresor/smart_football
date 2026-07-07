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
                                <h5 class="card-title">Season Calender</h5>
                            </div>
                            <!-- <div class="ms-auto">
                            <select class="form-control form-control-line" name="season">
                                    <option value="2018 - 2019">2018 - 2019</option>
                                    <option value="2019 - 2020">2019 - 2020</option>
                                    <option value="2021 - 2022" selected>2021 - 2022</option>
                                    <option value="2022 - 2023">2022 - 2023</option>

                                </select>
                                    </div> -->
                        </div>
                        <div class="message-center" style="height: 450px !important;">
                            <div id="select" class="table-responsive mt-3 no-wrap">
                            <?php
                // Get current week from GET or default to current week number
                $currentWeek = isset($_GET['week']) && is_numeric($_GET['week']) ? (int)$_GET['week'] : (int)date('W');

                // Check if previous and next week matches exist
                function weekHasMatches($connection, $week) {
                    $stmt = $connection->prepare('SELECT COUNT(*) FROM `match` WHERE week = :week');
                    $stmt->execute([':week' => $week]);
                    return $stmt->fetchColumn() > 0;
                }

                $prevWeek = $currentWeek - 1;
                $nextWeek = $currentWeek + 1;
                $showPrev = weekHasMatches($connection, $prevWeek);
                $showNext = weekHasMatches($connection, $nextWeek);

                // Fetch matches and join with team table twice for team1 and team2 details
                $sql = "SELECT m.*, 
                            t1.name as team1_name, t1.logon as team1_logo, t1.stadium as team1_stadium,
                            t2.name as team2_name, t2.logon as team2_logo, t2.stadium as team2_stadium
                        FROM `match` m
                        JOIN `team` t1 ON m.team1_id = t1.team_id
                        JOIN `team` t2 ON m.team2_id = t2.team_id
                        WHERE m.week = :week
                        ORDER BY m.match_date, m.match_time";

                $stmt = $connection->prepare($sql);
                $stmt->execute([':week' => $currentWeek]);
                $matches = $stmt->fetchAll(PDO::FETCH_OBJ);
                ?>

<div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
     
        <div>
            <?php if ($showPrev): ?>
                <a href="?week=<?= $prevWeek; ?>" class="btn btn-secondary btn-sm">← Previous Week</a>
            <?php endif; ?>
            <?php if ($showNext): ?>
                <a href="?week=<?= $nextWeek; ?>" class="btn btn-secondary btn-sm">Next Week →</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive no-wrap">
        <table class="table vm no-th-brd pro-of-month">
            <thead>
                <tr>
                    <th>Team 1</th>
                    <th></th>
                    <th>Match Info</th>
                    <th></th>
                    <th>Team 2</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No matches found for this week.</td></tr>
                <?php endif; ?>

                <?php foreach ($matches as $match): ?>
                <tr>
                    <!-- Team 1: logo, name, stadium -->
                    <td style="text-align: right; vertical-align: middle;">
                        <img src="../Logo/<?= htmlspecialchars($match->team1_logo); ?>" alt="<?= htmlspecialchars($match->team1_name); ?> Logo" width="50" height="50" class="rounded-circle"><br>
                        <strong><?= htmlspecialchars($match->team1_name); ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($match->team1_stadium); ?></small>
                    </td>

                    <td></td>

                    <!-- Match info -->
                    <td style="text-align: center; vertical-align: middle;">
                        <div><strong>Date:</strong> <?= htmlspecialchars($match->match_date); ?> At  <?= htmlspecialchars($match->match_time); ?></div>
                        
                        <div><strong>Stadium:</strong> <?= htmlspecialchars($match->stadium); ?></div>
                        <!-- <div><a href="?set=<?= $match->id ?>">Set referee</a></div> -->
                    </td>

                    <td></td>

                    <!-- Team 2: name, stadium, logo -->
                    <td style="text-align: left; vertical-align: middle;">
                        <img src="../Logo/<?= htmlspecialchars($match->team2_logo); ?>" alt="<?= htmlspecialchars($match->team2_name); ?> Logo" width="50" height="50" class="rounded-circle"><br>
                        <strong><?= htmlspecialchars($match->team2_name); ?></strong><br>
                        <small class="text-muted"> <?= htmlspecialchars($match->stadium); ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Column -->
            <!-- Column -->
   

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
    <!-- ============================================================== -->
    <?php require 'footer.php'; ?>