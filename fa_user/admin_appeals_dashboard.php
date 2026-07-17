<?php
session_start();
require '../app/database.php';

// Check if user is FA Admin
if (!isset($_SESSION['fa_user'])) {
    header('Location: ../app/login.php');
    exit;
}

$success_msg = $error_msg = '';

// Get appeal statistics
$stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'pending'");
$stmt->execute();
$pending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'approved'");
$stmt->execute();
$approved = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'rejected'");
$stmt->execute();
$rejected = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases");
$stmt->execute();
$total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appeals Monitoring - FA Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            min-height: 100vh;
            padding: 20px 0;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        .card-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
        }
        .stat-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .table-responsive {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        table {
            margin: 0;
        }
        table thead {
            background: #f8f9fa;
            font-weight: 600;
        }
        table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .btn-committee {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-committee:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- Back Button -->
            <a href="index.php" class="btn btn-light mb-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>

            <!-- Main Card -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                    <h1 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Appeals Monitoring
                    </h1>
                    <p class="text-white-50 mb-0 mt-2">View-only dashboard for FA Administrators</p>
                </div>
                <div class="card-body">
                    <!-- Info Alert -->
                    <div class="info-box">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>View Only:</strong> As an administrator, you can view all appeals and statistics. Only Committee Members can approve or reject appeals.
                        <a href="manage_committee.php" class="btn-committee ms-3">Go to Committee Dashboard</a>
                    </div>

                    <!-- Statistics Section -->
                    <h5 class="mb-4">Appeal Statistics</h5>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="badge badge-pending">PENDING</div>
                                <div class="stat-number text-warning"><?php echo $pending; ?></div>
                                <div class="stat-label">Pending Review</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="badge badge-approved">APPROVED</div>
                                <div class="stat-number text-success"><?php echo $approved; ?></div>
                                <div class="stat-label">Overturned</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="badge badge-rejected">REJECTED</div>
                                <div class="stat-number text-danger"><?php echo $rejected; ?></div>
                                <div class="stat-label">Upheld</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="badge bg-secondary">TOTAL</div>
                                <div class="stat-number text-dark"><?php echo $total; ?></div>
                                <div class="stat-label">All Cases</div>
                            </div>
                        </div>
                    </div>

                    <!-- All Appeals Table -->
                    <h5 class="mb-3">All Appeals</h5>
                    <?php
                    $stmt = $connection->prepare("
                        SELECT ac.*, t.name as team_name, dc.offence_description, dc.article_code, dc.sanction
                        FROM appeal_cases ac
                        JOIN team t ON ac.team_id = t.team_id
                        LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
                        ORDER BY ac.appeal_date DESC
                    ");
                    $stmt->execute();
                    $appeals = $stmt->fetchAll();
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Team</th>
                                    <th>Offense</th>
                                    <th>Article</th>
                                    <th>Sanction</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Decision Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appeals)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No appeals in the system yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($appeals as $appeal): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($appeal['team_name']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(substr($appeal['offence_description'] ?? 'N/A', 0, 40)); ?>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($appeal['article_code'] ?? 'N/A'); ?></code>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($appeal['sanction'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($appeal['appeal_date'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                    if ($appeal['status'] === 'approved') echo 'bg-success';
                                                    elseif ($appeal['status'] === 'rejected') echo 'bg-danger';
                                                    else echo 'bg-warning';
                                                    ?>">
                                                    <?php echo strtoupper($appeal['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($appeal['decision_date']) {
                                                    echo date('M d, Y', strtotime($appeal['decision_date']));
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Appeal Details Section (Expandable) -->
                    <h5 class="mt-5 mb-3">Detailed Appeal Information</h5>
                    <?php
                    $stmt = $connection->prepare("
                        SELECT ac.*, t.name as team_name, dc.offence_description, dc.article_code, dc.sanction
                        FROM appeal_cases ac
                        JOIN team t ON ac.team_id = t.team_id
                        LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
                        WHERE ac.status IN ('approved', 'rejected')
                        ORDER BY ac.decision_date DESC
                        LIMIT 10
                    ");
                    $stmt->execute();
                    $decided_appeals = $stmt->fetchAll();
                    ?>

                    <?php if (empty($decided_appeals)): ?>
                        <p class="text-muted">No decided appeals yet.</p>
                    <?php else: ?>
                        <?php foreach ($decided_appeals as $idx => $appeal): ?>
                            <div class="card mb-3">
                                <div class="card-header" style="background-color: <?php echo ($appeal['status'] === 'approved' ? '#28a745' : '#dc3545'); ?>;">
                                    <h6 class="text-white mb-0">
                                        <?php echo htmlspecialchars($appeal['team_name']); ?> - 
                                        <?php echo htmlspecialchars(substr($appeal['offence_description'] ?? '', 0, 50)); ?>
                                        <span class="badge bg-white text-dark float-end">
                                            <?php echo strtoupper($appeal['status']); ?>
                                        </span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Appeal Grounds:</strong><br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($appeal['appeal_reason'] ?? 'No reason provided'); ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Committee Decision:</strong><br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($appeal['decision_reason'] ?? 'No reasoning provided'); ?>
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small><strong>Decision Date:</strong><br><?php echo date('M d, Y H:i', strtotime($appeal['decision_date'])); ?></small>
                                        </div>
                                        <div class="col-md-4">
                                            <small><strong>Article:</strong><br><code><?php echo htmlspecialchars($appeal['article_code']); ?></code></small>
                                        </div>
                                        <div class="col-md-4">
                                            <small><strong>Sanction:</strong><br><?php echo htmlspecialchars($appeal['sanction']); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Quick Links -->
                    <div class="mt-4">
                        <a href="manage_committee.php" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-users-cog"></i> Manage Committee
                        </a>
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-home"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
