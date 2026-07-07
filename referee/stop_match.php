<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_id'])) {
    $matchId = (int) $_POST['match_id'];

    $conn = new mysqli("localhost", "root", "", "fa_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $update = $conn->prepare("UPDATE `match` SET status = 'completed' WHERE id = ?");
    $update->bind_param("i", $matchId);
    if ($update->execute()) {
        header("Location: cads.php");
        exit();
    } else {
        echo "Error stopping match.";
    }

    $conn->close();
}
?>
