<?php
session_start();

// DB connection
$conn = mysqli_connect("localhost", "root", "", "fa_db");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Check if required POST fields are provided
if (!isset($_POST['player_id'], $_POST['card'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required data']));
}

$player_id = (int)$_POST['player_id'];
$card = $_POST['card']; // either "yellow" or "red"
$match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : null;
$card_time = isset($_POST['card_time']) ? $_POST['card_time'] : null;

// Validate referee authorization (only referees can give cards)
if (!isset($_SESSION['referee_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized: Only referees can issue cards']));
}

// Fetch current card counts and player info
$sql = "SELECT member_id, fname, lname, yellow, double_yellow, red FROM team_members WHERE member_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $player_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    die(json_encode(['success' => false, 'message' => 'Player not found']));
}

$player = mysqli_fetch_assoc($result);
$current_yellow = $player['yellow'];
$current_double_yellow = $player['double_yellow'];
$current_red = $player['red'];

// Validate card logic
$updateSql = '';
$cardTypeToRecord = '';
$message = '';

if ($card === 'yellow') {
    $new_yellow_count = $current_yellow + 1;

    // Check if this is the second yellow card (automatic red)
    if ($new_yellow_count >= 2) {
        // Convert to red card - reset yellows and add red
        $new_yellow_count = 0; // Reset yellow cards
        $new_red_count = $current_red + 1;
        $updateSql = "UPDATE team_members SET yellow = ?, red = ? WHERE member_id = ?";
        $cardTypeToRecord = 'red'; // Record as red card due to double yellow
        $message = 'Second yellow card issued to ' . $player['fname'] . ' ' . $player['lname'] . ' - AUTOMATIC RED CARD! (Red cards: ' . $new_red_count . ')';
    } else {
        // Regular yellow card
        $updateSql = "UPDATE team_members SET yellow = ? WHERE member_id = ?";
        $cardTypeToRecord = 'yellow';
        $message = 'Yellow card issued to ' . $player['fname'] . ' ' . $player['lname'] . ' (Total: ' . $new_yellow_count . ')';
    }
} elseif ($card === 'red') {
    // Direct red card - increment red count
    $new_red_count = $current_red + 1;
    $updateSql = "UPDATE team_members SET red = ? WHERE member_id = ?";
    $cardTypeToRecord = 'red';
    $message = 'Red card issued to ' . $player['fname'] . ' ' . $player['lname'] . ' (Total: ' . $new_red_count . ')';
} else {
    die(json_encode(['success' => false, 'message' => 'Invalid card type']));
}

// Begin transaction
mysqli_autocommit($conn, false);

try {
    // Update team_members table
    $stmt = mysqli_prepare($conn, $updateSql);

    if ($card === 'yellow' && isset($new_red_count)) {
        // Double yellow to red conversion - update both yellow and red counts
        mysqli_stmt_bind_param($stmt, "iii", $new_yellow_count, $new_red_count, $player_id);
    } elseif ($card === 'yellow') {
        // Regular yellow card
        mysqli_stmt_bind_param($stmt, "ii", $new_yellow_count, $player_id);
    } elseif ($card === 'red') {
        // Direct red card
        mysqli_stmt_bind_param($stmt, "ii", $new_red_count, $player_id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update player card count");
    }

    // Record card in cards table for history
    if ($match_id) {
        $cardSql = "INSERT INTO cards (member_id, card_type, match_id, card_time, created_at) VALUES (?, ?, ?, ?, NOW())";
        $cardStmt = mysqli_prepare($conn, $cardSql);
        mysqli_stmt_bind_param($cardStmt, "isis", $player_id, $cardTypeToRecord, $match_id, $card_time);

        if (!mysqli_stmt_execute($cardStmt)) {
            throw new Exception("Failed to record card in history");
        }
    }

    // Commit transaction
    mysqli_commit($conn);

    // Return success response
    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'card_type' => $cardTypeToRecord,
            'player_name' => $player['fname'] . ' ' . $player['lname']
        ]);
    } else {
        // Redirect back to the referring page for non-AJAX requests
        $_SESSION['card_message'] = $message;
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'view_match.php'));
        exit;
    }

} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);

    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    } else {
        die("Error: " . $e->getMessage());
    }
}

mysqli_close($conn);
?>
