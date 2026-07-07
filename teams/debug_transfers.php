<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Transfers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Debug Transfer Display Issue</h2>
        
        <?php
        session_start();
        
        // Connect to database
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            die("Connection failed: " . mysqli_connect_error());
        }

        echo '<div class="alert alert-info">';
        echo '<h5>Debugging Transfer Display</h5>';
        echo '<p>Let\'s check what data exists and why it\'s not showing up.</p>';
        echo '</div>';

        // Test with different team IDs
        $test_team_id = isset($_GET['team']) ? (int)$_GET['team'] : 4; // Default to team 4 which has data
        $_SESSION['Team_id'] = $test_team_id;
        
        echo '<div class="mb-3">';
        echo '<label class="form-label">Test with different teams:</label><br>';
        for ($i = 1; $i <= 10; $i++) {
            $active = ($i == $test_team_id) ? 'btn-primary' : 'btn-outline-primary';
            echo '<a href="?team=' . $i . '" class="btn ' . $active . ' btn-sm me-1">Team ' . $i . '</a>';
        }
        echo '</div>';

        echo '<div class="alert alert-primary">';
        echo '<strong>Currently testing with Team ID: ' . $test_team_id . '</strong>';
        echo '</div>';

        // Step 1: Check if team exists
        echo '<h4>Step 1: Check Team Exists</h4>';
        $team_check = "SELECT * FROM team WHERE team_id = ?";
        $team_stmt = $con->prepare($team_check);
        $team_stmt->bind_param("i", $test_team_id);
        $team_stmt->execute();
        $team_result = $team_stmt->get_result();
        $team_data = $team_result->fetch_assoc();

        if ($team_data) {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Team Found:</strong> ' . htmlspecialchars($team_data['name']) . ' (ID: ' . $team_data['team_id'] . ')';
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ Team NOT Found:</strong> No team with ID ' . $test_team_id;
            echo '</div>';
        }

        // Step 2: Check raw transfer data
        echo '<h4>Step 2: Check Raw Transfer Data</h4>';
        $raw_transfers = "SELECT * FROM transfer";
        $raw_result = mysqli_query($con, $raw_transfers);
        
        if ($raw_result && mysqli_num_rows($raw_result) > 0) {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Transfer Records Found:</strong> ' . mysqli_num_rows($raw_result) . ' total transfers';
            echo '</div>';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm table-bordered">';
            echo '<thead class="table-dark">';
            echo '<tr><th>ID</th><th>Team From</th><th>Team To</th><th>Member ID</th><th>Status</th><th>Request Date</th><th>Post</th></tr>';
            echo '</thead><tbody>';
            
            while ($transfer = mysqli_fetch_assoc($raw_result)) {
                $highlight = ($transfer['team_from'] == $test_team_id) ? 'table-success' : '';
                echo '<tr class="' . $highlight . '">';
                echo '<td>' . $transfer['id'] . '</td>';
                echo '<td>' . $transfer['team_from'] . '</td>';
                echo '<td>' . $transfer['team_to'] . '</td>';
                echo '<td>' . $transfer['member_id'] . '</td>';
                echo '<td>' . $transfer['status'] . '</td>';
                echo '<td>' . $transfer['requestDate'] . '</td>';
                echo '<td>' . $transfer['post'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ No Transfer Records Found</strong>';
            echo '</div>';
        }

        // Step 3: Check team_members data
        echo '<h4>Step 3: Check Team Members Data</h4>';
        $members_query = "SELECT * FROM team_members";
        $members_result = mysqli_query($con, $members_query);
        
        if ($members_result && mysqli_num_rows($members_result) > 0) {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Team Members Found:</strong> ' . mysqli_num_rows($members_result) . ' total members';
            echo '</div>';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm table-bordered">';
            echo '<thead class="table-dark">';
            echo '<tr><th>Member ID</th><th>Name</th><th>Team</th><th>Role</th><th>Post</th><th>Position</th></tr>';
            echo '</thead><tbody>';
            
            while ($member = mysqli_fetch_assoc($members_result)) {
                $highlight = ($member['team'] == $test_team_id) ? 'table-info' : '';
                echo '<tr class="' . $highlight . '">';
                echo '<td>' . $member['member_id'] . '</td>';
                echo '<td>' . htmlspecialchars($member['fname'] . ' ' . $member['lname']) . '</td>';
                echo '<td>' . $member['team'] . '</td>';
                echo '<td>' . $member['role_in_team'] . '</td>';
                echo '<td>' . $member['post'] . '</td>';
                echo '<td>' . $member['position'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ No Team Members Found</strong>';
            echo '</div>';
        }

        // Step 4: Test the exact query from requests.php
        echo '<h4>Step 4: Test Exact Query from requests.php</h4>';
        $sql = "SELECT t.*,
                       tm.fname, tm.lname, tm.role_in_team,
                       tt.name AS to_team
                FROM transfer t
                JOIN team_members tm ON t.member_id = tm.member_id
                JOIN team tt ON t.team_to = tt.team_id
                WHERE t.team_from = ?
                ORDER BY t.requestDate DESC";

        echo '<div class="alert alert-info">';
        echo '<strong>Query:</strong><br>';
        echo '<code>' . str_replace('?', $test_team_id, $sql) . '</code>';
        echo '</div>';

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $test_team_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && mysqli_num_rows($result) > 0) {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Query Successful:</strong> Found ' . mysqli_num_rows($result) . ' transfers for team ' . $test_team_id;
            echo '</div>';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered">';
            echo '<thead class="table-primary">';
            echo '<tr><th>#</th><th>Member</th><th>Role</th><th>To Team</th><th>Status</th><th>Request Date</th></tr>';
            echo '</thead><tbody>';
            
            $count = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                echo '<td>' . $count++ . '</td>';
                echo '<td>' . htmlspecialchars($row['fname'] . ' ' . $row['lname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['role_in_team']) . '</td>';
                echo '<td>' . htmlspecialchars($row['to_team']) . '</td>';
                echo '<td>' . $row['status'] . '</td>';
                echo '<td>' . $row['requestDate'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">';
            echo '<strong>⚠️ Query Returned No Results</strong>';
            echo '<p>Possible reasons:</p>';
            echo '<ul>';
            echo '<li>No transfers FROM team ' . $test_team_id . '</li>';
            echo '<li>JOIN conditions not matching</li>';
            echo '<li>Data integrity issues</li>';
            echo '</ul>';
            echo '</div>';
            
            // Let's check each JOIN condition separately
            echo '<h5>Debugging JOIN Conditions:</h5>';
            
            // Check transfer records for this team
            $transfer_check = "SELECT * FROM transfer WHERE team_from = ?";
            $transfer_stmt = $con->prepare($transfer_check);
            $transfer_stmt->bind_param("i", $test_team_id);
            $transfer_stmt->execute();
            $transfer_result = $transfer_stmt->get_result();
            
            if ($transfer_result && mysqli_num_rows($transfer_result) > 0) {
                echo '<div class="alert alert-info">';
                echo '<strong>Transfers FROM team ' . $test_team_id . ':</strong>';
                echo '<ul>';
                while ($t = mysqli_fetch_assoc($transfer_result)) {
                    echo '<li>Transfer ID ' . $t['id'] . ': Member ' . $t['member_id'] . ' to Team ' . $t['team_to'] . '</li>';
                    
                    // Check if member exists
                    $member_check = "SELECT * FROM team_members WHERE member_id = " . $t['member_id'];
                    $member_result = mysqli_query($con, $member_check);
                    if ($member_result && mysqli_num_rows($member_result) > 0) {
                        $member = mysqli_fetch_assoc($member_result);
                        echo '<li style="margin-left: 20px;">✅ Member found: ' . $member['fname'] . ' ' . $member['lname'] . '</li>';
                    } else {
                        echo '<li style="margin-left: 20px;">❌ Member NOT found with ID ' . $t['member_id'] . '</li>';
                    }
                    
                    // Check if destination team exists
                    $dest_team_check = "SELECT * FROM team WHERE team_id = " . $t['team_to'];
                    $dest_team_result = mysqli_query($con, $dest_team_check);
                    if ($dest_team_result && mysqli_num_rows($dest_team_result) > 0) {
                        $dest_team = mysqli_fetch_assoc($dest_team_result);
                        echo '<li style="margin-left: 20px;">✅ Destination team found: ' . $dest_team['name'] . '</li>';
                    } else {
                        echo '<li style="margin-left: 20px;">❌ Destination team NOT found with ID ' . $t['team_to'] . '</li>';
                    }
                }
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning">';
                echo '<strong>No transfers found FROM team ' . $test_team_id . '</strong>';
                echo '</div>';
            }
        }

        mysqli_close($con);
        ?>

        <div class="mt-4">
            <a href="requests.php" class="btn btn-primary">Go to Requests Page</a>
            <button onclick="location.reload()" class="btn btn-secondary">Refresh Debug</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
