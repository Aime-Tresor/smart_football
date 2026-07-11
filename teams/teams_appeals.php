<?php
session_start();

if (!isset($_SESSION['Team_id'])) {
    header('Location: ../logrole.php');
    exit;
}

require_once '../app/database.php';

$team_id = $_SESSION['Team_id'];
$team_name = $_SESSION['Team_Name'];
// Fetch open discipline cases for this team
$stmt = $connection->prepare("
    SELECT dc.*, t.name as offending_team
    FROM ai_discipline_cases dc
    JOIN team t ON dc.team_id = t.team_id
    WHERE dc.team_id = ? AND dc.status = 'approved'
    ORDER BY dc.created_at DESC
");
$stmt->execute([$team_id]);
$cases = $stmt->fetchAll();

// Fetch existing appeals
$stmt = $connection->prepare("
    SELECT ac.*, 
           dc.offence_description,
           tm.fname, 
           tm.lname, 
           tm.number, 
           tm.position
    FROM appeal_cases ac
    JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
    LEFT JOIN team_members tm ON dc.member_id = tm.member_id
    WHERE ac.team_id = ?
    ORDER BY ac.appeal_date DESC
");
$stmt->execute([$team_id]);
$appeals = $stmt->fetchAll();

// Handle form submission
$success_msg = $error_msg = '';
if ($_POST && isset($_POST['submit_appeal'])) {
    $case_id = $_POST['case_id'];
    $appeal_reason = trim($_POST['appeal_reason']);
    
    if (!empty($appeal_reason)) {
        try {
            $stmt = $connection->prepare("
                INSERT INTO appeal_cases (discipline_case_id, team_id, appeal_reason, status)
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->execute([$case_id, $team_id, $appeal_reason]);
            $success_msg = "Appeal submitted successfully. Awaiting committee review.";
        } catch (Exception $e) {
            $error_msg = "Error submitting appeal: " . $e->getMessage();
        }
    } else {
        $error_msg = "Appeal reason is required";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team_name); ?> - Appeals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px 0; }
        .card { border: none; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; }
        .btn-primary { background: #667eea; border: none; }
        .btn-primary:hover { background: #764ba2; }
        .badge-pending { background: #ffc107; }
        .badge-approved { background: #28a745; }
        .badge-rejected { background: #dc3545; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #667eea; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
    </style>
</head>
<body>
<div class="container-fluid py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Disciplinary Appeals - <?php echo htmlspecialchars($team_name); ?></h4>
                </div>
                <div class="card-body">
                    <!-- Messages -->
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <?php echo $success_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> <?php echo $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#open-cases">Open Sanctions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#appeals">My Appeals</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Open Cases Tab -->
                        <div id="open-cases" class="tab-pane fade show active">
                            <h5 class="mb-3">Active Sanctions Ready for Appeal</h5>
                            <?php if (empty($cases)): ?>
                                <p class="text-muted">No open sanctions to appeal at this time.</p>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($cases as $case): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?php echo htmlspecialchars($case['offence_description'] ?? 'Unknown Offense'); ?></h6>
                                                    <p class="card-text">
                                                        <small><strong>Article:</strong> <?php echo htmlspecialchars($case['article_code'] ?? 'N/A'); ?></small><br>
                                                        <small><strong>Sanction:</strong> <?php echo htmlspecialchars($case['sanction'] ?? 'N/A'); ?></small><br>
                                                        <small><strong>Date:</strong> <?php echo date('M d, Y', strtotime($case['created_at'])); ?></small>
                                                    </p>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#appealModal" onclick="setAppealCase(<?php echo $case['case_id']; ?>, '<?php echo htmlspecialchars($case['offence_description']); ?>')">
                                                        Submit Appeal
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Appeals Tab -->
                        <div id="appeals" class="tab-pane fade">
                            <h5 class="mb-3">Your Appeals History</h5>
                            <?php if (empty($appeals)): ?>
                                <p class="text-muted">No appeals submitted yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                 <th>Player</th>
                                                <th>Offense</th>
                                                <th>Appeal Date</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                                <th>Decision</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($appeals as $appeal): ?>
                                                <tr>
                                                <td>
        <strong><?php echo $appeal['fname'] . ' ' . $appeal['lname']; ?></strong>
        <br><small>No. <?php echo $appeal['number']; ?> - <?php echo $appeal['position']; ?></small>
      </td>
                                                    <td><?php echo htmlspecialchars(substr($appeal['offence_description'], 0, 30)); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($appeal['appeal_date'])); ?></td>
                                                    <td><small><?php echo htmlspecialchars(substr($appeal['appeal_reason'], 0, 40)); ?>...</small></td>
                                                    <td><span class="badge badge-<?php echo $appeal['status']; ?>"><?php echo ucfirst($appeal['status']); ?></span></td>
                                                    <td>
                                                        <?php if ($appeal['status'] !== 'pending'): ?>
                                                            <small><?php echo htmlspecialchars(substr($appeal['decision_reason'] ?? 'N/A', 0, 30)); ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Awaiting review</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appeal Modal -->
<div class="modal fade" id="appealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Submit Appeal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="caseId" name="case_id">
                    <div class="mb-3">
                        <label class="form-label"><strong>Offense</strong></label>
                        <p id="offenseText" class="form-control-plaintext"></p>
                    </div>
                    <div class="mb-3">
                        <label for="appealReason" class="form-label"><strong>Appeal Grounds</strong></label>
                        <textarea class="form-control" id="appealReason" name="appeal_reason" rows="4" placeholder="Explain why you believe this sanction should be overturned..." required></textarea>
                        <small class="text-muted">Be clear and factual. Reference specific rules or circumstances.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_appeal" class="btn btn-primary">Submit Appeal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setAppealCase(caseId, offense) {
    document.getElementById('caseId').value = caseId;
    document.getElementById('offenseText').textContent = offense;
    document.getElementById('appealReason').value = '';
}
</script>
</body>
</html>
