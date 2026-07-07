<?php require 'header.php'; ?>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">

            <!-- MATCH FIXTURES Section -->
            <div class="col-lg-4 col-md-12">
                <div class="card card-body mailbox">
                    <h5 class="card-title">All Fixtures</h5>
                    <div class="message-center" style="height: 420px !important;">
                        <?php
                        $sql = "SELECT m.*, 
                                       t1.name AS team1_name, t1.logon AS team1_logo,
                                       t2.name AS team2_name, t2.logon AS team2_logo
                                FROM `match` m
                                JOIN team t1 ON m.team1_id = t1.team_id
                                JOIN team t2 ON m.team2_id = t2.team_id
                                WHERE m.team1_id = ? OR m.team2_id = ?
                                ORDER BY m.week ASC";
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$_SESSION['Team_id'], $_SESSION['Team_id']]);
                        $matches = $stmt->fetchAll(PDO::FETCH_OBJ);

                        foreach ($matches as $match) {
                            $isHome = $match->team1_id == $_SESSION['Team_id'];
                            $opponentName = $isHome ? $match->team2_name : $match->team1_name;
                            $opponentLogo = $isHome ? $match->team2_logo : $match->team1_logo;
                            $link = ($match->status == 'upcoming') ? "lineUp.php?week={$match->week}" : "#";
                        ?>
                            <a href="<?= $link ?>">
                                <span class="round" style="background:white;">
                                    <img src="../Logo/<?= htmlspecialchars($opponentLogo) ?>" alt="Logo" width="50" height="50">
                                </span>
                                <div class="mail-contnet">
                                    <h6 class="text-dark font-medium mb-0"><?= htmlspecialchars($opponentName) ?></h6>
                                    <span class="mail-desc">Week <?= $match->week ?> Fixture</span>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- PLAYER Section -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">PLAYERS</h5>
                        <?php
                        $sql = 'SELECT * FROM team_members WHERE role_in_team = "player" AND team = ?';
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$_SESSION['Team_id']]);
                        $players = $stmt->fetchAll(PDO::FETCH_OBJ);
                        foreach ($players as $player):
                            $status = ($player->yellow >= 5 || $player->double_yellow > 0 || $player->red > 0) ? 'Suspend' : 'Allowed';
                            ?>
                            <div class="d-flex flex-row comment-row m-t-0">
                                <div class="p-2"><span class="round round-info"><?= $player->number ?></span></div>
                                <div class="comment-text">
                                    <h6 class="font-medium"><?= $player->fname ?> <?= $player->lname ?></h6>
                                    <div class="comment-footer">
                                        <span class="badge <?= $status == 'Suspend' ? 'bg-danger' : 'bg-info' ?>">
                                            <?= $status ?>
                                        </span>
                                        <span class="badge badge-danger"><?= $player->red > 0 ? 1 : 0 ?></span>
                                        <span class="badge text-info" style="background-color:#ffff1a;">
                                            <?= $player->yellow ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- STAFF Section -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">STAFF</h5>
                        <?php
                        $sql = 'SELECT * FROM team_members WHERE role_in_team = "staff" AND team = ?';
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$_SESSION['Team_id']]);
                        $staff = $stmt->fetchAll(PDO::FETCH_OBJ);
                        foreach ($staff as $member):
                            $status = ($member->yellow >= 5 || $member->double_yellow > 0 || $member->red > 0) ? 'Suspend' : 'Allowed';
                            ?>
                            <div class="d-flex flex-row comment-row m-t-0">
                                <div class="p-2"><span class="round round-info"><?= $member->post ?></span></div>
                                <div class="comment-text">
                                    <h6 class="font-medium"><?= $member->fname ?> <?= $member->lname ?></h6>
                                    <div class="comment-footer">
                                        <span class="badge <?= $status == 'Suspend' ? 'bg-danger' : 'bg-info' ?>">
                                            <?= $status ?>
                                        </span>
                                        <span class="badge badge-danger"><?= $member->red > 0 ? 1 : 0 ?></span>
                                        <span class="badge text-info" style="background-color:#ffff1a;">
                                            <?= $member->yellow ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
