<?php 
session_start();
require_once 'header.php';
$loggedUserTeamId = $_SESSION['team_id'] ?? null;

?>
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="card-title mb-4 text-primary">
                    <i class="fa fa-random me-2"></i> Transfer Player/Staff
                </h4>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <form action="controls/addTransfer.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Member</label>
                            <select name="member_id" class="form-select" required>
    <option value="">-- Choose Player / Staff --</option>
    <?php
    // Use the correct session key
    $loggedUserTeamId = $_SESSION['Team_id'] ?? null;

    if ($loggedUserTeamId) {
        // Prepare and execute query
        $stmt = $connection->prepare("
            SELECT tm.*, t.name AS team_name 
            FROM team_members tm 
            JOIN team t ON tm.team = t.team_id
            WHERE tm.team = :team_id
        ");
        $stmt->execute(['team_id' => $loggedUserTeamId]);

        // Loop and display each team member
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $displayName = "{$row['fname']} {$row['lname']} - {$row['role_in_team']} ({$row['team_name']})";
            echo "<option value='{$row['member_id']}'>" . htmlspecialchars($displayName) . "</option>";
        }
    } else {
        echo '<option disabled>No team assigned to user</option>';
    }
    ?>
</select>

                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Post</label>
                            <select name="post" class="form-select" required>
                                <option value="player">Player</option>
                                <option value="HC">Head Coach</option>
                                <option value="AC">Assistant Coach</option>
                                <option value="GC">Goalkeeper Coach</option>
                                <option value="Do">Doctor</option>
                                <option value="Ph">Physiotherapist</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                    <div class="col-md-6 d-none">
                            <label class="form-label">Team From</label>
                            <?php
                            $teamName = '';
                            if ($loggedUserTeamId) {
                                $stmt = $connection->prepare("SELECT name FROM team WHERE team_id = :id");
                                $stmt->execute(['id' => $loggedUserTeamId]);
                                $teamName = $stmt->fetchColumn();
                            }
                            ?>
                            <input type="hidden" name="team_from" value="<?= htmlspecialchars($loggedUserTeamId) ?>">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($teamName) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Team To</label>
                            <select name="team_to" class="form-select" required>
                                <option value="">-- Select To Team --</option>
                                <?php
                                $stmt = $connection->prepare("SELECT * FROM team");
                                $stmt->execute();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['team_id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="status" value="1">

                    <div class="form-group mt-4 text-center">
                        <button type="submit" name="submit" class="btn btn-lg btn-success px-5">
                            <i class="fa fa-check-circle me-1"></i> Submit Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
