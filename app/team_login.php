<?php
session_start();
require 'database.php';

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Input validation
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required.";
        header("Location: ../teams.php?error=empty_fields");
        exit();
    }

    try {
        // First, get the team data by username only
        $sql = 'SELECT * FROM team WHERE username = ?';
        $stmt = $connection->prepare($sql);
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $team_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stored_password = $team_data['password'];
            $login_successful = false;

            // Check password using multiple methods
            // Method 1: Direct plain text comparison (for old passwords)
            if ($password === $stored_password) {
                $login_successful = true;
                $password_type = 'plain';
            }
            // Method 2: MD5 hash comparison (for new passwords)
            elseif (md5($password) === $stored_password) {
                $login_successful = true;
                $password_type = 'md5';
            }
            // Method 3: Check if stored password is MD5 and compare with plain text
            elseif (strlen($stored_password) === 32 && ctype_xdigit($stored_password)) {
                // Stored password looks like MD5 hash, compare with hashed input
                if (md5($password) === $stored_password) {
                    $login_successful = true;
                    $password_type = 'md5_hash';
                }
            }

            if ($login_successful) {
                // Set session variables
                $_SESSION['Team_id'] = $team_data['team_id'];
                $_SESSION['Team_Name'] = $team_data['name'];
                $_SESSION['logo'] = $team_data['logon'];
                $_SESSION['stadium'] = $team_data['stadium'];
                $_SESSION['username'] = $team_data['username'];

                // Optional: Log successful login for security monitoring
                error_log("Team login successful: " . $username . " (password type: " . $password_type . ")");

                // Optional: Upgrade plain text passwords to MD5 for better security
                if ($password_type === 'plain') {
                    try {
                        $hashed_password = md5($password);
                        $update_sql = 'UPDATE team SET password = ? WHERE team_id = ?';
                        $update_stmt = $connection->prepare($update_sql);
                        $update_stmt->execute([$hashed_password, $team_data['team_id']]);
                        error_log("Password upgraded to MD5 for team: " . $username);
                    } catch (PDOException $e) {
                        // Log error but don't fail login
                        error_log("Failed to upgrade password for team: " . $username . " - " . $e->getMessage());
                    }
                }

                // Redirect to team dashboard
                header("Location: ../teams/");
                exit();
            } else {
                // Password doesn't match
                $_SESSION['login_error'] = "Invalid username or password.";
                error_log("Team login failed - invalid password: " . $username);
                header("Location: ../teams.php?error=invalid_credentials");
                exit();
            }
        } else {
            // Username not found
            $_SESSION['login_error'] = "Invalid username or password.";
            error_log("Team login failed - username not found: " . $username);
            header("Location: ../teams.php?error=invalid_credentials");
            exit();
        }

    } catch (PDOException $e) {
        // Database error
        $_SESSION['login_error'] = "Database error occurred. Please try again.";
        error_log("Team login database error: " . $e->getMessage());
        header("Location: ../teams.php?error=database_error");
        exit();
    }
} else {
    // No form submission
    header("Location: ../teams.php");
    exit();
}