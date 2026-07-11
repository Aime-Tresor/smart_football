<?php
session_start();
require '../app/database.php';
?>
<?php 
if (!isset($_SESSION['fa_user']) or empty($_SESSION['fa_user'])) {
    $_SESSION['msg'] = '<script>swal("Error!", "Please Login first", "fail");</script>';
    echo"<script>window.location=' ../'</script>";
}
 ?>
<!DOCTYPE html>
<html lang="en">

<style>

    /* Sidebar background and text */
.left-sidebar {
  background: #1e2a38; /* Dark blue-gray */
  color: #e0e7ff; /* Light text */
  width: 260px;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  border-right: 1px solid #2f3b4a;
  transition: width 0.3s ease;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  z-index: 999;
}

/* Scrollbar for sidebar */
.scroll-sidebar::-webkit-scrollbar {
  width: 8px;
}
.scroll-sidebar::-webkit-scrollbar-thumb {
  background-color: #6366f1; /* Indigo */
  border-radius: 4px;
}

/* Sidebar navigation list */
#sidebarnav {
  padding: 0;
  margin: 0;
  list-style: none;
}

/* Sidebar links */
#sidebarnav li a {
  display: flex;
  align-items: center;
  padding: 15px 24px;
  font-weight: 600;
  font-size: 15px;
  color: #394867;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
  text-decoration: none;
  white-space: nowrap;
}

/* Sidebar icons */
#sidebarnav li a i.fa {
  margin-right: 16px;
  font-size: 18px;
  color: #818cf8; /* lighter indigo */
  width: 22px;
  text-align: center;
}

/* Hover and active link styles */
#sidebarnav li a:hover,
#sidebarnav li a.active {
  background-color: #394867; /* muted blue */
  color: #ffffff;
  border-left: 4px solid #6366f1; /* indigo */
}
#sidebarnav li a:hover i.fa,
#sidebarnav li a.active i.fa {
  color: #a5b4fc; /* lighter indigo */
}

/* Hide the default focus outline but keep accessibility */
#sidebarnav li a:focus {
  outline: none;
  box-shadow: 0 0 8px 2px #818cf8;
  border-left: 4px solid #818cf8;
}

/* Responsive sidebar width (optional) */
@media (max-width: 768px) {
  .left-sidebar {
    width: 60px;
  }
  #sidebarnav li a span.hide-menu {
    display: none;
  }
  #sidebarnav li a i.fa {
    margin-right: 0;
  }
}




</style>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords"
        content="wrappixel, admin dashboard, html css dashboard, web dashboard, bootstrap 5 admin, bootstrap 5, css3 dashboard, bootstrap 5 dashboard, AdminWrap lite admin bootstrap 5 dashboard, frontend, responsive bootstrap 5 admin template, AdminWrap lite design, AdminWrap lite dashboard bootstrap 5 dashboard template">
    <meta name="description"
        content="AdminWrap Lite is powerful and clean admin dashboard template, inpired from Bootstrap Framework">
    <meta name="robots" content="noindex,nofollow">
    <title>Ferwafa</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../Logo/Ferwafa_logo.png">
    <!-- Bootstrap Core CSS -->
    <link href="../assets/node_modules/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/node_modules/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
      <!-- <link href="../assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" /> -->
    <!-- This page CSS -->
    <!-- chartist CSS -->
    <link href="../assets/node_modules/morrisjs/morris.css" rel="stylesheet">
    <!--c3 CSS -->
    <link href="../assets/node_modules/c3-master/c3.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Dashboard 1 Page CSS -->
    <link href="../assets/css/pages/dashboard1.css" rel="stylesheet">
    <!-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> -->
    <script src="../assets/js/sweet.alert.js"></script>
    <!-- You can change the theme colors from here -->
    <link href="../assets/css/colors/default.css" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="fix-header fix-sidebar card-no-border">

    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="index.html">
                        <!-- Logo icon --><b>
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="../Logo/Ferwafa_logo.png" alt="homepage" class="dark-logo" style="width: 40px;height: 40px;"/>
                            <!-- Light Logo icon -->
                         
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text --><span>
                            <!-- dark Logo text -->
                            <!-- <img src="../assets/images/logo-text.png" alt="homepage" class="dark-logo" /> -->
                            <!-- Light Logo text -->
                            <!-- <img src="../assets/images/logo-light-text.png" class="light-logo" alt="homepage" /></span> -->
                    </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up waves-effect waves-dark"
                                href="javascript:void(0)"><i class="fa fa-bars"></i></a> </li>
                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                     
                    </ul>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav my-lg-0">
                        <!-- ============================================================== -->
                        <!-- Profile -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown u-pro">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="#"
                                id="navbarDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img
                                    src="../Logo/Ferwafa_logo.png" alt="user" class="" /> <span
                                    class="hidden-md-down" style="color: #394867;"><?= $_SESSION['fa_user']; ?>&nbsp;</span> </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li>
                                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                            </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                <ul id="sidebarnav">
                
                <?php 
                // Check if user is committee member
                $is_committee = isset($_SESSION['committee_id']);
                ?>

                <!-- Always show Dashboard -->
                <li> 
                  <a class="waves-effect waves-dark" href="index.php" aria-expanded="false">
                    <i class="fa fa-home"></i><span class="hide-menu">Dashboard</span>
                  </a>
                </li>

                <!-- COMMITTEE-ONLY MENU ITEMS -->
                <?php if ($is_committee): ?>
                  <li> 
                    <a class="waves-effect waves-dark" href="committee_member_dashboard.php" aria-expanded="false">
                      <i class="fa fa-user-circle"></i><span class="hide-menu">My Profile</span>
                    </a>
                  </li>

                  <li> 
                    <a class="waves-effect waves-dark" href="discipline_committee_dashboard.php" aria-expanded="false">
                      <i class="fa fa-gavel"></i><span class="hide-menu">Review Appeals</span>
                    </a>
                  </li>

                  <li> 
                    <a class="waves-effect waves-dark" href="logout.php" aria-expanded="false">
                      <i class="fa fa-sign-out"></i><span class="hide-menu">Logout</span>
                    </a>
                  </li>
                <?php else: ?>
                  <!-- ADMIN MENU ITEMS (existing) -->
                  <li> <a class="waves-effect waves-dark" href="teams.php" aria-expanded="false"><i class="fa fa-user-circle-o"></i><span class="hide-menu">Teams</span></a></li>
                  <li> <a class="waves-effect waves-dark" href="referee.php" aria-expanded="false"><i class="fa fa-eraser"></i><span class="hide-menu">Referees</span></a></li>
                  <li> <a class="waves-effect waves-dark" href="fixture.php" aria-expanded="false"><i class="fa fa-th-list"></i><span class="hide-menu">Fixtures</span></a></li>
                  <li> <a class="waves-effect waves-dark" href="fixtureReport.php" aria-expanded="false"><i class="fa fa-user"></i><span class="hide-menu">Results</span></a></li>
                  <li> <a class="waves-effect waves-dark" href="transfer.php" aria-expanded="false"><i class="fa fa-refresh"></i><span class="hide-menu">Transfer</span></a></li>
                  <li> <a class="waves-effect waves-dark" href="manage_committee.php" aria-expanded="false"><i class="fa fa-users-cog"></i><span class="hide-menu">Manage Committee</span></a></li>
                  <li> <a class="waves-effect waves-dark" href="logout.php" aria-expanded="false"><i class="fa fa-sign-out"></i><span class="hide-menu">Logout</span></a></li>
                <?php endif; ?>

            </ul>
                    
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <?php  if(isset($_SESSION['msg'])) {?>
        <?php echo $_SESSION['msg']; ?>
        <?php unset($_SESSION['msg']); } ?>