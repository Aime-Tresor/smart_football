<?php
session_start();
require 'database.php';

$error_msg = '';

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_msg = "Username and password are required.";
    } else {
        try {
            $sql = 'SELECT * FROM committee_members WHERE username = ?';
            $stmt = $connection->prepare($sql);
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $committee_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $stored_password = $committee_data['password'];
                $login_successful = false;

                // Check password (plain text or MD5)
                if ($password === $stored_password) {
                    $login_successful = true;
                } elseif (md5($password) === $stored_password) {
                    $login_successful = true;
                }

                if ($login_successful) {
                    $_SESSION['fa_user'] = $committee_data['username'];
                    $_SESSION['committee_id'] = $committee_data['committee_id'];
                    $_SESSION['committee_name'] = $committee_data['name'];
                    $_SESSION['committee_role'] = $committee_data['role'] ?? 'Member';

                    header("Location: ../fa_user/committee_member_dashboard.php");
                    exit();
                } else {
                    $error_msg = "Invalid username or password.";
                }
            } else {
                $error_msg = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error_msg = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Discipline Committee Login - Ferwafa</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../Logo/Ferwafa_logo.png">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-left h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .login-right {
            padding: 60px 40px;
        }
        .login-right h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        .login-right p {
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            opacity: 0.8;
        }
        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 20px;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #333;
        }

        @media (max-width: 768px) {
            .login-left {
                padding: 40px 20px;
            }
            .login-right {
                padding: 40px 20px;
            }
            .login-right h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0">
            <div class="col-lg-5 login-left">
                <a href="../logrole.php" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Role Selection
                </a>
                <h1>Discipline Committee</h1>
                <p>Rwanda Football Association</p>
                <p class="mt-4" style="font-size: 0.95rem; opacity: 0.85;">
                    Access the committee dashboard to review appeals and make disciplinary decisions.
                </p>
            </div>
            <div class="col-lg-7 login-right">
                <h2>Committee Officer Login</h2>
                <p>Sign in to your account</p>

                <?php if (!empty($error_msg)): ?>
                    <div class="error-alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user me-2" style="color: #667eea;"></i>Username
                        </label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock me-2" style="color: #667eea;"></i>Password
                        </label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" name="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <div class="info-box">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Access Committee Dashboard</strong><br>
                    Review pending appeals, make decisions, and view appeal history.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
