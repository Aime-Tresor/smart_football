<?php
/**
 * Universal Logout Script
 * Handles logout from any dashboard section and redirects to logrole.php
 * 
 * This script can be called from any dashboard:
 * - Referee Dashboard
 * - Team Dashboard  
 * - FA User Dashboard
 * - Admin Dashboard
 */

session_start();

// Log the logout attempt for security monitoring
$logout_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'session_id' => session_id(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

// Determine user type for logging
$user_type = 'Unknown';
$user_identifier = 'Unknown';

if (isset($_SESSION['referee_id'])) {
    $user_type = 'Referee';
    $user_identifier = $_SESSION['referee_id'];
} elseif (isset($_SESSION['Team_id'])) {
    $user_type = 'Team';
    $user_identifier = $_SESSION['Team_Name'] ?? $_SESSION['Team_id'];
} elseif (isset($_SESSION['fa_user'])) {
    $user_type = 'FA User';
    $user_identifier = $_SESSION['fa_user'];
} elseif (isset($_SESSION['admin_id'])) {
    $user_type = 'Admin';
    $user_identifier = $_SESSION['admin_id'];
}

// Log the logout event
error_log("Universal Logout: {$user_type} ({$user_identifier}) logged out at {$logout_info['timestamp']} from IP {$logout_info['ip_address']}");

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Optional: Add a success message for the next page
session_start();
$_SESSION['logout_success'] = "You have been successfully logged out.";

// Redirect to the role selection page
header("Location: logrole.php");
exit();
?>
