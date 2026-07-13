<?php
require __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

session_start();
if (!isset($_SESSION['fa_user'])) {
    header('Location: ../app/login.php');
    exit;
}

require_once '../app/database.php';

// Fetch pending appeals
$stmt = $connection->prepare("
    SELECT ac.*, dc.offence_description, dc.article_code, dc.sanction, t.name as team_name,
           crd.card_id, crd.card_reason_title, crd.ai_summary, crd.ai_summary_status
    FROM appeal_cases ac
    JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
    JOIN team t ON ac.team_id = t.team_id
    LEFT JOIN cards crd ON dc.card_id = crd.card_id
    WHERE ac.status = 'pending'
    ORDER BY ac.appeal_date ASC
");
$stmt->execute();
$pending_appeals = $stmt->fetchAll();

// Handle appeal decision
$success_msg = $_SESSION['success'] ?? '';
$error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
if ($_POST && isset($_POST['decide_appeal'])) {
    $appeal_id = $_POST['appeal_id'];
    $decision = $_POST['decision'];
    $reason = trim($_POST['decision_reason']);
    $hearing_date = $_POST['hearing_date'] ?? NULL;
    $hearing_notes = trim($_POST['hearing_notes']) ?? NULL;
    
    if (in_array($decision, ['approved', 'rejected'])) {
        try {
            $stmt = $connection->prepare("
                UPDATE appeal_cases 
                SET status = ?, decision_reason = ?, decision_date = NOW(), 
                    hearing_date = ?, hearing_notes = ?
                WHERE appeal_id = ?
            ");
            $stmt->execute([$decision, $reason, $hearing_date, $hearing_notes, $appeal_id]);
            
            // If approved, update original case status
            if ($decision === 'approved') {
                $stmt = $connection->prepare("UPDATE ai_discipline_cases SET status = 'overturned' WHERE case_id IN (SELECT discipline_case_id FROM appeal_cases WHERE appeal_id = ?)");
                $stmt->execute([$appeal_id]);
            }

            // Best-effort: generates the AI summary of the decision reason
            // and notifies the team (in-app + email) - never blocks the
            // decision itself from being recorded.
            try {
                ServiceFactory::appealDecisionService()->recordDecisionMade((int) $appeal_id);
            } catch (\Throwable $e) {
                error_log('committee_appeals.php: appeal decision follow-up failed: ' . $e->getMessage());
            }

            $success_msg = "Appeal decision recorded successfully.";
        } catch (Exception $e) {
            $error_msg = "Error recording decision: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appeal Management - Committee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; }
        .card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .badge-pending { background: #ffc107; }
        .badge-approved { background: #28a745; }
        .badge-rejected { background: #dc3545; }
        .appeal-card { border-left: 4px solid #ffc107; }
        .btn-group-vertical .btn { text-align: left; }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-gavel"></i> Appeal Review Board</h2>
        <small class="text-muted">Total Pending: <strong><?php echo count($pending_appeals); ?></strong></small>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($pending_appeals)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>No pending appeals to review.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pending_appeals as $appeal): ?>
                <div class="col-md-6 mb-4">
                    <div class="card appeal-card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo htmlspecialchars($appeal['team_name']); ?></strong>
                                <span class="badge badge-pending">PENDING</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-flag"></i> <?php echo htmlspecialchars(substr($appeal['offence_description'], 0, 50)); ?>
                            </h6>
                            <p class="card-text">
                                <small><strong>Card Reason Title:</strong> <?php echo htmlspecialchars($appeal['card_reason_title'] ?? 'N/A'); ?></small><br>
                                <small><strong>AI Summary:</strong>
                                    <?php if (!empty($appeal['ai_summary'])): ?>
                                        <em><?php echo htmlspecialchars($appeal['ai_summary']); ?></em>
                                    <?php elseif (($appeal['ai_summary_status'] ?? '') === 'pending'): ?>
                                        <span class="text-muted">Generating...</span>
                                    <?php elseif (($appeal['ai_summary_status'] ?? '') === 'failed'): ?>
                                        <span class="text-danger">Generation failed</span>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </small><br>
                                <small><strong>Article:</strong> <?php echo htmlspecialchars($appeal['article_code']); ?></small><br>
                                <small><strong>Sanction:</strong> <?php echo htmlspecialchars($appeal['sanction']); ?></small><br>
                                <small><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($appeal['appeal_date'])); ?></small>
                            </p>
                            <?php if (!empty($appeal['card_id'])): ?>
                                <a href="controls/regenerate_ai_summary.php?card_id=<?php echo (int) $appeal['card_id']; ?>&redirect=committee_appeals.php"
                                   class="btn btn-sm btn-outline-secondary mb-2">
                                    <i class="fas fa-sync"></i> Regenerate AI Summary
                                </a>
                            <?php endif; ?>
                            
                            <div class="bg-light p-3 rounded mb-3">
                                <strong>Appeal Grounds:</strong>
                                <p class="small mt-2 mb-0"><?php echo htmlspecialchars($appeal['appeal_reason']); ?></p>
                            </div>

                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#decideModal<?php echo $appeal['appeal_id']; ?>">
                                <i class="fas fa-check"></i> Review & Decide
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Decision Modal -->
                <div class="modal fade" id="decideModal<?php echo $appeal['appeal_id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title">Decision: <?php echo htmlspecialchars($appeal['team_name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="appeal_id" value="<?php echo $appeal['appeal_id']; ?>">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Decision</strong></label>
                                        <div class="btn-group-vertical w-100">
                                            <input type="radio" class="btn-check" name="decision" id="approve<?php echo $appeal['appeal_id']; ?>" value="approved">
                                            <label class="btn btn-outline-success text-start" for="approve<?php echo $appeal['appeal_id']; ?>">
                                                <i class="fas fa-thumbs-up"></i> APPROVE APPEAL - Overturn sanction
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="decision" id="reject<?php echo $appeal['appeal_id']; ?>" value="rejected" checked>
                                            <label class="btn btn-outline-danger text-start" for="reject<?php echo $appeal['appeal_id']; ?>">
                                                <i class="fas fa-thumbs-down"></i> REJECT APPEAL - Uphold sanction
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="decisionReason<?php echo $appeal['appeal_id']; ?>" class="form-label"><strong>Written Reasoning</strong></label>
                                        <textarea class="form-control" id="decisionReason<?php echo $appeal['appeal_id']; ?>" name="decision_reason" rows="3" placeholder="Explain the committee's decision..." required></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="hearingDate<?php echo $appeal['appeal_id']; ?>" class="form-label">Hearing Date (optional)</label>
                                            <input type="datetime-local" class="form-control" id="hearingDate<?php echo $appeal['appeal_id']; ?>" name="hearing_date">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hearingNotes<?php echo $appeal['appeal_id']; ?>" class="form-label">Hearing Notes</label>
                                            <input type="text" class="form-control" id="hearingNotes<?php echo $appeal['appeal_id']; ?>" name="hearing_notes" placeholder="e.g., Video evidence reviewed">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="decide_appeal" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Record Decision
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
