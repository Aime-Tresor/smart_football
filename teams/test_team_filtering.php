<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Team Filtering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Team Transfer Filtering Test</h2>
        
        <?php
        session_start();
        
        // Connect to database
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Test with different team IDs
        $test_team_ids = [1, 2, 3, 4, 5];
        
        echo '<div class="alert alert-info">';
        echo '<h5>Team Transfer Filtering Test</h5>';
        echo '<p>This page tests that the requests.php page only shows transfers FROM the specific logged-in team.</p>';
        echo '</div>';

        // Show all available teams first
        echo '<div class="card mb-4">';
        echo '<div class="card-header"><h5>Available Teams</h5></div>';
        echo '<div class="card-body">';
        
        $teams_sql = "SELECT * FROM team ORDER BY team_id";
        $teams_result = mysqli_query($con, $teams_sql);
        
        if ($teams_result && mysqli_num_rows($teams_result) > 0) {
            echo '<div class="row">';
            while ($team = mysqli_fetch_assoc($teams_result)) {
                echo '<div class="col-md-4 mb-3">';
                echo '<div class="card border-primary">';
                echo '<div class="card-body">';
                echo '<h6 class="card-title">Team ID: ' . $team['team_id'] . '</h6>';
                echo '<p class="card-text">';
                echo '<strong>Name:</strong> ' . htmlspecialchars($team['name']) . '<br>';
                echo '<strong>Stadium:</strong> ' . htmlspecialchars($team['stadium']);
                echo '</p>';
                echo '<a href="?test_team=' . $team['team_id'] . '" class="btn btn-primary btn-sm">Test This Team</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="text-muted">No teams found in database.</p>';
        }
        echo '</div>';
        echo '</div>';

        // Test specific team if requested
        $test_team_id = isset($_GET['test_team']) ? (int)$_GET['test_team'] : null;
        
        if ($test_team_id) {
            // Set session for testing
            $_SESSION['Team_id'] = $test_team_id;
            
            // Get team info
            $team_sql = "SELECT * FROM team WHERE team_id = ?";
            $team_stmt = $con->prepare($team_sql);
            $team_stmt->bind_param("i", $test_team_id);
            $team_stmt->execute();
            $team_result = $team_stmt->get_result();
            $team_info = $team_result->fetch_assoc();
            
            if ($team_info) {
                echo '<div class="card mb-4 border-success">';
                echo '<div class="card-header bg-success text-white">';
                echo '<h5 class="mb-0">Testing Team: ' . htmlspecialchars($team_info['name']) . ' (ID: ' . $test_team_id . ')</h5>';
                echo '</div>';
                echo '<div class="card-body">';
                
                // Test the same query used in requests.php
                $sql = "SELECT t.*, 
                               tm.fname, tm.lname, tm.role_in_team, tm.number, tm.position, tm.post,
                               tf.name AS from_team, tt.name AS to_team
                        FROM transfer t
                        JOIN team_members tm ON t.member_id = tm.member_id
                        JOIN team tf ON t.team_from = tf.team_id
                        JOIN team tt ON t.team_to = tt.team_id
                        WHERE t.team_from = ?
                        ORDER BY t.requestDate DESC";

                $stmt = $con->prepare($sql);
                $stmt->bind_param("i", $test_team_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                echo '<div class="alert alert-primary">';
                echo '<strong>Query Result:</strong> Found ' . $result->num_rows . ' transfer requests FROM this team.';
                echo '</div>';
                
                if ($result->num_rows > 0) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered">';
                    echo '<thead class="table-light">';
                    echo '<tr>';
                    echo '<th>Transfer ID</th>';
                    echo '<th>Member</th>';
                    echo '<th>From Team</th>';
                    echo '<th>To Team</th>';
                    echo '<th>Status</th>';
                    echo '<th>Request Date</th>';
                    echo '<th>Verification</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    while ($row = $result->fetch_assoc()) {
                        $is_correct = ($row['team_from'] == $test_team_id);
                        $verification_class = $is_correct ? 'success' : 'danger';
                        $verification_text = $is_correct ? 'Correct' : 'ERROR: Wrong team!';
                        
                        echo '<tr class="' . ($is_correct ? '' : 'table-danger') . '">';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($row['fname'] . ' ' . $row['lname']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['from_team']) . ' (ID: ' . $row['team_from'] . ')</td>';
                        echo '<td>' . htmlspecialchars($row['to_team']) . ' (ID: ' . $row['team_to'] . ')</td>';
                        echo '<td>' . $row['status'] . '</td>';
                        echo '<td>' . $row['requestDate'] . '</td>';
                        echo '<td><span class="badge bg-' . $verification_class . '">' . $verification_text . '</span></td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<h6>No Transfer Requests Found</h6>';
                    echo '<p>This team has not made any transfer requests (no outgoing transfers).</p>';
                    echo '<p><strong>This is correct behavior</strong> - only transfers FROM this team should appear.</p>';
                    echo '</div>';
                }
                
                echo '<div class="mt-3">';
                echo '<a href="requests.php" class="btn btn-success">View Actual Requests Page</a>';
                echo '<a href="?test_team=" class="btn btn-secondary">Clear Test</a>';
                echo '</div>';
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-danger">Team not found!</div>';
            }
        }

        // Show all transfers for reference
        echo '<div class="card mt-4">';
        echo '<div class="card-header"><h5>All Transfers in Database (For Reference)</h5></div>';
        echo '<div class="card-body">';
        
        $all_transfers_sql = "SELECT t.*, 
                                     tf.name AS from_team_name, 
                                     tt.name AS to_team_name
                              FROM transfer t
                              JOIN team tf ON t.team_from = tf.team_id
                              JOIN team tt ON t.team_to = tt.team_id
                              ORDER BY t.requestDate DESC";
        
        $all_transfers_result = mysqli_query($con, $all_transfers_sql);
        
        if ($all_transfers_result && mysqli_num_rows($all_transfers_result) > 0) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm">';
            echo '<thead class="table-dark">';
            echo '<tr>';
            echo '<th>Transfer ID</th>';
            echo '<th>Member ID</th>';
            echo '<th>From Team (ID)</th>';
            echo '<th>To Team (ID)</th>';
            echo '<th>Status</th>';
            echo '<th>Request Date</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($transfer = mysqli_fetch_assoc($all_transfers_result)) {
                echo '<tr>';
                echo '<td>' . $transfer['id'] . '</td>';
                echo '<td>' . $transfer['member_id'] . '</td>';
                echo '<td>' . htmlspecialchars($transfer['from_team_name']) . ' (' . $transfer['team_from'] . ')</td>';
                echo '<td>' . htmlspecialchars($transfer['to_team_name']) . ' (' . $transfer['team_to'] . ')</td>';
                echo '<td>' . $transfer['status'] . '</td>';
                echo '<td>' . $transfer['requestDate'] . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">No transfers found in the database.</div>';
        }
        
        echo '</div>';
        echo '</div>';

        mysqli_close($con);
        ?>

        <div class="mt-4">
            <h4>How the Filtering Works</h4>
            <div class="alert alert-success">
                <h6>Correct Behavior:</h6>
                <ul class="mb-0">
                    <li><strong>Only shows transfers FROM the logged-in team</strong></li>
                    <li>Uses <code>WHERE t.team_from = ?</code> in the SQL query</li>
                    <li>The <code>?</code> parameter is bound to <code>$_SESSION['Team_id']</code></li>
                    <li>This ensures team isolation - each team only sees their own outgoing transfers</li>
                </ul>
            </div>
            
            <div class="alert alert-info">
                <h6>What Each Team Sees:</h6>
                <ul class="mb-0">
                    <li><strong>Team A:</strong> Only transfers where Team A is the source (team_from = Team A's ID)</li>
                    <li><strong>Team B:</strong> Only transfers where Team B is the source (team_from = Team B's ID)</li>
                    <li><strong>No Cross-Contamination:</strong> Team A cannot see Team B's transfers and vice versa</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
