<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="text-center mb-5">
                    <h1 class="display-4">🔧 Transfer Tools</h1>
                    <p class="lead">Debug and Fix Transfer Display Issues</p>
                </div>

                <div class="row">
                    <!-- Main Action -->
                    <div class="col-md-12 mb-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white text-center">
                                <h4>🎯 MAIN GOAL: Fix Transfer Display</h4>
                            </div>
                            <div class="card-body text-center">
                                <a href="verify_and_fix.php" class="btn btn-success btn-lg">
                                    🚀 START HERE: Verify & Fix All Issues
                                </a>
                                <p class="mt-2 text-muted">This will check everything and guide you through the fix</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Step by Step Tools -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5>🔍 Diagnostic Tools</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="debug_transfers.php" class="btn btn-outline-primary">
                                        🐛 Debug Transfer Data
                                    </a>
                                    <a href="test_session.php" class="btn btn-outline-info">
                                        🔐 Test Session Status
                                    </a>
                                    <a href="test_simple_requests.php" class="btn btn-outline-secondary">
                                        📋 Test Simple Requests
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5>⚡ Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="setup_session.php" class="btn btn-warning">
                                        ⚙️ Setup Team Session
                                    </a>
                                    <a href="requests.php" class="btn btn-success">
                                        📋 View Transfer Requests
                                    </a>
                                    <a href="../login.php" class="btn btn-outline-dark">
                                        🔐 Proper Team Login
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Status Check -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5>📊 Current System Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        session_start();
                        
                        // Check database
                        $con = mysqli_connect("localhost", "root", "", "fa_db");
                        $db_connected = $con ? true : false;
                        
                        // Check session
                        $session_set = isset($_SESSION['Team_id']);
                        $team_id = $_SESSION['Team_id'] ?? 'Not set';
                        
                        // Check transfers
                        $transfer_count = 0;
                        $sample_transfer_exists = false;
                        
                        if ($db_connected) {
                            // Count total transfers
                            $result = mysqli_query($con, "SELECT COUNT(*) as count FROM transfer");
                            if ($result) {
                                $row = mysqli_fetch_assoc($result);
                                $transfer_count = $row['count'];
                            }
                            
                            // Check for sample transfer (ID 16)
                            $sample_check = mysqli_query($con, "SELECT * FROM transfer WHERE id = 16");
                            $sample_transfer_exists = $sample_check && mysqli_num_rows($sample_check) > 0;
                            
                            mysqli_close($con);
                        }
                        ?>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="alert alert-<?= $db_connected ? 'success' : 'danger' ?>">
                                    <strong>Database:</strong><br>
                                    <?= $db_connected ? '✅ Connected' : '❌ Failed' ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-<?= $session_set ? 'success' : 'warning' ?>">
                                    <strong>Session:</strong><br>
                                    <?= $session_set ? "✅ Team $team_id" : '⚠️ Not set' ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-<?= $transfer_count > 0 ? 'success' : 'warning' ?>">
                                    <strong>Transfers:</strong><br>
                                    <?= $transfer_count > 0 ? "✅ $transfer_count records" : '⚠️ No data' ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-<?= $sample_transfer_exists ? 'success' : 'warning' ?>">
                                    <strong>Sample Data:</strong><br>
                                    <?= $sample_transfer_exists ? '✅ Exists' : '⚠️ Missing' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Problem Solving Guide -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5>🎯 Problem Solving Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>🚨 If transfers are not showing:</h6>
                                <ol>
                                    <li><strong>Check Session:</strong> Use "Setup Team Session"</li>
                                    <li><strong>Set Team 4:</strong> This team has sample data</li>
                                    <li><strong>Verify Database:</strong> Use "Debug Transfer Data"</li>
                                    <li><strong>Test Query:</strong> Use "Verify & Fix All Issues"</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>✅ Expected Result:</h6>
                                <div class="alert alert-light">
                                    <strong>Team 4 (Kiyovu fc) should show:</strong><br>
                                    <em>Rodriguez Man → Police fc (Requested)</em>
                                </div>
                                <p><small>Other teams may show "No transfer requests" which is normal.</small></p>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <div class="alert alert-success">
                                <h6>🎯 Quick Fix Steps:</h6>
                                <p class="mb-2">1. Click "Verify & Fix All Issues" → 2. Set Session to Team 4 → 3. Go to "View Transfer Requests"</p>
                                <a href="verify_and_fix.php" class="btn btn-success">Start Quick Fix</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
