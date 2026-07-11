<?php require_once 'header.php'; ?>

<style>
  .card {
    background-color: #1e293b;
    border-radius: 12px;
    border: none;
    color: #f1f5f9;
  }
  .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
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
  }
  tbody tr:hover {
    background-color: #475569;
  }
  tbody td {
    color: #f8fafc;
  }
</style>

<div class="page-wrapper">
  <div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="mb-4">
      <h2 class="text-white mb-2">
        <i class="fas fa-users-cog me-2">Committee Member Management</i>
      </h2>
      <p class="text-muted">Add, edit, and manage discipline committee officers</p>
    </div>

    <!-- Add New Member Form -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4 class="text-white mb-0">
              <i class="fas fa-user-plus me-2"></i>Add New Committee Member
            </h4>
          </div>
          <div class="card-body">
            <?php
            $success_msg = $error_msg = '';

            if ($_POST && isset($_POST['add_member'])) {
                $name = trim($_POST['name']);
                $username = trim($_POST['username']);
                $password = trim($_POST['password']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);
                $role = trim($_POST['role']);

                if (empty($name) || empty($username) || empty($password)) {
                    $error_msg = "Name, username, and password are required.";
                } else {
                    try {
                        $stmt = $connection->prepare("
                            INSERT INTO committee_members (name, username, password, email, phone, role, status)
                            VALUES (?, ?, ?, ?, ?, ?, 'active')
                        ");
                        $stmt->execute([$name, $username, $password, $email, $phone, $role]);
                        $success_msg = "Committee member added successfully!";
                    } catch (Exception $e) {
                        $error_msg = "Error: " . $e->getMessage();
                    }
                }
            }

            if ($success_msg) {
                echo '<div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> ' . $success_msg . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
            }
            if ($error_msg) {
                echo '<div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> ' . $error_msg . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                      </div>';
            }
            ?>

            <form method="POST">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label"><strong>Full Name</strong></label>
                  <input type="text" class="form-control" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label"><strong>Username</strong></label>
                  <input type="text" class="form-control" name="username" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label"><strong>Password</strong></label>
                  <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label"><strong>Email</strong></label>
                  <input type="email" class="form-control" name="email">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label"><strong>Phone</strong></label>
                  <input type="text" class="form-control" name="phone">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label"><strong>Role</strong></label>
                  <select class="form-control" name="role">
                    <option value="Member">Member</option>
                    <option value="Chairperson">Chairperson</option>
                    <option value="Secretary">Secretary</option>
                    <option value="Treasurer">Treasurer</option>
                  </select>
                </div>
              </div>

              <button type="submit" name="add_member" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Add Member
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Committee Members List -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h4 class="text-white mb-0">
              <i class="fas fa-list me-2"></i>Committee Members
            </h4>
          </div>
          <div class="card-body">
            <?php
            if ($_POST && isset($_POST['delete_member'])) {
                $member_id = $_POST['member_id'];
                try {
                    $stmt = $connection->prepare("DELETE FROM committee_members WHERE committee_id = ?");
                    $stmt->execute([$member_id]);
                    echo '<div class="alert alert-success">Member deleted successfully!</div>';
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            }

            $stmt = $connection->prepare("
              SELECT * FROM committee_members
              ORDER BY role DESC, name ASC
            ");
            $stmt->execute();
            $members = $stmt->fetchAll();
            ?>

            <?php if (empty($members)): ?>
              <p class="text-muted">No committee members found.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Username</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th>Joined</th>
                      <th width="120">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($members as $member): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($member['username']); ?></td>
                        <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                        <td>
                          <span class="badge bg-info"><?php echo htmlspecialchars($member['role']); ?></span>
                        </td>
                        <td>
                          <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($member['status']); ?>
                          </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($member['created_at'])); ?></td>
                        <td>
                          <form method="POST" style="display: inline;">
                            <input type="hidden" name="member_id" value="<?php echo $member['committee_id']; ?>">
                            <button type="submit" name="delete_member" class="btn btn-sm btn-danger" onclick="return confirm('Delete this member?');">
                              <i class="fas fa-trash"></i>
                            </button>
                          </form>
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