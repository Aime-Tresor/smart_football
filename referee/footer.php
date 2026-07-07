 
        </div>

    </div>
  
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
    <script>
        $(function(){
 
 $('#phone').keyup(function()
 {
 var yourInput = $(this).val();
 re = /[abcdefghijklmnopqrstuvwxyz`~!@#$%^&*( )_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
 var isSplChar = re.test(yourInput);
 if(isSplChar)
 {
 var no_spl_char = yourInput.replace(/[`abcdefghijklmnopqrstuvwxyz~!@#$%^&*( )_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
 $(this).val(no_spl_char);
 }
 });
 });
 $(function(){
  
 $('#Id_number').keyup(function()
 {
 var yourInput = $(this).val();
 re = /[abcdefghijklmnopqrstuvwxyz`~!@#$%^&*( )_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
 var isSplChar = re.test(yourInput);
 if(isSplChar)
 {
 var no_spl_char = yourInput.replace(/[`abcdefghijklmnopqrstuvwxyz~!@#$%^&*( )_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
 $(this).val(no_spl_char);
 }
 });
 });
 $(function(){
  
 $('#fullname').keyup(function()
 {
 var yourInput = $(this).val();
 re = /[1234567890`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
 var isSplChar = re.test(yourInput);
 if(isSplChar)
 {
 var no_spl_char = yourInput.replace(/[`1234567890~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
 $(this).val(no_spl_char);
 }
 });
  
 });
 $(function(){
  
 $('#lname').keyup(function()
 {
 var yourInput = $(this).val();
 re = /[1234567890`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
 var isSplChar = re.test(yourInput);
 if(isSplChar)
 {
 var no_spl_char = yourInput.replace(/[`1234567890~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
 $(this).val(no_spl_char);
 }
 });
 $('#fname').keyup(function()
 {
 var yourInput = $(this).val();
 re = /[1234567890`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi;
 var isSplChar = re.test(yourInput);
 if(isSplChar)
 {
 var no_spl_char = yourInput.replace(/[`1234567890~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
 $(this).val(no_spl_char);
 }
 });
  
 });
 
 function fileValidation(){
     var fileInput = document.getElementById('file');
     var filePath = fileInput.value;
     var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
     if(!allowedExtensions.exec(filePath)){
         alert('Please Uplaod Only Picture');
         fileInput.value = '';
         return false;
     }
 };
 $(function() {
     $( "#datepicker" ).datepicker({  minDate: new Date() });
 });
 
    </script>
</body>

</html>