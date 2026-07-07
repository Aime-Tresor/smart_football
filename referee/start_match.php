<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['match_id'])) {
        die("Match ID missing.");
    }

    $match_id = intval($_POST['match_id']);

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "fa_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update match status to 'live' for the given match_id
    $sql = "UPDATE `match` SET status = 'live' WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $match_id);

    if ($stmt->execute()) {
        // Redirect back to matches page or show success message
        header("Location: matches.php?message=Match+started+successfully");
        exit;
    } else {
        echo "Error updating match status: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
