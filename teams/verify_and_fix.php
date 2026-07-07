<?php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle actions
$message = '';
$message_type = 'info';

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_test_data':
            // Create test transfer data
            $insert_sql = "INSERT INTO transfer (team_from, team_to, status, requestDate, member_id, post) VALUES (4, 6, 1, CURDATE(), 3, 'player')";
            if (mysqli_query($con, $insert_sql)) {
                $message = "✅ Test transfer data created successfully!";
                $message_type = 'success';
            } else {
                $message = "❌ Failed to create test data: " . mysqli_error($con);
                $message_type = 'danger';
            }
            break;
            
        case 'set_session':
            $_SESSION['Team_id'] = 4;
            $message = "✅ Session set to Team 4 (Kiyovu fc)";
            $message_type = 'success';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify and Fix Transfer Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>🔧 Transfer Display - Verify and Fix</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Step 1: Database Check -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5>📊 Step 1: Database Verification</h5>
            </div>
            <div class="card-body">
                <?php
                // Check tables exist
                $tables = ['team', 'team_members', 'transfer'];
                $all_tables_exist = true;
                
                foreach ($tables as $table) {
                    $check_table = "SHOW TABLES LIKE '$table'";
                    $result = mysqli_query($con, $check_table);
                    if (mysqli_num_rows($result) > 0) {
                        echo "<span class='badge bg-success me-2'>✅ $table</span>";
                    } else {
                        echo "<span class='badge bg-danger me-2'>❌ $table</span>";
                        $all_tables_exist = false;
                    }
                }
                
                if ($all_tables_exist) {
                    echo "<div class='alert alert-success mt-3'>All required tables exist!</div>";
                } else {
                    echo "<div class='alert alert-danger mt-3'>Some tables are missing. Please check your database setup.</div>";
                }
                ?>
            </div>
        </div>

        <!-- Step 2: Data Check -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5>📋 Step 2: Data Verification</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Teams:</h6>
                        <?php
                        $teams_result = mysqli_query($con, "SELECT team_id, name FROM team");
                        if ($teams_result && mysqli_num_rows($teams_result) > 0) {
                            echo "<ul class='list-group list-group-flush'>";
                            while ($team = mysqli_fetch_assoc($teams_result)) {
                                echo "<li class='list-group-item'>ID {$team['team_id']}: {$team['name']}</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<div class='alert alert-warning'>No teams found</div>";
                        }
                        ?>
                    </div>
                    <div class="col-md-4">
                        <h6>Team Members:</h6>
                        <?php
                        $members_result = mysqli_query($con, "SELECT member_id, fname, lname, team FROM team_members");
                        if ($members_result && mysqli_num_rows($members_result) > 0) {
                            echo "<ul class='list-group list-group-flush'>";
                            while ($member = mysqli_fetch_assoc($members_result)) {
                                echo "<li class='list-group-item'>ID {$member['member_id']}: {$member['fname']} {$member['lname']} (Team {$member['team']})</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<div class='alert alert-warning'>No team members found</div>";
                        }
                        ?>
                    </div>
                    <div class="col-md-4">
                        <h6>Transfers:</h6>
                        <?php
                        $transfers_result = mysqli_query($con, "SELECT id, team_from, team_to, member_id, status FROM transfer");
                        if ($transfers_result && mysqli_num_rows($transfers_result) > 0) {
                            echo "<ul class='list-group list-group-flush'>";
                            while ($transfer = mysqli_fetch_assoc($transfers_result)) {
                                echo "<li class='list-group-item'>ID {$transfer['id']}: Team {$transfer['team_from']} → Team {$transfer['team_to']} (Member {$transfer['member_id']})</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<div class='alert alert-warning'>No transfers found</div>";
                            echo "<form method='post' class='mt-2'>";
                            echo "<input type='hidden' name='action' value='create_test_data'>";
                            echo "<button type='submit' class='btn btn-sm btn-warning'>Create Test Data</button>";
                            echo "</form>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Session Check -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5>🔐 Step 3: Session Verification</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Current Session:</h6>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong>Team ID:</strong> <?= $_SESSION['Team_id'] ?? 'Not set' ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Session ID:</strong> <?= session_id() ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Quick Actions:</h6>
                        <?php if (!isset($_SESSION['Team_id'])): ?>
                            <form method="post">
                                <input type="hidden" name="action" value="set_session">
                                <button type="submit" class="btn btn-primary">Set Session to Team 4</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success">✅ Session is set!</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Test Query -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>🧪 Step 4: Test Transfer Query</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['Team_id'])): ?>
                    <?php
                    $team_id = $_SESSION['Team_id'];
                    $test_sql = "SELECT t.*, tm.fname, tm.lname, tm.role_in_team, tt.name AS to_team
                                FROM transfer t
                                JOIN team_members tm ON t.member_id = tm.member_id
                                JOIN team tt ON t.team_to = tt.team_id
                                WHERE t.team_from = ?
                                ORDER BY t.requestDate DESC";
                    
                    $stmt = $con->prepare($test_sql);
                    $stmt->bind_param("i", $team_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    ?>
                    
                    <div class="alert alert-info">
                        <strong>Query Result:</strong> Found <?= $result->num_rows ?> transfers for Team <?= $team_id ?>
                    </div>
                    
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Transfer ID</th>
                                        <th>Member</th>
                                        <th>Role</th>
                                        <th>To Team</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                                            <td><?= htmlspecialchars($row['role_in_team']) ?></td>
                                            <td><?= htmlspecialchars($row['to_team']) ?></td>
                                            <td><?= $row['status'] ?></td>
                                            <td><?= $row['requestDate'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-success">
                            <h6>✅ Query is working perfectly!</h6>
                            <p>The transfer requests should now display correctly in the main page.</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <h6>⚠️ No transfers found for Team <?= $team_id ?></h6>
                            <p>This team has no outgoing transfer requests.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">Please set a session first to test the query.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Final Actions -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5>🚀 Final Step: Test the Solution</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="requests.php" class="btn btn-success btn-lg">
                        🎯 Go to Transfer Requests Page
                    </a>
                    <a href="setup_session.php" class="btn btn-info">
                        🔧 Setup Session
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary">
                        🔄 Refresh Check
                    </button>
                </div>
                
                <div class="mt-3">
                    <div class="alert alert-info">
                        <h6>📝 Expected Result:</h6>
                        <p>If everything is working correctly, you should see transfer requests displayed in a table format when you visit the requests page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php mysqli_close($con); ?>
