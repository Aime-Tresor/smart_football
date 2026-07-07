<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Simple Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Simple Transfer Requests Test</h2>
        
        <?php
        session_start();
        
        // Connect to database
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Test with a specific team ID
        $test_team_id = isset($_GET['team']) ? (int)$_GET['team'] : 1;
        $_SESSION['Team_id'] = $test_team_id;
        
        echo '<div class="alert alert-info">';
        echo '<h5>Testing Simple Requests Display</h5>';
        echo '<p>Testing with Team ID: ' . $test_team_id . '</p>';
        echo '<p>This shows exactly what the simplified requests.php page displays.</p>';
        echo '</div>';

        // Show team selection
        echo '<div class="mb-3">';
        echo '<label class="form-label">Test with different teams:</label><br>';
        for ($i = 1; $i <= 5; $i++) {
            $active = ($i == $test_team_id) ? 'btn-primary' : 'btn-outline-primary';
            echo '<a href="?team=' . $i . '" class="btn ' . $active . ' btn-sm me-2">Team ' . $i . '</a>';
        }
        echo '</div>';

        // Use the same logic as the simplified requests.php
        $status_labels = [
            0 => 'Pending',
            1 => 'Requested',
            2 => 'Rejected',
            3 => 'Completed'
        ];

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<h4 class="card-title">Transfer Requests</h4>';
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

        $sql = "SELECT t.*, 
                       tm.fname, tm.lname, tm.role_in_team,
                       tt.name AS to_team
                FROM transfer t
                JOIN team_members tm ON t.member_id = tm.member_id
                JOIN team tt ON t.team_to = tt.team_id
                WHERE t.team_from = ?
                ORDER BY t.requestDate DESC";

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $test_team_id);
        $stmt->execute();
        $result = $stmt->get_result();

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

        if ($count == 1) {
            echo '<tr>';
            echo '<td colspan="6" class="text-center text-muted">No transfer requests from your team.</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Show what data exists for reference
        echo '<div class="mt-4">';
        echo '<h5>Available Data (for reference)</h5>';
        
        // Show teams
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6>Teams:</h6>';
        $teams_result = mysqli_query($con, "SELECT * FROM team ORDER BY team_id");
        if ($teams_result && mysqli_num_rows($teams_result) > 0) {
            echo '<ul class="list-group">';
            while ($team = mysqli_fetch_assoc($teams_result)) {
                $active = ($team['team_id'] == $test_team_id) ? 'active' : '';
                echo '<li class="list-group-item ' . $active . '">';
                echo '<strong>ID ' . $team['team_id'] . ':</strong> ' . htmlspecialchars($team['name']);
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h6>All Transfers:</h6>';
        $all_transfers = mysqli_query($con, "SELECT t.*, tf.name as from_team, tt.name as to_team FROM transfer t JOIN team tf ON t.team_from = tf.team_id JOIN team tt ON t.team_to = tt.team_id ORDER BY t.requestDate DESC");
        if ($all_transfers && mysqli_num_rows($all_transfers) > 0) {
            echo '<ul class="list-group">';
            while ($transfer = mysqli_fetch_assoc($all_transfers)) {
                $highlight = ($transfer['team_from'] == $test_team_id) ? 'list-group-item-primary' : '';
                echo '<li class="list-group-item ' . $highlight . '">';
                echo '<small>';
                echo '<strong>ID ' . $transfer['id'] . ':</strong> ';
                echo 'From ' . htmlspecialchars($transfer['from_team']) . ' ';
                echo 'to ' . htmlspecialchars($transfer['to_team']) . ' ';
                echo '(Status: ' . $transfer['status'] . ')';
                echo '</small>';
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        echo '</div>';

        mysqli_close($con);
        ?>

        <div class="mt-4">
            <a href="requests.php" class="btn btn-success">View Actual Requests Page</a>
            <button onclick="location.reload()" class="btn btn-secondary">Refresh</button>
        </div>

        <div class="mt-3">
            <div class="alert alert-success">
                <h6>✅ Simplified Implementation Complete</h6>
                <p class="mb-0">The requests.php page now shows <strong>only</strong> the transfer requests for the specific team with no extra features:</p>
                <ul class="mb-0 mt-2">
                    <li>Simple table with essential columns</li>
                    <li>Filtered by team_from = logged-in team ID</li>
                    <li>No statistics, no extra information, no complex features</li>
                    <li>Just the data you requested</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
