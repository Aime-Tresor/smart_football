<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'header.php';

// Database connection
$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in as a team
$your_team_id = $_SESSION['Team_id'] ?? null;

// Debug: Show current session for troubleshooting
if (!$your_team_id) {
    echo "<div class='alert alert-warning'>";
    echo "<h5>⚠️ Team Login Required</h5>";
    echo "<p>You need to be logged in as a team to view transfer requests.</p>";
    echo "<p><strong>Debug Info:</strong></p>";
    echo "<ul>";
    echo "<li>Session ID: " . session_id() . "</li>";
    echo "<li>Team ID in session: " . ($your_team_id ? $your_team_id : 'Not set') . "</li>";
    echo "<li>All session vars: " . print_r($_SESSION, true) . "</li>";
    echo "</ul>";
    echo "<div class='mt-3'>";
    echo "<a href='../login.php' class='btn btn-primary'>Team Login</a> ";
    echo "<a href='test_session.php' class='btn btn-secondary'>Test Session</a>";
    echo "</div>";
    echo "</div>";
    exit;
}

$status_labels = [
    0 => 'Pending',
    1 => 'Requested',
    2 => 'Rejected',
    3 => 'Completed'
];
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0">
                    <i class="fa fa-exchange text-primary me-2"></i> Transfer Requests
                </h4>
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
                            <th>Current Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get team name for display
                        $team_query = "SELECT name FROM team WHERE team_id = ?";
                        $team_stmt = $con->prepare($team_query);
                        $team_stmt->bind_param("i", $your_team_id);
                        $team_stmt->execute();
                        $team_result = $team_stmt->get_result();
                        $team_data = $team_result->fetch_assoc();
                        $team_name = $team_data ? $team_data['name'] : 'Unknown Team';

                        // Query to get transfers involving this team (both incoming and outgoing)
                        $sql = "SELECT * FROM transfer WHERE team_from = ? OR team_to = ? ORDER BY id DESC";
                        $result = mysqli_query($con, "SELECT * FROM transfer WHERE team_from = $your_team_id OR team_to = $your_team_id ORDER BY id DESC");

                        if (!$result) {
                            echo "<tr><td colspan='7'>Error: " . mysqli_error($con) . "</td></tr>";
                        } elseif (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='7' class='text-center py-4'>";
                            echo "<div class='text-muted'>";
                            echo "<i class='fa fa-info-circle fa-2x mb-2'></i>";
                            echo "<h6>No Transfer Requests</h6>";
                            echo "<p>Team \"" . htmlspecialchars($team_name) . "\" (ID: $your_team_id) has no transfer requests.</p>";
                            echo "<small>Both incoming and outgoing transfers are shown here.</small>";
                            echo "</div>";
                            echo "</td></tr>";
                        } else {
                            while ($transfer = mysqli_fetch_assoc($result)) {
                                // Member details
                                $member_id = (int)$transfer['member_id'];
                                $memberResult = mysqli_query($con, "SELECT * FROM team_members WHERE member_id = $member_id");
                                if (!$memberResult || mysqli_num_rows($memberResult) == 0) {
                                    continue;
                                }
                                $member = mysqli_fetch_assoc($memberResult);

                                $name = $member['fname'] . ' ' . $member['lname'];
                                $number = !empty($member['number']) ? $member['number'] : strtoupper($transfer['post'][0]);
                                $position_or_role = !empty($member['position']) ? $member['position'] : $member['role_in_team'];
                                $post = $transfer['post']; // player or staff

                                if ($post === 'staff') {
                                    switch (strtolower($member['role_in_team'])) {
                                        case 'hc': $staffTitle = 'Head Coach'; break;
                                        case 'ac': $staffTitle = 'Assistant Coach'; break;
                                        case 'gc': $staffTitle = 'Goalkeeper Coach'; break;
                                        case 'do': $staffTitle = 'Doctor'; break;
                                        case 'ph': $staffTitle = 'Physiotherapist'; break;
                                        default: $staffTitle = 'Staff';
                                    }
                                } else {
                                    $staffTitle = '';
                                }

                                // Get team names
                                $team_from_id = (int)$transfer['team_from'];
                                $team_to_id = (int)$transfer['team_to'];

                                $teamFromResult = mysqli_query($con, "SELECT name FROM team WHERE team_id = $team_from_id");
                                $teamFrom = ($teamFromResult && mysqli_num_rows($teamFromResult) > 0)
                                    ? mysqli_fetch_assoc($teamFromResult)['name'] : '';

                                $teamToResult = mysqli_query($con, "SELECT name FROM team WHERE team_id = $team_to_id");
                                $teamTo = ($teamToResult && mysqli_num_rows($teamToResult) > 0)
                                    ? mysqli_fetch_assoc($teamToResult)['name'] : '';

                                // Status text
                                $statusLabels = [
                                    0 => 'Pending',
                                    1 => 'Requested',
                                    2 => 'Rejected',
                                    3 => 'Completed',
                                ];
                                $statusText = $statusLabels[$transfer['status']] ?? 'Unknown';

                                // Determine the most recent status update date
                                $statusDates = [];
                                if (!empty($transfer['requestDate'])) {
                                    $statusDates['requestDate'] = $transfer['requestDate'];
                                }
                                if (!empty($transfer['aprovalDate'])) {
                                    $statusDates['aprovalDate'] = $transfer['aprovalDate'];
                                }
                                if (!empty($transfer['rejectDate'])) {
                                    $statusDates['rejectDate'] = $transfer['rejectDate'];
                                }
                                if (!empty($transfer['completeDate'])) {
                                    $statusDates['completeDate'] = $transfer['completeDate'];
                                }

                                // Find the most recent date
                                $mostRecentDate = '';
                                $mostRecentStatus = '';
                                if (!empty($statusDates)) {
                                    $latestDate = max($statusDates);
                                    $mostRecentDate = $latestDate;

                                    // Determine which status corresponds to the latest date
                                    foreach ($statusDates as $dateType => $date) {
                                        if ($date === $latestDate) {
                                            switch ($dateType) {
                                                case 'requestDate':
                                                    $mostRecentStatus = 'Requested';
                                                    break;
                                                case 'aprovalDate':
                                                    $mostRecentStatus = 'Approved';
                                                    break;
                                                case 'rejectDate':
                                                    $mostRecentStatus = 'Rejected';
                                                    break;
                                                case 'completeDate':
                                                    $mostRecentStatus = 'Completed';
                                                    break;
                                            }
                                            break;
                                        }
                                    }
                                }
                        ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-info text-white fw-bold rounded-circle d-inline-block text-center"
                                                  style="width: 30px; height: 30px; line-height: 30px;">
                                                <?= htmlspecialchars($number) ?>
                                            </span>
                                            <span class="ms-3"><?= htmlspecialchars($name) ?></span>
                                        </div>
                                    </td>
                                    <td><?= ucfirst(htmlspecialchars($post)) ?></td>
                                    <td><?= $post === 'player' ? htmlspecialchars($position_or_role) : htmlspecialchars($staffTitle) ?></td>
                                    <td><?= htmlspecialchars($teamFrom) ?></td>
                                    <td><?= htmlspecialchars($teamTo) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $transfer['status'] == 0 ? 'secondary' : ($transfer['status'] == 1 ? 'primary' : ($transfer['status'] == 2 ? 'danger' : 'success')) ?>"
                                              title="Current status: <?= htmlspecialchars($statusText) ?>"
                                              data-bs-toggle="tooltip">
                                            <?= htmlspecialchars($statusText) ?>
                                        </span>
                                    </td>
                                    <td class="last-updated">
                                        <?php if ($mostRecentDate): ?>
                                            <div>
                                                <strong><?= htmlspecialchars($mostRecentDate) ?></strong>
                                                <br>
                                                <small class="text-muted">(<?= htmlspecialchars($mostRecentStatus) ?>)</small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No updates</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Status badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Last updated styling */
.last-updated {
    font-size: 0.9rem;
}

.last-updated strong {
    color: #495057;
}

.last-updated .text-muted {
    font-size: 0.8rem;
}

/* Table improvements */
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

/* Status-specific badge colors */
.bg-secondary {
    background-color: #6c757d !important;
}

.bg-primary {
    background-color: #0d6efd !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-success {
    background-color: #198754 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require 'footer.php'; ?>
