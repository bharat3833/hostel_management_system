<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">

    <title>Login</title>
    <link rel="icon" href="/hostel-management-system/img/hostel-image.png" type="image/x-icon">

    <style>
        body {
            height: 100vh;
            margin: 0;
            background: linear-gradient(180deg, #ffffff, #f6f9fc 60%);
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #0f172a;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .login-container {
            display: flex;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(2,6,23,.08);
            box-shadow: 0px 18px 50px rgba(2,6,23,0.12);
            overflow: hidden;
            width: 880px;
            max-width: 96%;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, rgba(14,165,233,.15), rgba(2,132,199,.10));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .login-left img {
            max-width: 100%;
            border-radius: 14px;
            border: 1px solid rgba(2,6,23,.08);
            box-shadow: 0 10px 30px rgba(14,165,233,.18);
        }

        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            animation: fadeInRight 0.8s ease;
        }

        @keyframes fadeInRight {
            from {opacity: 0; transform: translateX(50px);}
            to {opacity: 1; transform: translateX(0);}
        }

        .login-card {
            width: 100%;
        }

        .login-card h3 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 22px;
            color: #0f172a;
        }
        .form-control, .input-group-text {
            background: #ffffff;
            border: 1px solid rgba(2,6,23,.12);
            color: #0f172a;
        }
        .form-control::placeholder { color: #64748b; }
        .form-check-label { color: #334155; }

        .btn-primary {
            border-radius: 30px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.25s ease;
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            border: none;
            color: #fff;
            box-shadow: 0px 8px 24px rgba(14,165,233,.25);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0px 12px 28px rgba(14,165,233,.35);
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Left Logo/Image -->
        <div class="login-left">
            <img src="\hostel-management-system\img\iiit.jpeg" alt="Hostel Logo">
        </div>

        <!-- Right Form -->
        <div class="login-right">
            <div class="login-card">
                <h3><i class="fas fa-user-circle"></i> Hostel Login</h3>
                <form id="loginForm" action="partials/_handleLogin.php" method="post">
                    
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username"><b>Username</b></label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter Username" required>
                    </div>

                    <!-- Password with Show/Hide -->
                    <div class="form-group">
                        <label for="password"><b>Password</b></label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter Password" required>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
            </div>
        </div>
    </div>

<?php
// PHP Login Error Handling
if(isset($_GET['loginsuccess']) && $_GET['loginsuccess']=="false"){
    echo '<script>
            document.getElementById("loginForm").classList.add("shake");
            setTimeout(()=>{ document.getElementById("loginForm").classList.remove("shake"); }, 800);
          </script>
          <div class="alert alert-danger text-center mt-3">
            <strong>Invalid Credentials!</strong> Please try again.
          </div>';
}
?>

    <!-- jQuery + Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    <!-- Show/Hide Password Script -->
    <script>
        $(document).ready(function(){
            $("#togglePassword").click(function(){
                let input = $("#password");
                let type = input.attr("type") === "password" ? "text" : "password";
                input.attr("type", type);
                $(this).toggleClass("fa-eye fa-eye-slash");
            });
        });
    </script>

</body>
</html>
