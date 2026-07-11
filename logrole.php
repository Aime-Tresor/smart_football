<?php
session_start();

// Handle form submission to redirect based on role
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';

    switch (strtolower($role)) {
        case 'referee':
            header('Location: referee.php');
            exit;
        case 'teams':
            header('Location: teams.php');
            exit;
        case 'admin':
            header('Location: login.php');
            exit;
            case 'discipline_committee_officer':
              header('Location: app/committee_login.php');
              exit;
        default:
            $_SESSION['msg'] = "Please select a valid role.";
            $_SESSION['msg_type'] = "error";
            header('Location: logrole.php');
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Ferwafa - Select Role</title>
  <link rel="icon" type="image/png" sizes="16x16" href="Logo/Ferwafa_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="all,follow">

  <!-- Fonts and CSS -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
  <link rel="stylesheet" href="vendor/choices.js/public/assets/styles/choices.min.css">
  <link rel="stylesheet" href="assets/css/style.default.css" id="theme-stylesheet">
  <link rel="stylesheet" href="css/custom.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
if (isset($_SESSION['msg'])) {
  echo "<script>
          Swal.fire({
            icon: '" . ($_SESSION['msg_type'] ?? 'info') . "',
            title: 'Notification',
            text: '" . addslashes($_SESSION['msg']) . "',
            confirmButtonText: 'OK'
          });
        </script>";
  unset($_SESSION['msg']);
  unset($_SESSION['msg_type']);
}
?>

<!-- Role Selection Page -->
<div class="login-page">
  <div class="container d-flex align-items-center position-relative py-5">
    <div class="card shadow-sm w-100 rounded overflow-hidden bg-none">
      <div class="card-body p-0">
        <div class="row gx-0 align-items-stretch">

          <!-- Info Panel -->
          <div class="col-lg-6">
            <div class="info d-flex justify-content-center flex-column p-4 h-100 text-white position-relative">
              <!-- Back Button -->
              <div class="position-absolute top-0 start-0 p-3">
                <a href="index.php" class="btn btn-outline-light btn-sm">
                  <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
              </div>

              <div class="py-5 text-center">
                <div class="text-center mb-4">
                </div>
                <h1 class="display-6 fw-bold">Football Association</h1>
                <p class="fw-light mb-0">BK Pro League Role Selection</p>
              </div>
            </div>
          </div>

          <!-- Role Buttons Panel -->
          <div class="col-lg-6 bg-white">
            <div class="d-flex align-items-center px-4 px-lg-5 h-100">
              <form class="login-form py-5 w-100" method="post" action="">
                <h2 class="mb-4 text-center fw-bold">Select Your Role</h2>

                <button type="submit" name="role" value="referee" class="btn btn-outline-success w-100 mb-3 py-2 fs-5">
                  <i class="fas fa-whistle me-2"></i> Referee
                </button>
                <!-- <button type="submit" name="role" value="committee_login" class="btn btn-outline-success w-100 mb-3 py-2 fs-5">
                  <i class="fas fa-whistle me-2"></i> Discipline Commette Officer
                </button> -->
                <button type="submit" name="role" value="discipline_committee_officer" class="btn btn-outline-success w-100 mb-3 py-2 fs-5">
                <i class="fas fa-gavel me-2"></i> Discipline Committee Officer
                </button>
                <button type="submit" name="role" value="teams" class="btn btn-outline-primary w-100 mb-3 py-2 fs-5">
                  <i class="fas fa-users me-2"></i> Teams
                </button>

                <button type="submit" name="role" value="admin" class="btn btn-outline-danger w-100 mb-3 py-2 fs-5">
                  <i class="fas fa-user-shield me-2"></i> Admin
                </button>

              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="assets/js/front.js"></script>
</body>
</html>
