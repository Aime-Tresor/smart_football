<?php
session_start();
require_once 'header.php';

$con = mysqli_connect("localhost", "root", "", "fa_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// On form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $id = (int)$_POST['id'];
    $status = (int)$_POST['status'];
    $today = date('Y-m-d');

    // Reset all optional dates first
    $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";

    // Set the correct date field based on status
    // Status mapping: 0=Pending, 1=Requested, 2=Rejected, 3=Completed
    if ($status === 0) {
        // Pending - clear all dates except request date
        $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";
    } elseif ($status === 1) {
        // Requested - keep requestDate as is, clear others
        $datesUpdate = "aprovalDate = NULL, rejectDate = NULL, completeDate = NULL";
    } elseif ($status === 2) {
        // Rejected - set reject date
        $datesUpdate = "rejectDate = '$today', aprovalDate = NULL, completeDate = NULL";
    } elseif ($status === 3) {
        // Completed - set complete date
        $datesUpdate = "completeDate = '$today', aprovalDate = NULL, rejectDate = NULL";
    }

    $update = "UPDATE transfer SET status = $status, $datesUpdate WHERE id = $id";

    if (mysqli_query($con, $update)) {
        $statusNames = [0 => 'Pending', 1 => 'Requested', 2 => 'Rejected', 3 => 'Completed'];
        $_SESSION['success'] = "Transfer updated successfully. Status changed to: " . $statusNames[$status];
    } else {
        $_SESSION['error'] = "Error updating transfer: " . mysqli_error($con);
    }

    header("Location: transfer.php");
    exit;
}

// On GET (load existing transfer)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid transfer ID.";
    header("Location: transfer.php");
    exit;
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM transfer WHERE id = $id";
$result = mysqli_query($con, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Transfer record not found.";
    header("Location: transfer.php");
    exit;
}
$transfer = mysqli_fetch_assoc($result);
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="card-title mb-4 text-primary">
                    <i class="fa fa-random me-2"></i> Approve Transfer Player/Staff
                </h4>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="controls/updateTransfer.php" class="needs-validation" novalidate>
                    <input type="hidden" name="id" value="<?= $transfer['id'] ?>">

                    <div class="row mb-3 d-none">
                        <div class="col-md-6">
                            <label class="form-label">Team From</label>
                            <input type="text" class="form-control" value="<?= $transfer['team_from'] ?>" disabled>
                        </div>

                        <div class="col-md-6 d-none">
                            <label class="form-label">Team To</label>
                            <input type="text" class="form-control" value="<?= $transfer['team_to'] ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-3 d-none">
                        <div class="col-md-6">
                            <label class="form-label">Post</label>
                            <input type="text" class="form-control" value="<?= $transfer['post'] ?>" disabled>
                        </div>

                        <div class="col-md-6 d-none ">
                            <label class="form-label">Request Date</label>
                            <input type="text" class="form-control" value="<?= $transfer['requestDate'] ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Transfer Status</label>
                            <select name="status" class="form-select" required>
                                <option value="0" <?= ($transfer['status'] == 0) ? 'selected' : '' ?>>Pending</option>
                                <option value="1" <?= ($transfer['status'] == 1) ? 'selected' : '' ?>>Requested</option>
                                <option value="2" <?= ($transfer['status'] == 2) ? 'selected' : '' ?>>Rejected</option>
                                <option value="3" <?= ($transfer['status'] == 3) ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group text-center">
                        <button type="submit" name="submit" class="btn btn-success px-5">
                            <i class="fa fa-check-circle me-1"></i> Update Transfer
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
