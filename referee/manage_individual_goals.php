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

$referee_id = $_SESSION['referee_id'];

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        addGoal($conn, $referee_id);
        break;
    case 'edit':
        editGoal($conn, $referee_id);
        break;
    case 'delete':
        deleteGoal($conn, $referee_id);
        break;
    case 'get_goals':
        getGoals($conn, $referee_id);
        break;
    case 'get_players':
        getPlayers($conn, $referee_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function addGoal($conn, $referee_id) {
    $match_id = intval($_POST['match_id']);
    $team_id = intval($_POST['team_id']);
    $player_id = !empty($_POST['player_id']) ? intval($_POST['player_id']) : null;
    $goal_minute = trim($_POST['goal_minute'] ?? '');
    $goal_type = $_POST['goal_type'] ?? 'regular';
    $description = trim($_POST['description'] ?? '');

    // Verify referee is assigned to this match
    if (!verifyRefereeAccess($conn, $referee_id, $match_id)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        return;
    }

    // Validate inputs
    if (empty($goal_minute) || !preg_match('/^\d{1,3}\'?$/', $goal_minute)) {
        echo json_encode(['success' => false, 'message' => 'Invalid goal minute format']);
        return;
    }

    $conn->autocommit(false);
    
    try {
        $sql = "INSERT INTO individual_goals (match_id, team_id, player_id, goal_minute, goal_type, description, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssi", $match_id, $team_id, $player_id, $goal_minute, $goal_type, $description, $referee_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add goal");
        }

        $goal_id = $conn->insert_id;
        $conn->commit();

        // Get goal details for response
        $goal_details = getGoalDetails($conn, $goal_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Goal added successfully',
            'goal' => $goal_details
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function editGoal($conn, $referee_id) {
    $goal_id = intval($_POST['goal_id']);
    $player_id = !empty($_POST['player_id']) ? intval($_POST['player_id']) : null;
    $goal_minute = trim($_POST['goal_minute']);
    $goal_type = $_POST['goal_type'] ?? 'regular';
    $description = trim($_POST['description'] ?? '');

    // Verify goal exists and referee has access
    $verify_sql = "SELECT ig.match_id FROM individual_goals ig 
                   JOIN weekly_fixtures wf ON ig.match_id = wf.match_id 
                   WHERE ig.goal_id = ? AND (wf.referee = ? OR wf.assistant1 = ? OR wf.assistant2 = ? OR wf.official = ?)";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("iiiii", $goal_id, $referee_id, $referee_id, $referee_id, $referee_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        return;
    }

    // Validate inputs
    if (empty($goal_minute) || !preg_match('/^\d{1,3}\'?$/', $goal_minute)) {
        echo json_encode(['success' => false, 'message' => 'Invalid goal minute format']);
        return;
    }

    $conn->autocommit(false);
    
    try {
        $sql = "UPDATE individual_goals SET player_id = ?, goal_minute = ?, goal_type = ?, description = ? WHERE goal_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $player_id, $goal_minute, $goal_type, $description, $goal_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update goal");
        }

        $conn->commit();

        // Get updated goal details
        $goal_details = getGoalDetails($conn, $goal_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Goal updated successfully',
            'goal' => $goal_details
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function deleteGoal($conn, $referee_id) {
    $goal_id = intval($_POST['goal_id']);

    // Verify goal exists and referee has access
    $verify_sql = "SELECT ig.match_id FROM individual_goals ig 
                   JOIN weekly_fixtures wf ON ig.match_id = wf.match_id 
                   WHERE ig.goal_id = ? AND (wf.referee = ? OR wf.assistant1 = ? OR wf.assistant2 = ? OR wf.official = ?)";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("iiiii", $goal_id, $referee_id, $referee_id, $referee_id, $referee_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        return;
    }

    $conn->autocommit(false);
    
    try {
        $sql = "DELETE FROM individual_goals WHERE goal_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $goal_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete goal");
        }

        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Goal deleted successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function getGoals($conn, $referee_id) {
    $match_id = intval($_GET['match_id']);

    // Verify referee access
    if (!verifyRefereeAccess($conn, $referee_id, $match_id)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        return;
    }

    $sql = "SELECT ig.*, tm.fname, tm.lname, tm.number, t.name as team_name
            FROM individual_goals ig
            LEFT JOIN team_members tm ON ig.player_id = tm.member_id
            JOIN team t ON ig.team_id = t.team_id
            WHERE ig.match_id = ?
            ORDER BY ig.goal_minute ASC, ig.created_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $goals = [];
    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }

    echo json_encode(['success' => true, 'goals' => $goals]);
}

function getPlayers($conn, $referee_id) {
    $team_id = intval($_GET['team_id']);

    $sql = "SELECT member_id, fname, lname, number, position 
            FROM team_members 
            WHERE team = (SELECT name FROM team WHERE team_id = ?) AND role_in_team = 'player'
            ORDER BY number ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }

    echo json_encode(['success' => true, 'players' => $players]);
}

function verifyRefereeAccess($conn, $referee_id, $match_id) {
    $sql = "SELECT 1 FROM weekly_fixtures WHERE match_id = ? AND (referee = ? OR assistant1 = ? OR assistant2 = ? OR official = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $match_id, $referee_id, $referee_id, $referee_id, $referee_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getGoalDetails($conn, $goal_id) {
    $sql = "SELECT ig.*, tm.fname, tm.lname, tm.number, t.name as team_name
            FROM individual_goals ig
            LEFT JOIN team_members tm ON ig.player_id = tm.member_id
            JOIN team t ON ig.team_id = t.team_id
            WHERE ig.goal_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $goal_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$conn->close();
?>
