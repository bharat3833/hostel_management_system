<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '_dbconnect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if($_SERVER["REQUEST_METHOD"] == "POST") {

    if(isset($_POST['removestudetails'])) {
        $Id = $_POST["Id"];
        $sql = "DELETE FROM `hostelbookings` WHERE `id`='$Id'";   
        $result = mysqli_query($conn, $sql);
        echo "<script>alert('Removed');
            window.location.href='/hostel-management-system/index.php?page=hostelstuManage';
            </script>";
    }
    
   }
?>