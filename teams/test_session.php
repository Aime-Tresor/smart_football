<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Session and Database Test</h2>
        
        <?php
        session_start();
        
        echo '<div class="card mb-4">';
        echo '<div class="card-header"><h5>Session Information</h5></div>';
        echo '<div class="card-body">';
        
        if (isset($_SESSION['Team_id'])) {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Session Active</strong><br>';
            echo 'Team ID: ' . $_SESSION['Team_id'] . '<br>';
            echo 'Session ID: ' . session_id();
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ No Team Session</strong><br>';
            echo 'You need to log in as a team first.';
            echo '</div>';
            
            // Set a test session for debugging
            $_SESSION['Team_id'] = 4;
            echo '<div class="alert alert-info">';
            echo '<strong>Setting test session: Team ID = 4</strong>';
            echo '</div>';
        }
        
        echo '<h6>All Session Variables:</h6>';
        echo '<pre>' . print_r($_SESSION, true) . '</pre>';
        echo '</div>';
        echo '</div>';

        // Test database connection
        echo '<div class="card mb-4">';
        echo '<div class="card-header"><h5>Database Connection Test</h5></div>';
        echo '<div class="card-body">';
        
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            echo '<div class="alert alert-danger">';
            echo '<strong>❌ Database Connection Failed:</strong> ' . mysqli_connect_error();
            echo '</div>';
        } else {
            echo '<div class="alert alert-success">';
            echo '<strong>✅ Database Connected Successfully</strong>';
            echo '</div>';
            
            // Test basic queries
            $tables = ['team', 'team_members', 'transfer'];
            foreach ($tables as $table) {
                $result = mysqli_query($con, "SELECT COUNT(*) as count FROM $table");
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    echo '<p><strong>' . $table . ':</strong> ' . $row['count'] . ' records</p>';
                } else {
                    echo '<p><strong>' . $table . ':</strong> <span class="text-danger">Error - ' . mysqli_error($con) . '</span></p>';
                }
            }
            
            mysqli_close($con);
        }
        
        echo '</div>';
        echo '</div>';

        // Quick login form for testing
        if (isset($_POST['login_team'])) {
            $_SESSION['Team_id'] = (int)$_POST['team_id'];
            echo '<div class="alert alert-success">Session set to Team ID: ' . $_SESSION['Team_id'] . '</div>';
            echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
        }
        ?>

        <div class="card">
            <div class="card-header"><h5>Quick Team Login (for testing)</h5></div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Select Team ID:</label>
                        <select name="team_id" class="form-select" required>
                            <option value="">Choose a team...</option>
                            <option value="4">Team 4 (Kiyovu fc)</option>
                            <option value="6">Team 6 (Police fc)</option>
                            <option value="9">Team 9 (Marine fc)</option>
                        </select>
                    </div>
                    <button type="submit" name="login_team" class="btn btn-primary">Set Session</button>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <a href="requests.php" class="btn btn-success">Go to Requests Page</a>
            <a href="debug_transfers.php" class="btn btn-info">Debug Transfers</a>
            <a href="../login.php" class="btn btn-secondary">Proper Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
