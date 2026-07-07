<?php
session_start();

// Quick session setup for testing transfers
if (isset($_GET['team'])) {
    $team_id = (int)$_GET['team'];
    $_SESSION['Team_id'] = $team_id;
    
    // Connect to database to get team name
    $con = mysqli_connect("localhost", "root", "", "fa_db");
    if ($con) {
        $query = "SELECT name FROM team WHERE team_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $team = $result->fetch_assoc();
        
        if ($team) {
            $_SESSION['Team_name'] = $team['name'];
            $message = "Session set for Team: " . $team['name'] . " (ID: $team_id)";
            $success = true;
        } else {
            $message = "Team with ID $team_id not found";
            $success = false;
        }
        mysqli_close($con);
    } else {
        $message = "Database connection failed";
        $success = false;
    }
} else {
    $message = "No team selected";
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Team Session Setup</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?= $success ? 'success' : 'danger' ?>">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <h5>Current Session Status:</h5>
                        <div class="alert alert-info">
                            <strong>Team ID:</strong> <?= $_SESSION['Team_id'] ?? 'Not set' ?><br>
                            <strong>Team Name:</strong> <?= $_SESSION['Team_name'] ?? 'Not set' ?><br>
                            <strong>Session ID:</strong> <?= session_id() ?>
                        </div>

                        <h5>Available Teams:</h5>
                        <div class="row">
                            <?php
                            $con = mysqli_connect("localhost", "root", "", "fa_db");
                            if ($con) {
                                $teams_query = "SELECT team_id, name FROM team ORDER BY team_id";
                                $teams_result = mysqli_query($con, $teams_query);
                                
                                if ($teams_result && mysqli_num_rows($teams_result) > 0) {
                                    while ($team = mysqli_fetch_assoc($teams_result)) {
                                        $active = (isset($_SESSION['Team_id']) && $_SESSION['Team_id'] == $team['team_id']) ? 'btn-success' : 'btn-outline-primary';
                                        echo '<div class="col-md-4 mb-2">';
                                        echo '<a href="?team=' . $team['team_id'] . '" class="btn ' . $active . ' w-100">';
                                        echo 'Team ' . $team['team_id'] . '<br><small>' . htmlspecialchars($team['name']) . '</small>';
                                        echo '</a>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="col-12"><div class="alert alert-warning">No teams found in database</div></div>';
                                }
                                mysqli_close($con);
                            } else {
                                echo '<div class="col-12"><div class="alert alert-danger">Database connection failed</div></div>';
                            }
                            ?>
                        </div>

                        <div class="mt-4">
                            <h5>Quick Actions:</h5>
                            <div class="btn-group" role="group">
                                <a href="requests.php" class="btn btn-primary">View Transfer Requests</a>
                                <a href="debug_transfers.php" class="btn btn-info">Debug Transfers</a>
                                <a href="../login.php" class="btn btn-secondary">Proper Login</a>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['Team_id'])): ?>
                            <div class="mt-4">
                                <div class="alert alert-success">
                                    <h6>✅ Session Ready!</h6>
                                    <p>You can now access the transfer requests page.</p>
                                    <a href="requests.php" class="btn btn-success">Go to Transfer Requests</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <h6>Instructions:</h6>
                            <ol>
                                <li>Click on a team above to set your session</li>
                                <li>Click "View Transfer Requests" to see transfers for that team</li>
                                <li>Team 4 (Kiyovu fc) has sample transfer data</li>
                                <li>Other teams may show "No transfer requests"</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
