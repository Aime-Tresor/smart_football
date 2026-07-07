<?php session_start(); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ferwafa</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="Logo/Ferwafa_logo.png">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <!-- Google fonts - Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,700">
    <!-- Choices CSS-->
    <link rel="stylesheet" href="vendor/choices.js/public/assets/styles/choices.min.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="assets/css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="css/custom.css">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
  </head>
  <body>
    <div class="login-page">
      <div class="container d-flex align-items-center position-relative py-5">
        <div class="card shadow-sm w-100 rounded overflow-hidden bg-none">
          <div class="card-body p-0">
            <div class="row gx-0 align-items-stretch">
              <!-- Logo & Information Panel-->
              <div class="col-lg-6">
                <div class="info d-flex justify-content-center flex-column p-4 h-100">
                  <div class="py-5">
                    <h1 class="display-6 fw-bold">Football Association</h1>
                    <p class="fw-light mb-0">Rwanda Primus National League</p>
                  </div>
                </div>
              </div>
              <?php  if(isset($_SESSION['success'])) {?>
                        <?php echo $_SESSION['success']; ?>
                   <?php unset($_SESSION['success']); } ?>
              <!-- Form Panel    -->
              <div class="col-lg-6 bg-white">
               
                <div class="d-flex align-items-center px-4 px-lg-5 h-100">
                  <form class="login-form py-5 w-100" method="post" action="app/createAcc.php">
                    <div class="input-material-group mb-3">
                      <input class="input-material" id="login-names" type="text" name="names" autocomplete="off" required data-validate-field="username">
                      <label class="label-material" for="login-names">Full Names</label>
                    </div>
                    <div class="input-material-group mb-4">
                      <input class="input-material" id="login-username" type="text" name="username" required data-validate-field="password">
                      <label class="label-material" for="login-username">Username</label>
                    </div>
                    <div class="input-material-group mb-4">
                      <input class="input-material" id="login-password" type="password" name="password" required data-validate-field="password">
                      <label class="label-material" for="login-password">Password</label>
                    </div>
                    <button class="btn btn-primary mb-3" name="submit" id="login" type="submit">Create Account</button><br>
                    <br>
                    <a class="text-sm text-paleBlue" href="index.php">Login</a><br>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
     
    </div>
   
    <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <script src="assets/js/front.js"></script>
</body>
</html>