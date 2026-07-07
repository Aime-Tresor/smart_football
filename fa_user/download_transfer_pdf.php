<?php
// download_transfer_pdf.php - Generate and download transfer table as PDF
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if we want to force download or display
$forceDownload = isset($_GET['download']) ? $_GET['download'] : 'yes';

if ($forceDownload === 'yes') {
    // Set headers for download
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="Transfer_Report_' . date('Y-m-d') . '.html"');
} else {
    // Just display in browser for testing
    header('Content-Type: text/html; charset=UTF-8');
}

// Get transfer data with optional status filter
$filter_status = isset($_GET['status']) ? (int)$_GET['status'] : null;

if ($filter_status !== null) {
    $sql = "SELECT * FROM transfer WHERE status = $filter_status ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM transfer ORDER BY id DESC";
}

$result = mysqli_query($con, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transfer Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            color: #666;
            margin: 5px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #ddd;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-size: 9px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .player-info {
            font-weight: bold;
        }
        .status-pending { color: #ffc107; }
        .status-requested { color: #17a2b8; }
        .status-rejected { color: #dc3545; }
        .status-completed { color: #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Smart Football - Transfer Report</h1>
        <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        <?php if ($filter_status !== null): ?>
            <p>Filtered by Status: <?= $filter_status ?></p>
        <?php endif; ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Player / Staff</th>
                <th>Post</th>
                <th>Position / Role</th>
                <th>Team From</th>
                <th>Team To</th>
                <th>Status</th>
                <th>Completed On</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) == 0) {
                echo "<tr><td colspan='7' class='text-center'>No transfers found.</td></tr>";
            } else {
                while ($transfer = mysqli_fetch_assoc($result)) {
                    // Member details
                    $member_id = (int)$transfer['member_id'];
                    $memberResult = mysqli_query($con, "SELECT * FROM team_members WHERE member_id = $member_id");
                    if (!$memberResult || mysqli_num_rows($memberResult) == 0) {
                        continue;
                    }
                    $member = mysqli_fetch_assoc($memberResult);

                    $name = $member['fname'] . ' ' . $member['lname'];
                    $number = !empty($member['number']) ? $member['number'] : strtoupper($transfer['post'][0]);
                    $position_or_role = !empty($member['position']) ? $member['position'] : $member['role_in_team'];
                    $post = $transfer['post']; // player or staff

                    if ($post === 'staff') {
                        switch (strtolower($member['role_in_team'])) {
                            case 'hc': $staffTitle = 'Head Coach'; break;
                            case 'ac': $staffTitle = 'Assistant Coach'; break;
                            case 'gc': $staffTitle = 'Goalkeeper Coach'; break;
                            case 'do': $staffTitle = 'Doctor'; break;
                            case 'ph': $staffTitle = 'Physiotherapist'; break;
                            default: $staffTitle = 'Staff';
                        }
                    } else {
                        $staffTitle = '';
                    }

                    // Get team names
                    $team_from_id = (int)$transfer['team_from'];
                    $team_to_id = (int)$transfer['team_to'];

                    $teamFromResult = mysqli_query($con, "SELECT name FROM team WHERE team_id = $team_from_id");
                    $teamFrom = ($teamFromResult && mysqli_num_rows($teamFromResult) > 0)
                        ? mysqli_fetch_assoc($teamFromResult)['name'] : '';

                    $teamToResult = mysqli_query($con, "SELECT name FROM team WHERE team_id = $team_to_id");
                    $teamTo = ($teamToResult && mysqli_num_rows($teamToResult) > 0)
                        ? mysqli_fetch_assoc($teamToResult)['name'] : '';

                    // Status text and class
                    $statusLabels = [
                        0 => 'Pending',
                        1 => 'Requested',
                        2 => 'Rejected',
                        3 => 'Completed',
                    ];
                    $statusText = $statusLabels[$transfer['status']] ?? 'Unknown';
                    $statusClass = 'status-' . strtolower($statusText);

                    echo '<tr>';
                    echo '<td class="player-info">#' . htmlspecialchars($number) . ' ' . htmlspecialchars($name) . '</td>';
                    echo '<td class="text-center">' . htmlspecialchars(ucfirst($post)) . '</td>';
                    echo '<td>' . htmlspecialchars($post === 'player' ? $position_or_role : $staffTitle) . '</td>';
                    echo '<td>' . htmlspecialchars($teamFrom) . '</td>';
                    echo '<td>' . htmlspecialchars($teamTo) . '</td>';
                    echo '<td class="text-center ' . $statusClass . '">' . htmlspecialchars($statusText) . '</td>';
                    echo '<td class="text-center">' . htmlspecialchars($transfer['completeDate'] ?? '-') . '</td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
mysqli_close($con);
?>
