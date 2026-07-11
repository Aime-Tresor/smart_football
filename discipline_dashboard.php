<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Ferwafa - Admin Login</title>
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

<!-- Login Page -->
<div class="login-page">
  <div class="container d-flex align-items-center position-relative py-5">
    <div class="card shadow-sm w-100 rounded overflow-hidden bg-none">
      <div class="card-body p-0">
        <div class="row gx-0 align-items-stretch">
        
          <!-- Info Panel -->
          <div class="col-lg-6">
            <div class="info d-flex justify-content-center flex-column p-4 h-100 text-white bg-primary">
              <div class="py-5">
                <div class="text-center mb-4">
                  <img src="Logo/Ferwafa_logo.png" alt="Ferwafa Logo" width="100">
                </div>
                <h1 class="display-6 fw-bold">Football Association</h1>
                <p class="fw-light mb-0">BK Pro League Admin Portal</p>
              </div>
            </div>
          </div>

          <!-- Login Form Panel -->
          <div class="col-lg-6 bg-white">
            <div class="d-flex align-items-center px-4 px-lg-5 h-100">
              <form class="login-form py-5 w-100" method="post" action=#>
                <h2 class="mb-4 text-center fw-bold">Discipline Commette Login Portal</h2>
                
                <div class="input-material-group mb-3">
                  <input class="input-material" id="login-username" type="text" name="username" autocomplete="off" required>
                  <label class="label-material" for="login-username">Username</label>
                </div>

                <div class="input-material-group mb-4">
                  <input class="input-material" id="login-password" type="password" name="password" required>
                  <label class="label-material" for="login-password">Password</label>
                </div>

                <button class="btn btn-primary mb-3 w-100" name="submit" id="login" type="submit">Login</button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://use.fontawesome.com/releases/v5.7.1/js/all.js" crossorigin="anonymous"></script>
<script src="assets/js/front.js"></script>

</body>
</html>
