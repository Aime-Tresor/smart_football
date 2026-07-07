<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Status Consistency</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Transfer Status Consistency Test</h2>

        <?php
        session_start();

        // Display success/error messages
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($_SESSION['success']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($_SESSION['error']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <?php
        // Connect to DB
        $con = mysqli_connect("localhost", "root", "", "fa_db");
        if (!$con) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        // Status mapping (should be consistent across all files)
        $statusLabels = [
            0 => 'Pending',
            1 => 'Requested',
            2 => 'Rejected',
            3 => 'Completed',
        ];

        echo '<div class="alert alert-info">';
        echo '<h5>Status Mapping Reference:</h5>';
        echo '<ul>';
        foreach ($statusLabels as $value => $label) {
            echo "<li><strong>$value</strong> = $label</li>";
        }
        echo '</ul>';
        echo '</div>';

        // Get all transfers to test the consistency
        $sql = "SELECT * FROM transfer ORDER BY id DESC LIMIT 10";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            echo '<h4>Current Transfer Data:</h4>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered">';
            echo '<thead class="table-light">';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Raw Status Value</th>';
            echo '<th>Displayed Status</th>';
            echo '<th>Request Date</th>';
            echo '<th>Approval Date</th>';
            echo '<th>Reject Date</th>';
            echo '<th>Complete Date</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($transfer = mysqli_fetch_assoc($result)) {
                $statusValue = $transfer['status'];
                $statusText = $statusLabels[$statusValue] ?? 'Unknown';
                $badgeClass = $statusValue == 0 ? 'secondary' : ($statusValue == 1 ? 'primary' : ($statusValue == 2 ? 'danger' : 'success'));

                echo '<tr>';
                echo '<td>' . $transfer['id'] . '</td>';
                echo '<td><code>' . $statusValue . '</code></td>';
                echo '<td><span class="badge bg-' . $badgeClass . '">' . $statusText . '</span></td>';
                echo '<td>' . ($transfer['requestDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>' . ($transfer['aprovalDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>' . ($transfer['rejectDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>' . ($transfer['completeDate'] ?? '<em>null</em>') . '</td>';
                echo '<td>';
                echo '<a href="fa_user/transfer_edit.php?id=' . $transfer['id'] . '" class="btn btn-sm btn-primary">Edit</a>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">No transfers found in the database.</div>';
        }

        mysqli_close($con);
        ?>

        <div class="mt-4">
            <h4>Test Instructions:</h4>
            <div class="alert alert-success">
                <ol>
                    <li><strong>Check Current Status:</strong> Verify that the "Displayed Status" column shows the correct text for each raw status value</li>
                    <li><strong>Test Updates:</strong> Click "Edit" on any transfer and change the status</li>
                    <li><strong>Verify Changes:</strong> After updating, refresh this page to see if the status changed correctly</li>
                    <li><strong>Expected Behavior:</strong> The displayed status should match exactly what you selected in the dropdown</li>
                </ol>
            </div>
        </div>

        <div class="mt-3">
            <h4>Quick Status Update Test:</h4>
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" action="update_status_test.php" class="border p-3 rounded">
                        <h6>Update Transfer Status</h6>
                        <div class="mb-3">
                            <label class="form-label">Select Transfer:</label>
                            <select name="transfer_id" class="form-select" required>
                                <option value="">Choose a transfer...</option>
                                <?php
                                // Reconnect for the form
                                $con = mysqli_connect("localhost", "root", "", "fa_db");
                                $result = mysqli_query($con, "SELECT id, member_id FROM transfer ORDER BY id DESC LIMIT 10");
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<option value="' . $row['id'] . '">Transfer ID: ' . $row['id'] . ' (Member: ' . $row['member_id'] . ')</option>';
                                }
                                mysqli_close($con);
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Status:</label>
                            <select name="new_status" class="form-select" required>
                                <option value="">Choose status...</option>
                                <option value="0">Pending</option>
                                <option value="1">Requested</option>
                                <option value="2">Rejected</option>
                                <option value="3">Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Update Status</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6>What This Tests:</h6>
                        <ul class="mb-0">
                            <li>Status value is correctly saved to database</li>
                            <li>Appropriate date field is updated</li>
                            <li>Display shows the selected status</li>
                            <li>No inconsistencies between files</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="fa_user/transfer.php" class="btn btn-primary">View Full Transfer Page</a>
            <button onclick="location.reload()" class="btn btn-secondary">Refresh Test Data</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
