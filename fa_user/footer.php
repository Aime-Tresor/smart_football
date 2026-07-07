   <!-- footer -->
          
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/node_modules/jquery/jquery.min.js"></script>
    <!-- Bootstrap popper Core JavaScript -->
    <script src="../assets/node_modules/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="../assets/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="../assets/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="../assets/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="../assets/js/custom.min.js"></script>
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!--morris JavaScript -->
    <script src="../assets/node_modules/raphael/raphael-min.js"></script>
    <script src="../assets/node_modules/morrisjs/morris.min.js"></script>
    <!--c3 JavaScript -->
    <script src="../assets/node_modules/d3/d3.min.js"></script>
    <script src="../assets/node_modules/c3-master/c3.min.js"></script>
    <!-- Chart JS -->
    <script src="../assets/js/dashboard1.js"></script>
    <script type="text/javascript">
        $(document).ready(function()
        {
            $("#select1").change(function(){
                var refeere_id = $(this).val();
                $.ajax({
                    url:"Dynamic.php",
                    method:"POST",
                    data:{refeere_id:refeere_id},
                    success:function(data) {
                        $("#select2").html(data);
                    }
                });
            });
            $("#select2").change(function(){
                var AssRef1 = $(this).val();
                $.ajax({
                    url:"Dynamic.php",
                    method:"POST",
                    data:{AssRef1:AssRef1},
                    success:function(data) {
                        $("#select3").html(data);
                    }
                });
            });
                 $("#select3").change(function(){
                var AssRef2 = $(this).val();
                $.ajax({
                    url:"Dynamic.php",
                    method:"POST",
                    data:{AssRef2:AssRef2},
                    success:function(data) {
                        $("#select4").html(data);
                    }
                });
            });
                 $("#select4").change(function(){
                var official = $(this).val();
                $.ajax({
                    url:"Dynamic.php",
                    method:"POST",
                    data:{official:official},
                    success:function(data) {
                        $("#select5").html(data);
                    }
                });
            });
        }
        );
    </script>
</body>

</html>