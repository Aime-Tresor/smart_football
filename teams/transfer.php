<?php

require_once 'header.php';

// DB connection
$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Logged-in team
$loggedTeamId = $_SESSION['Team_id'] ?? null;
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0">
                    <i class="fa fa-exchange text-primary me-2"></i> Transfers History
                </h4>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <a href="transfer_form.php" class="btn btn-success btn-sm">
                        <i class="fa fa-plus-circle me-1"></i> Transfer Player
                    </a>

                    <form action="controls/transferReport.php" method="post">
                        <button class="btn btn-primary btn-sm">
                            <i class="fa fa-file-alt me-1"></i> Generate Report
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered text-nowrap align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Player / Staff</th>
                            <th>Post</th>
                            <th>Position / Role</th>
                            <th>Team From</th>
                            <th>Team To</th>
                            <th>Status</th>
                            <th>Completed On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM transfer WHERE team_from = $loggedTeamId ORDER BY id DESC";
                        $result = mysqli_query($con, $sql);

                        if (!$result || mysqli_num_rows($result) === 0) {
                            echo "<tr><td colspan='8'>No transfers found.</td></tr>";
                        } else {
                            while ($transfer = mysqli_fetch_assoc($result)) {
                                $member_id = (int)$transfer['member_id'];
                                $member = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM team_members WHERE member_id = $member_id"));
                                if (!$member) continue;

                                $name = $member['fname'] . ' ' . $member['lname'];
                                $number = !empty($member['number']) ? $member['number'] : strtoupper($transfer['post'][0]);
                                $position = !empty($member['position']) ? $member['position'] : $member['role_in_team'];
                                $post = ucfirst($transfer['post']);

                                $teamFrom = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM team WHERE team_id = {$transfer['team_from']}"))['name'] ?? '';
                                $teamTo = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM team WHERE team_id = {$transfer['team_to']}"))['name'] ?? '';

                                $statusLabels = [0 => 'Pending', 1 => 'Requested', 2 => 'Rejected', 3 => 'Completed'];
                                $statusText = $statusLabels[$transfer['status']] ?? 'Unknown';

                                echo "<tr>
                                    <td>
                                        <div class='d-flex align-items-center'>
                                            <span class='badge bg-info text-white rounded-circle' style='width: 30px; height: 30px; line-height: 30px;'>
                                                $number
                                            </span>
                                            <span class='ms-3'>" . htmlspecialchars($name) . "</span>
                                        </div>
                                    </td>
                                    <td>$post</td>
                                    <td>" . htmlspecialchars($position) . "</td>
                                    <td>" . htmlspecialchars($teamFrom) . "</td>
                                    <td>" . htmlspecialchars($teamTo) . "</td>
                                    <td>$statusText</td>
                                    <td>" . ($transfer['completeDate'] ?? '-') . "</td>
                                    <td>
                                        <a href='transfer_edit.php?id={$transfer['id']}' class='btn btn-sm btn-warning mb-1'>Update</a>
                                        <a href='transfer_delete.php?id={$transfer['id']}' class='btn btn-sm btn-danger mb-1' onclick=\"return confirm('Are you sure you want to delete this transfer?');\">Delete</a>
                                    </td>
                                </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
