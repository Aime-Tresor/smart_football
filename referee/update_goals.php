<?php
session_start();

// Check if referee is logged in
if (!isset($_SESSION['referee_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "fa_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (!isset($_POST['match_id']) || !isset($_POST['team1_goals']) || !isset($_POST['team2_goals'])) {
        $_SESSION['error_message'] = 'All fields are required.';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }

    $match_id = intval($_POST['match_id']);
    $team1_goals = intval($_POST['team1_goals']);
    $team2_goals = intval($_POST['team2_goals']);
    $referee_id = $_SESSION['referee_id'];

    // Validate goal values (must be non-negative)
    if ($team1_goals < 0 || $team2_goals < 0) {
        $_SESSION['error_message'] = 'Goals cannot be negative.';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }

    // Verify that the referee is assigned to this match
    $verify_sql = "
        SELECT m.id, t1.name AS team1_name, t2.name AS team2_name
        FROM `match` m
        JOIN `team` t1 ON m.team1_id = t1.team_id
        JOIN `team` t2 ON m.team2_id = t2.team_id
        JOIN `weekly_fixtures` wf ON m.id = wf.match_id
        WHERE m.id = ? AND (wf.referee = ? OR wf.assistant1 = ? OR wf.assistant2 = ? OR wf.official = ?)
    ";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("iiiii", $match_id, $referee_id, $referee_id, $referee_id, $referee_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        $_SESSION['error_message'] = 'You are not authorized to update goals for this match.';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }

    $match_info = $verify_result->fetch_assoc();

    // Begin transaction
    $conn->autocommit(false);

    try {
        // Update match goals
        $update_sql = "UPDATE `match` SET team1_goal = ?, team2_goal = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $team1_goals, $team2_goals, $match_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update match goals");
        }

        // Commit transaction
        $conn->commit();

        // Set success message
        $_SESSION['success_message'] = "Goals updated successfully: {$match_info['team1_name']} {$team1_goals} - {$team2_goals} {$match_info['team2_name']}";

        // Handle AJAX requests
        if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
            echo json_encode([
                'success' => true,
                'message' => $_SESSION['success_message'],
                'team1_goals' => $team1_goals,
                'team2_goals' => $team2_goals
            ]);
            unset($_SESSION['success_message']);
            exit();
        }

        // Redirect back to referring page
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        $_SESSION['error_message'] = 'Error updating goals: ' . $e->getMessage();

        // Handle AJAX requests
        if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
            echo json_encode([
                'success' => false,
                'message' => $_SESSION['error_message']
            ]);
            unset($_SESSION['error_message']);
            exit();
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit();
    }

} else {
    // If not POST request, redirect to dashboard
    header("Location: index.php");
    exit();
}

$conn->close();
?>
