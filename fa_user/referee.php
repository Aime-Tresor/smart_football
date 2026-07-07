<?php 

require_once 'header.php'; ?>
<?php
 if (isset($_GET['delete'])) {
    $referee_id  = $_GET['delete'];
  
    try {
      $sql = 'DELETE FROM `referee` WHERE referee_id=:referee_id';
      $stmt = $connection->prepare($sql);
      if($stmt->execute([':referee_id' => $referee_id]))
      {      



        $_SESSION['success'] = "Referee has been deleted.";
        header("Location: referee.php?deleted");
    } 
  }
  catch (PDOException $e) {
       echo $e->getMessage();
    $_SESSION['error'] = "Referee did not deleted.";
      header("Location: referee.php?error");
    }
     
   }
 ?>
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->

        <div class="row">
            <!-- Column -->
            <div class="col-lg-8 col-md-12">
                <div class="card card-body mailbox">
                <?php
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success" role="alert">'
                            . $_SESSION['success'] .
                            '</div>';
                        unset($_SESSION['success']);
                    }

                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger" role="alert">'
                            . $_SESSION['error'] .
                            '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>
                    <h5 class="card-title">All referee</h5>
                    <div class="message-center" style="height: 420px !important;">
                        <div class="table-responsive mt-3 no-wrap">
                            <table class="table vm no-th-brd pro-of-month">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Names</th>
                                        <th>Email Address</th>
                                        <th colspan="2">Settings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                                $sql = 'SELECT * FROM `referee`';
                                                $statement = $connection->prepare($sql);
                                                $statement->execute();
                                                $Referees = $statement->fetchAll(PDO::FETCH_OBJ);
                                                foreach($Referees as $referee):
                                                ?>
                                    <tr class="active">
                                        <td><span class="round"><img src="../Profile/<?= $referee->image; ?>" alt="user"
                                                    width="50" height="50"></span></td>
                                        <td>
                                            <h6><?= $referee->fname; ?></h6>
                                            <small class="text-muted"><?= $referee->lname; ?></small>
                                        </td>
                                        <td><?= $referee->email; ?></td>
                                        <td> <a href="?edit=<?= $referee->referee_id; ?>"><i class="fa fa-edit"></i></a>
                                        </td>
                                        <td> <a href="?delete=<?= $referee->referee_id; ?>"><i class="fa fa-trash-o"></i></a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column -->
            <!-- Column -->
            <div class="col-lg-4 col-md-12">
                <?php
                        if (isset($_GET['edit'])) {
                        $referee_id = $_GET['edit'];
                        $sql = 'SELECT * FROM `referee` WHERE referee_id = ?';
                        $statement = $connection->prepare($sql);
                        $statement->execute([$referee_id ]);
                        $Referees = $statement->fetchAll(PDO::FETCH_OBJ);
                        foreach($Referees as $referee):
                                ?>
                <div class="card card-body mailbox">
                    <h5 class="card-title mb-5">Edit Referee</h5>
                    <form class="form-horizontal form-material mx-2" method="post" action="controls/EditReferee.php"
                        enctype="multipart/form-data">
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="hidden" name="referee_id" value="<?= $_GET['edit']; ?>">
                                <input type="text" name="fname" value="<?= $referee->fname; ?>" class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="text" name="lname" value="<?= $referee->lname; ?>"
                                    class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="file" name="profile" class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="email" name="email" value="<?= $referee->email; ?>"
                                    class="form-control form-control-line" required>
                            </div>
                        </div>
                      
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button name="submit" class="btn btn-success">Edit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
                endforeach;
                        }
                        else{
                            ?>
                <div class="card card-body mailbox">
                    <h5 class="card-title mb-5">Add Referee</h5>
                    

                    
                    <form class="form-horizontal form-material mx-2" method="post" action="controls/addReferee.php"
                        enctype="multipart/form-data">
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="text" name="fname" placeholder="First name"
                                    class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="text" name="lname" placeholder="Last Name"
                                    class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="file" name="profile" class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="email" name="email" placeholder="Email Address"
                                    class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="password" name="password" placeholder="Enter Your Password"
                                    class="form-control form-control-line" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button name="submit" class="btn btn-success">Add Referee</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
                        }
                        ?>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- End Notification And Feeds -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- End Page Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <?php require 'footer.php'; ?>