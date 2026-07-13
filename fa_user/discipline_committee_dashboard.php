<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\ServiceFactory;

require_once 'header.php';
?>

<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<style>
  /* Card styles */
  .card {
    background-color: #1e293b;
    border-radius: 12px;
    border: none;
    color: #f1f5f9;
    transition: box-shadow 0.3s ease;
  }
  .card:hover {
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
  }
  .card-footer {
    background-color: #273449;
    border-top: 1px solid #374151;
    color: #a1a1aa;
    font-weight: 500;
  }
  .card-body i {
    color: #6366f1;
  }
  .card-category {
    color: #cbd5e1;
    font-weight: 600;
  }
  .card-title {
    font-size: 2.1rem;
    font-weight: 700;
    color: #e0e7ff;
  }

  /* Table styles */
  thead {
    background-color: #273449;
  }
  thead th {
    color: #cbd5e1;
    font-weight: 600;
  }
  tbody tr {
    background-color: #334155;
    transition: background-color 0.2s;
  }
  tbody tr:hover {
    background-color: #475569;
  }
  tbody td {
    color: #f8fafc;
  }

  /* Badge styles */
  .badge-pending {
    background: #ffc107;
    color: #333;
  }
  .badge-approved {
    background: #28a745;
  }
  .badge-rejected {
    background: #dc3545;
  }

  /* Appeal card */
  .appeal-card {
    border-left: 4px solid #6366f1;
    background-color: #1e293b;
    transition: all 0.3s ease;
  }
  .appeal-card:hover {
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    border-left: 4px solid #818cf8;
  }
  .appeal-card.pending {
    border-left-color: #ffc107;
  }
  .appeal-card.approved {
    border-left-color: #28a745;
  }
  .appeal-card.rejected {
    border-left-color: #dc3545;
  }

  .stat-box {
    text-align: center;
    padding: 20px;
    border-radius: 8px;
  }
  .stat-box h3 {
    margin: 10px 0;
    font-size: 1.8rem;
    font-weight: 700;
  }
</style>

<div class="page-wrapper">
  <div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="mb-4">
      <h2 class="text-white mb-2">
        <i class="fas fa-gavel me-2"></i>Discipline Committee Dashboard
      </h2>
      <p class="text-muted">Review and manage disciplinary appeals</p>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
      <?php
      $stats = [
        [
          'title' => 'Pending Appeals',
          'icon' => 'fa-hourglass-end',
          'color' => 'warning',
          'query' => "SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'pending'"
        ],
        [
          'title' => 'Approved',
          'icon' => 'fa-check-circle',
          'color' => 'success',
          'query' => "SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'approved'"
        ],
        [
          'title' => 'Rejected',
          'icon' => 'fa-times-circle',
          'color' => 'danger',
          'query' => "SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'rejected'"
        ],
        [
          'title' => 'Total Cases',
          'icon' => 'fa-file-contract',
          'color' => 'info',
          'query' => "SELECT COUNT(*) as count FROM appeal_cases"
        ]
      ];

      foreach ($stats as $stat) {
        $stmt = $connection->prepare($stat['query']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
      ?>
        <div class="col-md-6 col-lg-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fas <?= $stat['icon']; ?> fa-2x text-<?= $stat['color']; ?>"></i>
                </div>
                <div>
                  <p class="card-category"><?= $stat['title']; ?></p>
                  <h5 class="card-title"><?= $count; ?></h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>

    <!-- Pending Appeals Section -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-primary">
            <h4 class="text-white mb-0">
              <i class="fas fa-list me-2"></i>Pending Appeals Queue
            </h4>
          </div>
          <div class="card-body">
            <?php
            if ($_POST && isset($_POST['decide_appeal'])) {
                $appeal_id = $_POST['appeal_id'];
                $decision = $_POST['decision'];
                $reason = $_POST['decision_reason'];
                
                $stmt = $connection->prepare("
                    UPDATE appeal_cases 
                    SET status = ?, decision_reason = ?, decision_date = NOW()
                    WHERE appeal_id = ?
                ");
                $stmt->execute([$decision, $reason, $appeal_id]);
                
                if ($decision === 'approved') {
                    $stmt = $connection->prepare("
                        UPDATE ai_discipline_cases
                        SET status = 'overturned'
                        WHERE case_id = (SELECT discipline_case_id FROM appeal_cases WHERE appeal_id = ?)
                    ");
                    $stmt->execute([$appeal_id]);
                }

                // Best-effort: generates the AI summary of the decision
                // reason and notifies the team (in-app + email) - never
                // blocks the decision itself from being recorded.
                try {
                    ServiceFactory::appealDecisionService()->recordDecisionMade((int) $appeal_id);
                } catch (\Throwable $e) {
                    error_log('discipline_committee_dashboard.php: appeal decision follow-up failed: ' . $e->getMessage());
                }

                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Appeal decision recorded successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
            }

            $stmt = $connection->prepare("
              SELECT ac.*, t.name as team_name, dc.offence_description, dc.article_code, dc.sanction,
                     tm.fname, tm.lname, tm.number, tm.position,
                     crd.card_id, crd.card_reason_title, crd.ai_summary, crd.ai_summary_status
              FROM appeal_cases ac
              JOIN team t ON ac.team_id = t.team_id
              LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
              LEFT JOIN team_members tm ON dc.member_id = tm.member_id
              LEFT JOIN cards crd ON dc.card_id = crd.card_id
              WHERE ac.status = 'pending'
              ORDER BY ac.appeal_date ASC
            ");
            $stmt->execute();
            $pending_appeals = $stmt->fetchAll();
            ?>

            <?php if (empty($pending_appeals)): ?>
              <div class="alert alert-info text-center py-4">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <p class="mb-0">No pending appeals to review</p>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th width="15%">Team</th>
                      <th width="15%">Player</th>
                      <th width="20%">Offense</th>
                      <th width="15%">Submitted</th>
                      <th width="20%">Appeal Grounds</th>
                      <th width="15%">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pending_appeals as $appeal): ?>
                      <tr>
                        <td>
                          <strong><?php echo htmlspecialchars($appeal['team_name']); ?></strong>
                        </td>
                        <td>
                          <strong><?php echo htmlspecialchars(($appeal['fname'] ?? '') . ' ' . ($appeal['lname'] ?? '')); ?></strong>
                          <br><small class="text-muted">No. <?php echo $appeal['number'] ?? 'N/A'; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars(substr($appeal['offence_description'] ?? 'N/A', 0, 40)); ?></td>
                        <td><?php echo date('M d, Y', strtotime($appeal['appeal_date'])); ?></td>
                        <td>
                          <small class="text-muted"><?php echo htmlspecialchars(substr($appeal['appeal_reason'], 0, 50)); ?>...</small>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#decideModal<?php echo $appeal['appeal_id']; ?>">
                            <i class="fas fa-check"></i> Review
                          </button>
                        </td>
                      </tr>

                      <!-- Decision Modal -->
                      <div class="modal fade" id="decideModal<?php echo $appeal['appeal_id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header bg-light">
                              <h5 class="modal-title">
                                <i class="fas fa-gavel me-2"></i>Appeal Decision - <?php echo htmlspecialchars($appeal['team_name']); ?>
                              </h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                              <input type="hidden" name="appeal_id" value="<?php echo $appeal['appeal_id']; ?>">
                              <div class="modal-body">
                                <!-- Offense Details -->
                                <div class="alert alert-light mb-3">
                                  <strong>Offense Details:</strong>
                                  <p class="mb-1">
                                    <strong>Player:</strong> <?php echo htmlspecialchars(($appeal['fname'] ?? '') . ' ' . ($appeal['lname'] ?? '')); ?> (No. <?php echo $appeal['number'] ?? 'N/A'; ?>)
                                  </p>
                                  <p class="mb-1">
                                    <strong>Offense:</strong> <?php echo htmlspecialchars($appeal['offence_description'] ?? 'N/A'); ?>
                                  </p>
                                  <p class="mb-1">
                                    <strong>Card Reason Title:</strong> <?php echo htmlspecialchars($appeal['card_reason_title'] ?? 'N/A'); ?>
                                  </p>
                                  <p class="mb-1">
                                    <strong>AI Summary:</strong>
                                    <?php if (!empty($appeal['ai_summary'])): ?>
                                      <em><?php echo htmlspecialchars($appeal['ai_summary']); ?></em>
                                    <?php elseif (($appeal['ai_summary_status'] ?? '') === 'pending'): ?>
                                      <span class="text-muted">Generating...</span>
                                    <?php elseif (($appeal['ai_summary_status'] ?? '') === 'failed'): ?>
                                      <span class="text-danger">Generation failed</span>
                                    <?php else: ?>
                                      <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                    <?php if (!empty($appeal['card_id'])): ?>
                                      <a href="controls/regenerate_ai_summary.php?card_id=<?php echo (int) $appeal['card_id']; ?>&redirect=discipline_committee_dashboard.php"
                                         class="btn btn-sm btn-outline-secondary ms-2">
                                        <i class="fas fa-sync"></i> Regenerate
                                      </a>
                                    <?php endif; ?>
                                  </p>
                                  <p class="mb-0">
                                    <strong>Article:</strong> <?php echo htmlspecialchars($appeal['article_code'] ?? 'N/A'); ?> |
                                    <strong>Sanction:</strong> <?php echo htmlspecialchars($appeal['sanction'] ?? 'N/A'); ?>
                                  </p>
                                </div>

                                <!-- Appeal Grounds -->
                                <div class="alert alert-light mb-3">
                                  <strong>Appeal Grounds from <?php echo htmlspecialchars($appeal['team_name']); ?>:</strong>
                                  <p class="mb-0 mt-2"><?php echo htmlspecialchars($appeal['appeal_reason']); ?></p>
                                </div>

                                <!-- Decision Options -->
                                <div class="mb-3">
                                  <label class="form-label"><strong>Committee Decision</strong></label>
                                  <div class="btn-group-vertical w-100" role="group">
                                    <input type="radio" class="btn-check" name="decision" id="approve<?php echo $appeal['appeal_id']; ?>" value="approved">
                                    <label class="btn btn-outline-success text-start" for="approve<?php echo $appeal['appeal_id']; ?>">
                                      <i class="fas fa-thumbs-up me-2"></i> APPROVE - Overturn Sanction
                                    </label>

                                    <input type="radio" class="btn-check" name="decision" id="reject<?php echo $appeal['appeal_id']; ?>" value="rejected" checked>
                                    <label class="btn btn-outline-danger text-start" for="reject<?php echo $appeal['appeal_id']; ?>">
                                      <i class="fas fa-thumbs-down me-2"></i> REJECT - Uphold Sanction
                                    </label>
                                  </div>
                                </div>

                                <!-- Reasoning -->
                                <div class="mb-3">
                                  <label for="reason<?php echo $appeal['appeal_id']; ?>" class="form-label"><strong>Decision Reasoning</strong></label>
                                  <textarea class="form-control" id="reason<?php echo $appeal['appeal_id']; ?>" name="decision_reason" rows="4" placeholder="Explain the committee's decision clearly..." required></textarea>
                                  <small class="text-muted">This will be communicated to the club</small>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="decide_appeal" class="btn btn-primary">
                                  <i class="fas fa-save me-2"></i>Record Decision
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Decisions History -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-success">
            <h4 class="text-white mb-0">
              <i class="fas fa-history me-2"></i>Decision History
            </h4>
          </div>
          <div class="card-body">
            <?php
            $stmt = $connection->prepare("
              SELECT ac.*, t.name as team_name, dc.offence_description
              FROM appeal_cases ac
              JOIN team t ON ac.team_id = t.team_id
              LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
              WHERE ac.status IN ('approved', 'rejected')
              ORDER BY ac.decision_date DESC
              LIMIT 20
            ");
            $stmt->execute();
            $decisions = $stmt->fetchAll();
            ?>

            <?php if (empty($decisions)): ?>
              <p class="text-muted">No decisions recorded yet</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Team</th>
                      <th>Offense</th>
                      <th>Decision Date</th>
                      <th>Status</th>
                      <th>Reasoning</th>
                      <th>AI Summary</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($decisions as $decision): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($decision['team_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($decision['offence_description'] ?? 'N/A', 0, 35)); ?></td>
                        <td><?php echo date('M d, Y', strtotime($decision['decision_date'])); ?></td>
                        <td>
                          <span class="badge badge-<?php echo $decision['status']; ?>">
                            <?php echo $decision['status'] === 'approved' ? 'APPROVED' : 'REJECTED'; ?>
                          </span>
                        </td>
                        <td><small><?php echo htmlspecialchars(substr($decision['decision_reason'] ?? '', 0, 40)); ?>...</small></td>
                        <td>
                          <small>
                            <?php if (!empty($decision['ai_summary'])): ?>
                              <em><?php echo htmlspecialchars($decision['ai_summary']); ?></em>
                            <?php elseif (($decision['ai_summary_status'] ?? '') === 'failed'): ?>
                              <span class="text-danger">Generation failed</span>
                            <?php else: ?>
                              <span class="text-muted">Not available</span>
                            <?php endif; ?>
                          </small>
                          <br>
                          <a href="controls/regenerate_appeal_summary.php?appeal_id=<?php echo (int) $decision['appeal_id']; ?>&redirect=discipline_committee_dashboard.php"
                             class="btn btn-sm btn-outline-secondary mt-1">
                            <i class="fas fa-sync"></i> Regenerate
                          </a>
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

<?php require 'footer.php'; ?>
