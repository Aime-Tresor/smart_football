<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\ServiceFactory;

session_start();
require '../../app/database.php';

if (!isset($_SESSION['fa_user']) || empty($_SESSION['fa_user'])) {
    header('Location: ../../login.php');
    exit;
}

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
            // Finishing a match is routed through MatchCompletionService so it
            // gets the same authorization, audit log, locking and player-stat
            // recalculation as the referee "Finish Match" button.
            $actorIdStmt = $connection->prepare('SELECT id FROM fa_user WHERE username = ?');
            $actorIdStmt->execute([$_SESSION['fa_user']]);

            $result = ServiceFactory::matchCompletionService()->finish($match_id, [
                'type' => 'fa_user',
                'id' => (int) ($actorIdStmt->fetchColumn() ?: 0),
            ], [
                'team1_goal' => $team1_score,
                'team2_goal' => $team2_score,
                'confirm_zero_scores' => true,
            ]);

            if (!$result->success) {
                throw new Exception($result->error);
            }

            $finalTeam1 = $result->match['team1_goal'];
            $finalTeam2 = $result->match['team2_goal'];
            $message = "Match status updated to completed with final score: {$finalTeam1} - {$finalTeam2}";
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
