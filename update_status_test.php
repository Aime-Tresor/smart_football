<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to DB
    $con = mysqli_connect("localhost", "root", "", "fa_db");
    if (!$con) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    $transfer_id = (int)$_POST['transfer_id'];
    $new_status = (int)$_POST['new_status'];
    $today = date('Y-m-d');

    // Status mapping for reference
    $statusNames = [
        0 => 'Pending',
        1 => 'Requested', 
        2 => 'Rejected',
        3 => 'Completed'
    ];

    // Validate inputs
    if ($transfer_id <= 0 || !array_key_exists($new_status, $statusNames)) {
        $_SESSION['error'] = "Invalid transfer ID or status value.";
        header("Location: test_status_consistency.php");
        exit;
    }

    // Reset all optional dates first
    $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";

    // Set the correct date field based on status
    if ($new_status === 0) { 
        // Pending - clear all dates except request date
        $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";
    } elseif ($new_status === 1) { 
        // Requested - keep requestDate as is, clear others
        $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";
    } elseif ($new_status === 2) { 
        // Rejected - set reject date
        $datesUpdate = "rejectDate = '$today', aprovalDate = NULL, completeDate = NULL";
    } elseif ($new_status === 3) { 
        // Completed - set complete date
        $datesUpdate = "completeDate = '$today', aprovalDate = NULL, rejectDate = NULL";
    }

    // Update the transfer
    $update = "UPDATE transfer SET status = $new_status, $datesUpdate WHERE id = $transfer_id";

    if (mysqli_query($con, $update)) {
        $_SESSION['success'] = "Transfer ID $transfer_id updated successfully. Status changed to: " . $statusNames[$new_status];
    } else {
        $_SESSION['error'] = "Error updating transfer: " . mysqli_error($con);
    }

    mysqli_close($con);
}

// Redirect back to test page
header("Location: test_status_consistency.php");
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Update Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-info">
            <h4>Processing Status Update...</h4>
            <p>You should be redirected automatically. If not, <a href="test_status_consistency.php">click here</a>.</p>
        </div>
    </div>
</body>
</html>
