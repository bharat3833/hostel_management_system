<?php 
    session_start();
    if(isset($_SESSION['adminloggedin']) && $_SESSION['adminloggedin']==true){
        $adminloggedin= true;
        $userId = $_SESSION['adminuserId'];
    }
    else{
        $adminloggedin = false;
        $userId = 0;
    }

if($adminloggedin) {
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <title>Admin Page</title>
    <link rel = "icon" href ="/hostel-management-system/img/hostel-image.png" type = "image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body id="body-pd">
    <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
    <?php if ($page === 'home') { ?>
        <style>
        .iiitdm-banner {
            width: 100%;
            background: linear-gradient(90deg, #0ea5e9 60%, #38bdf8 100%);
            padding: 6px 0 4px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: fadeInDown 1.1s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 2px 8px rgba(2,6,23,0.04);
            z-index: 100;
        }
        .iiitdm-banner img {
            height: 32px;
            width: auto;
            border-radius: 6px;
            background: #fff;
            padding: 1px 3px;
            box-shadow: 0 1px 4px rgba(14,165,233,.10);
        }
        .iiitdm-banner span {
            color: #fff;
            font-size: 1.08rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-family: 'Segoe UI', 'Inter', Arial, sans-serif;
            text-shadow: 0 1px 2px rgba(2,6,23,0.08);
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        </style>
        <div class="iiitdm-banner">
            <img src="/hostel-management-system/img/iiit.jpeg" alt="IIITDM Kurnool Logo">
            <span>Welcome to IIITDM Kurnool</span>
        </div>
    <?php } ?>
<?php
   require 'partials/_dbconnect.php';
   require 'partials/_nav.php';

    if(isset($_GET['loginsuccess']) && $_GET['loginsuccess']=="true"){
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="width:100%">
                    <strong>Success!</strong> You are logged in
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
                  </div>';
        }
    ?>

<?php $page = isset($_GET['page']) ? $_GET['page'] :'home';
     include $page.'.php'
?>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>         
    <script src="https://unpkg.com/bootstrap-show-password@1.2.1/dist/bootstrap-show-password.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
    }
else
{
 header("location: /hostel-management-system/login.php");
}
?>