<?php require_once 'header.php'; ?>

<style>
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
  .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  .card-footer {
    background-color: #273449;
    border-top: 1px solid #374151;
    color: #a1a1aa;
  }
  .card-category {
    color: #cbd5e1;
    font-weight: 600;
  }
  .card-title {
    font-size: 2rem;
    font-weight: 700;
    color: #e0e7ff;
  }
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
  .badge-pending { background: #ffc107; color: #333; }
  .badge-approved { background: #28a745; }
  .badge-rejected { background: #dc3545; }
  .profile-badge {
    display: inline-block;
    background: #6366f1;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
  }
  .quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
  }
  .stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    color: white;
  }
  .stat-card h3 {
    font-size: 2rem;
    margin: 10px 0;
  }
  .stat-card p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.9;
  }
</style>

<div class="page-wrapper">
  <div class="container-fluid py-4">

    <!-- Member Profile Section -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h4 class="text-white mb-0">
                  <i class="fas fa-user-circle me-2"></i>Committee Member Profile
                </h4>
              </div>
              <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit me-2"></i>Edit Profile
              </button>
            </div>
          </div>
          <div class="card-body">
            <?php
            $committee_id = $_SESSION['committee_id'] ?? null;
            if ($committee_id) {
                $stmt = $connection->prepare("SELECT * FROM committee_members WHERE committee_id = ?");
                $stmt->execute([$committee_id]);
                $member = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($member):
            ?>
              <div class="row">
                <div class="col-md-3 text-center mb-4">
                  <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-circle fa-5x text-white"></i>
                  </div>
                </div>
                <div class="col-md-9">
                  <h5 class="text-white mb-3"><?php echo htmlspecialchars($member['name']); ?></h5>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <p class="mb-1"><small class="text-muted">Username</small></p>
                      <p class="text-white"><strong><?php echo htmlspecialchars($member['username']); ?></strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                      <p class="mb-1"><small class="text-muted">Role</small></p>
                      <p><span class="profile-badge"><?php echo htmlspecialchars($member['role']); ?></span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                      <p class="mb-1"><small class="text-muted">Email</small></p>
                      <p class="text-white"><?php echo htmlspecialchars($member['email'] ?? 'Not set'); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                      <p class="mb-1"><small class="text-muted">Phone</small></p>
                      <p class="text-white"><?php echo htmlspecialchars($member['phone'] ?? 'Not set'); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                      <p class="mb-1"><small class="text-muted">Status</small></p>
                      <p><span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'danger'; ?>"><?php echo ucfirst($member['status']); ?></span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                      <p class="mb-1"><small class="text-muted">Member Since</small></p>
                      <p class="text-white"><?php echo date('M d, Y', strtotime($member['created_at'])); ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; } ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
      <?php
      // Get pending appeals count
      $stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'pending'");
      $stmt->execute();
      $pending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

      // Get total decisions made
      $stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status IN ('approved', 'rejected')");
      $stmt->execute();
      $decisions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

      // Get approved appeals
      $stmt = $connection->prepare("SELECT COUNT(*) as count FROM appeal_cases WHERE status = 'approved'");
      $stmt->execute();
      $approved = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
      ?>
      
      <div class="stat-card">
        <i class="fas fa-hourglass-end fa-2x"></i>
        <h3><?php echo $pending; ?></h3>
        <p>Pending Appeals</p>
      </div>
      <div class="stat-card">
        <i class="fas fa-check-circle fa-2x"></i>
        <h3><?php echo $approved; ?></h3>
        <p>Approved</p>
      </div>
      <div class="stat-card">
        <i class="fas fa-file-contract fa-2x"></i>
        <h3><?php echo $decisions; ?></h3>
        <p>Total Decisions</p>
      </div>
    </div>

    <!-- Committee Actions -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4 class="text-white mb-0">
              <i class="fas fa-tasks me-2"></i>Quick Actions
            </h4>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 mb-3">
                <a href="discipline_committee_dashboard.php" class="btn btn-primary w-100 py-3">
                  <i class="fas fa-gavel fa-2x d-block mb-2"></i>
                  <span>Review Appeals</span>
                </a>
              </div>
              <div class="col-md-3 mb-3">
                <a href="#" class="btn btn-success w-100 py-3" data-bs-toggle="modal" data-bs-target="#viewDecisionsModal">
                  <i class="fas fa-history fa-2x d-block mb-2"></i>
                  <span>My Decisions</span>
                </a>
              </div>
              <div class="col-md-3 mb-3">
                <a href="#" class="btn btn-info w-100 py-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                  <i class="fas fa-lock fa-2x d-block mb-2"></i>
                  <span>Change Password</span>
                </a>
              </div>
              <div class="col-md-3 mb-3">
                <a href="logout.php" class="btn btn-danger w-100 py-3">
                  <i class="fas fa-sign-out-alt fa-2x d-block mb-2"></i>
                  <span>Logout</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending Appeals Summary -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4 class="text-white mb-0">
              <i class="fas fa-list me-2"></i>Pending Appeals (Summary)
            </h4>
          </div>
          <div class="card-body">
            <?php
            $stmt = $connection->prepare("
              SELECT ac.appeal_id, t.name as team_name, dc.offence_description, ac.appeal_date
              FROM appeal_cases ac
              JOIN team t ON ac.team_id = t.team_id
              LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
              WHERE ac.status = 'pending'
              ORDER BY ac.appeal_date ASC
              LIMIT 5
            ");
            $stmt->execute();
            $pending_appeals = $stmt->fetchAll();
            ?>

            <?php if (empty($pending_appeals)): ?>
              <p class="text-muted">No pending appeals at this time.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th>Team</th>
                      <th>Offense</th>
                      <th>Submitted</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pending_appeals as $appeal): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($appeal['team_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars(substr($appeal['offence_description'] ?? 'N/A', 0, 40)); ?></td>
                        <td><?php echo date('M d, Y', strtotime($appeal['appeal_date'])); ?></td>
                        <td>
                          <a href="discipline_committee_dashboard.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right"></i> Review
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="text-center mt-3">
                <a href="discipline_committee_dashboard.php" class="btn btn-primary">
                  View All Pending Appeals
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" class="form-control" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="new_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirm_password" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Decisions Modal -->
<div class="modal fade" id="viewDecisionsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">My Decisions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php
        $stmt = $connection->prepare("
          SELECT ac.*, t.name as team_name, dc.offence_description
          FROM appeal_cases ac
          JOIN team t ON ac.team_id = t.team_id
          LEFT JOIN ai_discipline_cases dc ON ac.discipline_case_id = dc.case_id
          WHERE ac.status IN ('approved', 'rejected')
          ORDER BY ac.decision_date DESC
          LIMIT 10
        ");
        $stmt->execute();
        $decisions = $stmt->fetchAll();
        ?>

        <?php if (empty($decisions)): ?>
          <p class="text-muted">No decisions made yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Team</th>
                  <th>Decision</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($decisions as $decision): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($decision['team_name']); ?></td>
                    <td>
                      <span class="badge badge-<?php echo $decision['status']; ?>">
                        <?php echo strtoupper($decision['status']); ?>
                      </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($decision['decision_date'])); ?></td>
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

<?php require 'footer.php'; ?>
