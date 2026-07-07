<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'header.php';

// Connect to DB
$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0">
                    <i class="fa fa-exchange text-primary me-2"></i> Transfers History
                </h4>
            
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

                <div class="d-flex gap-2">
                    <button id="downloadTransferBtn" class="btn btn-danger btn-sm">
                        <i class="fa fa-file-pdf me-1"></i> Download PDF
                    </button>

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
                            <th>Current Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th> <!-- New column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Optional: get status filter from GET param (e.g. ?status=3)
                        $filter_status = isset($_GET['status']) ? (int)$_GET['status'] : null;

                        if ($filter_status !== null) {
                            $sql = "SELECT * FROM transfer WHERE status = $filter_status ORDER BY id DESC";
                        } else {
                            $sql = "SELECT * FROM transfer ORDER BY id DESC";
                        }

                        $result = mysqli_query($con, $sql);

                        if (!$result) {
                            echo "<tr><td colspan='8'>Error: " . mysqli_error($con) . "</td></tr>";
                        } elseif (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='8'>No transfers found.</td></tr>";
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

                                    <td>
                                        <a href="transfer_edit.php?id=<?= $transfer['id'] ?>" class="btn btn-sm btn-warning mb-1">Update</a>

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

<!-- Download Modal -->
<div id="downloadModal" class="modal fade" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="downloadModalLabel">
                    <i class="fa fa-file-pdf text-danger me-2"></i>Download Transfer Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Click the button below to download the transfer data as PDF:</p>

                <div class="download-option p-3 border rounded mb-3" style="cursor: pointer; transition: all 0.3s ease;" onclick="downloadTransferPDF()">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="me-3">
                            <i class="fa fa-file-pdf fa-2x text-danger"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">PDF Transfer Report</h6>
                            <small class="text-muted">Clean table data in PDF format - perfect for printing and sharing</small>
                        </div>
                        <div class="ms-3">
                            <i class="fa fa-download text-primary"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fa fa-info-circle me-1"></i>
                        Click to download the transfer data as a PDF document
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.download-option:hover {
    background-color: #f8f9fa !important;
    border-color: #dc3545 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
}

.download-option:active {
    transform: translateY(0);
}

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

    // Download button event
    document.getElementById('downloadTransferBtn').addEventListener('click', function() {
        console.log('Download button clicked');

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('downloadModal'));
        modal.show();
    });
});

function downloadTransferPDF() {
    console.log('downloadTransferPDF function called');

    // Show loading state
    const downloadOption = document.querySelector('.download-option');
    const originalContent = downloadOption.innerHTML;

    downloadOption.innerHTML = `
        <div class="d-flex align-items-center justify-content-center">
            <div class="me-3">
                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
            </div>
            <div>
                <h6 class="mb-1">Preparing PDF...</h6>
                <small class="text-muted">Please wait while we generate your report</small>
            </div>
        </div>
    `;

    // Start download
    setTimeout(() => {
        console.log('Starting download...');

        // Get current status filter if any
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        let downloadUrl = 'download_transfer_pdf.php?download=yes';

        if (status) {
            downloadUrl += '&status=' + encodeURIComponent(status);
        }

        // Direct download approach
        window.location.href = downloadUrl;

        // Reset the download option
        setTimeout(() => {
            downloadOption.innerHTML = originalContent;
        }, 1000);

        // Show success message
        setTimeout(() => {
            showSuccessMessage('PDF download started successfully!');

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('downloadModal'));
            if (modal) {
                modal.hide();
            }
        }, 500);

    }, 800);
}

function showSuccessMessage(message) {
    // Create and show success alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fa fa-check-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alertDiv);

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}
</script>

<?php require 'footer.php'; ?>
