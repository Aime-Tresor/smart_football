<?php
session_start();
require '../../app/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $match_id = (int)$_POST['match_id'];
        $new_status = $_POST['new_status'];
        $team1_score = isset($_POST['team1_score']) ? (int)$_POST['team1_score'] : null;
        $team2_score = isset($_POST['team2_score']) ? (int)$_POST['team2_score'] : null;

        // Validate status
        $valid_statuses = ['upcoming', 'live', 'completed'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception('Invalid status provided.');
        }

        // Validate match exists
        $checkSql = "SELECT id, status, team1_goal, team2_goal FROM `match` WHERE id = ?";
        $checkStmt = $connection->prepare($checkSql);
        $checkStmt->execute([$match_id]);
        $match = $checkStmt->fetch(PDO::FETCH_OBJ);

        if (!$match) {
            throw new Exception('Match not found.');
        }

        // Prepare update query based on status
        if ($new_status === 'completed') {
            // For completed matches, require scores
            if ($team1_score === null || $team2_score === null) {
                throw new Exception('Scores are required for completed matches.');
            }
            
            $updateSql = "UPDATE `match` SET status = ?, team1_goal = ?, team2_goal = ? WHERE id = ?";
            $updateStmt = $connection->prepare($updateSql);
            $updateStmt->execute([$new_status, $team1_score, $team2_score, $match_id]);
            
            $message = "Match status updated to completed with final score: {$team1_score} - {$team2_score}";
        } else {
            // For upcoming/live matches, just update status
            if ($new_status === 'upcoming') {
                // Reset scores for upcoming matches
                $updateSql = "UPDATE `match` SET status = ?, team1_goal = NULL, team2_goal = NULL WHERE id = ?";
                $updateStmt = $connection->prepare($updateSql);
                $updateStmt->execute([$new_status, $match_id]);
            } else {
                // For live matches, keep existing scores or set to 0 if null
                $updateSql = "UPDATE `match` SET status = ?, team1_goal = COALESCE(team1_goal, 0), team2_goal = COALESCE(team2_goal, 0) WHERE id = ?";
                $updateStmt = $connection->prepare($updateSql);
                $updateStmt->execute([$new_status, $match_id]);
            }
            
            $message = "Match status updated to " . ucfirst($new_status);
        }

        $_SESSION['success'] = $message;
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating match status: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

// Redirect back to fixture page
header("Location: ../fixture.php");
exit;
?>
