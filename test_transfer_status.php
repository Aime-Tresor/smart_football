<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Transfer Status Logic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Transfer Status Logic Test</h2>
        
        <?php
        // Connect to DB
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        // Get all transfers to test the logic
        $sql = "SELECT * FROM transfer ORDER BY id DESC LIMIT 10";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered">';
            echo '<thead class="table-light">';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Status</th>';
            echo '<th>Request Date</th>';
            echo '<th>Approval Date</th>';
            echo '<th>Reject Date</th>';
            echo '<th>Complete Date</th>';
            echo '<th>Most Recent Date</th>';
            echo '<th>Most Recent Status</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($transfer = mysqli_fetch_assoc($result)) {
                // Apply the same logic as in transfer.php
                $statusLabels = [
                    0 => 'Pending',
                    1 => 'Requested',
                    2 => 'Rejected',
                    3 => 'Completed',
                ];
                $statusText = $statusLabels[$transfer['status']] ?? 'Unknown';

                // Determine the most recent status update date
                $statusDates = [];
                if (!empty($transfer['requestDate'])) {
                    $statusDates['requestDate'] = $transfer['requestDate'];
                }
                if (!empty($transfer['aprovalDate'])) {
                    $statusDates['aprovalDate'] = $transfer['aprovalDate'];
                }
                if (!empty($transfer['rejectDate'])) {
                    $statusDates['rejectDate'] = $transfer['rejectDate'];
                }
                if (!empty($transfer['completeDate'])) {
                    $statusDates['completeDate'] = $transfer['completeDate'];
                }

                // Find the most recent date
                $mostRecentDate = '';
                $mostRecentStatus = '';
                if (!empty($statusDates)) {
                    $latestDate = max($statusDates);
                    $mostRecentDate = $latestDate;
                    
                    // Determine which status corresponds to the latest date
                    foreach ($statusDates as $dateType => $date) {
                        if ($date === $latestDate) {
                            switch ($dateType) {
                                case 'requestDate':
                                    $mostRecentStatus = 'Requested';
                                    break;
                                case 'aprovalDate':
                                    $mostRecentStatus = 'Approved';
                                    break;
                                case 'rejectDate':
                                    $mostRecentStatus = 'Rejected';
                                    break;
                                case 'completeDate':
                                    $mostRecentStatus = 'Completed';
                                    break;
                            }
                            break;
                        }
                    }
                }

                echo '<tr>';
                echo '<td>' . $transfer['id'] . '</td>';
                echo '<td><span class="badge bg-' . ($transfer['status'] == 0 ? 'secondary' : ($transfer['status'] == 1 ? 'primary' : ($transfer['status'] == 2 ? 'danger' : 'success'))) . '">' . $statusText . '</span></td>';
                echo '<td>' . ($transfer['requestDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>' . ($transfer['aprovalDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>' . ($transfer['rejectDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>' . ($transfer['completeDate'] ?? '<em>null</em>') . '</td>';
                echo '<td><strong>' . ($mostRecentDate ?: '<em>No dates</em>') . '</strong></td>';
                echo '<td><span class="badge bg-info">' . ($mostRecentStatus ?: 'None') . '</span></td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">No transfers found in the database.</div>';
        }

        mysqli_close($con);
        ?>

        <div class="mt-4">
            <h4>Logic Explanation</h4>
            <div class="alert alert-info">
                <p><strong>How it works:</strong></p>
                <ul>
                    <li>The system checks all date fields: requestDate, aprovalDate, rejectDate, completeDate</li>
                    <li>It finds the most recent (latest) date among all non-null dates</li>
                    <li>It then determines which status action corresponds to that latest date</li>
                    <li>The "Last Updated" column shows this most recent date and the corresponding action</li>
                </ul>
                <p><strong>Status Badge Colors:</strong></p>
                <ul>
                    <li><span class="badge bg-secondary">Pending (0)</span> - Gray</li>
                    <li><span class="badge bg-primary">Requested (1)</span> - Blue</li>
                    <li><span class="badge bg-danger">Rejected (2)</span> - Red</li>
                    <li><span class="badge bg-success">Completed (3)</span> - Green</li>
                </ul>
            </div>
        </div>

        <div class="mt-3">
            <a href="fa_user/transfer.php" class="btn btn-primary">View Full Transfer Page</a>
            <a href="fa_user/transfer_edit.php" class="btn btn-secondary">Test Status Update</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
