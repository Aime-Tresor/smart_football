<?php
session_start();
require_once 'header.php';

$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid transfer ID.";
    header("Location: transfer.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch existing transfer data
$sql = "SELECT * FROM transfer WHERE id = $id";
$result = mysqli_query($con, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Transfer record not found.";
    header("Location: transfer.php");
    exit;
}
$transfer = mysqli_fetch_assoc($result);
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="card-title mb-4 text-primary">
                    <i class="fa fa-random me-2"></i> Edit Transfer Player/Staff
                </h4>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form action="controls/updateTransfer.php" method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Member</label>
                            <select name="member_id" class="form-select" required>
                                <option value="">-- Choose Player / Staff --</option>
                                <?php
                                $membersSql = "SELECT tm.*, t.name AS team_name FROM team_members tm JOIN team t ON tm.team = t.team_id";
                                $membersResult = mysqli_query($con, $membersSql);
                                while ($row = mysqli_fetch_assoc($membersResult)) {
                                    $selected = ($row['member_id'] == $transfer['member_id']) ? 'selected' : '';
                                    $displayName = "{$row['fname']} {$row['lname']} - {$row['role_in_team']} ({$row['team_name']})";
                                    echo "<option value='{$row['member_id']}' $selected>{$displayName}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Post</label>
                            <select name="post" class="form-select" required>
                                <?php
                                $posts = ['player' => 'Player', 'HC' => 'Head Coach', 'AC' => 'Assistant Coach', 'GC' => 'Goalkeeper Coach', 'Do' => 'Doctor', 'Ph' => 'Physiotherapist'];
                                foreach ($posts as $key => $label) {
                                    $selected = ($key == $transfer['post']) ? 'selected' : '';
                                    echo "<option value='$key' $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 d-none">
                            <label class="form-label">Team From</label>
                            <select name="team_from" class="form-select" required>
                                <option value="">-- Select From Team --</option>
                                <?php
                                $teamsResult = mysqli_query($con, "SELECT * FROM team");
                                while ($row = mysqli_fetch_assoc($teamsResult)) {
                                    $selected = ($row['team_id'] == $transfer['team_from']) ? 'selected' : '';
                                    echo "<option value='{$row['team_id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Team To</label>
                            <select name="team_to" class="form-select" required>
                                <option value="">-- Select To Team --</option>
                                <?php
                                // Need to re-query teams again, or reset result pointer
                                mysqli_data_seek($teamsResult, 0); // reset pointer to first row
                                while ($row = mysqli_fetch_assoc($teamsResult)) {
                                    $selected = ($row['team_id'] == $transfer['team_to']) ? 'selected' : '';
                                    echo "<option value='{$row['team_id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="status" value="<?= htmlspecialchars($transfer['status']) ?>">

                    <div class="form-group mt-4 text-center">
                        <button type="submit" name="submit" class="btn btn-lg btn-success px-5">
                            <i class="fa fa-check-circle me-1"></i> Update Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
