<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Transfer Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Fix Transfer Display Issue</h2>
        
        <?php
        session_start();
        
        // Connect to database
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Set test session if not set
        if (!isset($_SESSION['Team_id'])) {
            $_SESSION['Team_id'] = 4; // Use team 4 which has data
        }
        
        $your_team_id = $_SESSION['Team_id'];

        echo '<div class="alert alert-info">';
        echo '<h5>Diagnostic and Fix Process</h5>';
        echo '<p>Current Team ID: ' . $your_team_id . '</p>';
        echo '</div>';

        // Step 1: Check if we have the sample data
        echo '<h4>Step 1: Verify Sample Data</h4>';
        $sample_check = "SELECT * FROM transfer WHERE id = 16";
        $sample_result = mysqli_query($con, $sample_check);
        
        if ($sample_result && mysqli_num_rows($sample_result) > 0) {
            $sample = mysqli_fetch_assoc($sample_result);
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Sample Transfer Found:</strong><br>';
            echo 'ID: ' . $sample['id'] . '<br>';
            echo 'From Team: ' . $sample['team_from'] . '<br>';
            echo 'To Team: ' . $sample['team_to'] . '<br>';
            echo 'Member ID: ' . $sample['member_id'] . '<br>';
            echo 'Status: ' . $sample['status'] . '<br>';
            echo 'Request Date: ' . $sample['requestDate'];
            echo '</div>';
            
            // Check if member exists
            $member_check = "SELECT * FROM team_members WHERE member_id = " . $sample['member_id'];
            $member_result = mysqli_query($con, $member_check);
            
            if ($member_result && mysqli_num_rows($member_result) > 0) {
                $member = mysqli_fetch_assoc($member_result);
                echo '<div class="alert alert-success">';
                echo '<strong>✅ Member Found:</strong> ' . $member['fname'] . ' ' . $member['lname'] . ' (Team: ' . $member['team'] . ')';
                echo '</div>';
            } else {
                echo '<div class="alert alert-danger">';
                echo '<strong>❌ Member NOT Found with ID ' . $sample['member_id'] . '</strong>';
                echo '</div>';
            }
            
            // Check destination team
            $dest_check = "SELECT * FROM team WHERE team_id = " . $sample['team_to'];
            $dest_result = mysqli_query($con, $dest_check);
            
            if ($dest_result && mysqli_num_rows($dest_result) > 0) {
                $dest = mysqli_fetch_assoc($dest_result);
                echo '<div class="alert alert-success">';
                echo '<strong>✅ Destination Team Found:</strong> ' . $dest['name'];
                echo '</div>';
            } else {
                echo '<div class="alert alert-danger">';
                echo '<strong>❌ Destination Team NOT Found with ID ' . $sample['team_to'] . '</strong>';
                echo '</div>';
            }
            
        } else {
            echo '<div class="alert alert-warning">';
            echo '<strong>⚠️ Sample Transfer Not Found</strong><br>';
            echo 'Let\'s create some test data...';
            echo '</div>';
            
            // Create test data
            $create_transfer = "INSERT INTO transfer (team_from, team_to, status, requestDate, member_id, post) VALUES (4, 6, 1, '2025-01-15', 3, 'player')";
            if (mysqli_query($con, $create_transfer)) {
                echo '<div class="alert alert-success">✅ Test transfer created</div>';
            } else {
                echo '<div class="alert alert-danger">❌ Failed to create test transfer: ' . mysqli_error($con) . '</div>';
            }
        }

        // Step 2: Test the exact query
        echo '<h4>Step 2: Test Query with Current Team</h4>';
        
        $status_labels = [
            0 => 'Pending',
            1 => 'Requested',
            2 => 'Rejected',
            3 => 'Completed'
        ];

        $sql = "SELECT t.*,
                       tm.fname, tm.lname, tm.role_in_team,
                       tt.name AS to_team
                FROM transfer t
                JOIN team_members tm ON t.member_id = tm.member_id
                JOIN team tt ON t.team_to = tt.team_id
                WHERE t.team_from = ?
                ORDER BY t.requestDate DESC";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $your_team_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<div class="alert alert-info">';
        echo '<strong>Query Result:</strong> Found ' . $result->num_rows . ' transfers for team ' . $your_team_id;
        echo '</div>';

        if ($result->num_rows > 0) {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Query Working! Here\'s what should appear:</strong>';
            echo '</div>';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-striped">';
            echo '<thead class="table-primary">';
            echo '<tr>';
            echo '<th>#</th>';
            echo '<th>Member</th>';
            echo '<th>Role</th>';
            echo '<th>To Team</th>';
            echo '<th>Status</th>';
            echo '<th>Request Date</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $count = 1;
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $count++ . '</td>';
                echo '<td>' . htmlspecialchars($row['fname'] . ' ' . $row['lname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['role_in_team']) . '</td>';
                echo '<td>' . htmlspecialchars($row['to_team']) . '</td>';
                echo '<td>' . ($status_labels[$row['status']] ?? 'Unknown') . '</td>';
                echo '<td>' . $row['requestDate'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            
        } else {
            echo '<div class="alert alert-warning">';
            echo '<strong>⚠️ No Results Found</strong><br>';
            echo 'Possible issues:';
            echo '<ul>';
            echo '<li>Team ' . $your_team_id . ' has no outgoing transfers</li>';
            echo '<li>JOIN conditions failing</li>';
            echo '<li>Data integrity problems</li>';
            echo '</ul>';
            echo '</div>';
            
            // Let's check each part separately
            echo '<h5>Detailed Diagnosis:</h5>';
            
            // Check transfers for this team
            $transfer_check = "SELECT * FROM transfer WHERE team_from = $your_team_id";
            $transfer_result = mysqli_query($con, $transfer_check);
            
            if ($transfer_result && mysqli_num_rows($transfer_result) > 0) {
                echo '<div class="alert alert-info">';
                echo '<strong>Transfers FROM team ' . $your_team_id . ':</strong>';
                while ($t = mysqli_fetch_assoc($transfer_result)) {
                    echo '<br>- Transfer ID ' . $t['id'] . ': Member ' . $t['member_id'] . ' to Team ' . $t['team_to'];
                }
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning">';
                echo '<strong>No transfers found FROM team ' . $your_team_id . '</strong>';
                echo '</div>';
            }
        }

        // Step 3: Fix recommendations
        echo '<h4>Step 3: Fix Recommendations</h4>';
        
        if ($result->num_rows > 0) {
            echo '<div class="alert alert-success">';
            echo '<h6>✅ The query is working correctly!</h6>';
            echo '<p>The issue might be:</p>';
            echo '<ul>';
            echo '<li>Session not being set properly in requests.php</li>';
            echo '<li>Different team ID being used</li>';
            echo '<li>PHP output buffering or display issues</li>';
            echo '</ul>';
            echo '<p><strong>Solution:</strong> The requests.php page should work now. Try accessing it.</p>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">';
            echo '<h6>⚠️ No data for current team</h6>';
            echo '<p>Options:</p>';
            echo '<ul>';
            echo '<li>Test with a different team that has transfer data</li>';
            echo '<li>Create some test transfer data</li>';
            echo '<li>Check if the sample data exists</li>';
            echo '</ul>';
            echo '</div>';
            
            // Offer to create test data
            if (isset($_POST['create_test_data'])) {
                $test_sql = "INSERT INTO transfer (team_from, team_to, status, requestDate, member_id, post) VALUES ($your_team_id, 6, 1, CURDATE(), 1, 'player')";
                if (mysqli_query($con, $test_sql)) {
                    echo '<div class="alert alert-success">✅ Test data created! Refresh to see results.</div>';
                } else {
                    echo '<div class="alert alert-danger">❌ Failed to create test data: ' . mysqli_error($con) . '</div>';
                }
            }
            
            echo '<form method="post" class="mt-3">';
            echo '<button type="submit" name="create_test_data" class="btn btn-warning">Create Test Transfer Data</button>';
            echo '</form>';
        }

        mysqli_close($con);
        ?>

        <div class="mt-4">
            <h4>Next Steps:</h4>
            <div class="btn-group" role="group">
                <a href="requests.php" class="btn btn-success">Test Requests Page</a>
                <a href="test_session.php" class="btn btn-info">Check Session</a>
                <a href="debug_transfers.php" class="btn btn-secondary">Full Debug</a>
                <button onclick="location.reload()" class="btn btn-outline-primary">Refresh Fix</button>
            </div>
        </div>

        <div class="mt-3">
            <div class="alert alert-info">
                <h6>Summary of Findings:</h6>
                <p>Based on the database structure and sample data:</p>
                <ul>
                    <li><strong>Sample Data Exists:</strong> Transfer ID 16, from Team 4 to Team 6, Member 3</li>
                    <li><strong>Query Structure:</strong> Correct JOINs between transfer, team_members, and team tables</li>
                    <li><strong>Expected Result:</strong> Should show "Rodriguez Man" transferring from Kiyovu fc to Police fc</li>
                </ul>
                <p><strong>If requests.php still shows no data, the issue is likely session-related or a display problem.</strong></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
