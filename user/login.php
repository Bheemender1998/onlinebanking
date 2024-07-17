<?php
include 'connection.php';
include "script.php";
session_start();

if (isset($_SESSION['username'])) {
    header("Location: ../user/UserData/Dashboard.php");
} else {

    <console class="log">ssss</console>

    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $hashPassword = md5($password);
        $password_err = $username_err = "";

        if (empty(trim($_POST['password'])) && empty(trim($_POST['username']))) {

            header("Location: ../user/login.php?error=Username and Password required");
            exit();
        } elseif (empty(trim($_POST['username']))) {

            $username_err = "Username cannot be blank";
            header("Location: ../user/login.php?error=Username required");
            exit();
        } elseif (empty(trim($_POST['password']))) {

            header("Location: ../user/login.php?error=Password required");
            exit();
        } else {
            $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

            $collection = $mongoDB->login; // Assuming 'login' is your collection name
            
            
            //$hashPassword = hash('sha256', $password); // Assuming you hash passwords in the same way as in your MySQL version
            
            $filter = [
                'Username' => $username,
                'Password' => $hashPassword
            ];
            print_r($filter);
            $result = $collection->findOne($filter);
            
            if ($result) {
                $status = $result['Status'];
                $state = $result['State'];
            
                if ($state == 0) {
                    if ($status == "Active") {
                        session_start();
                        $_SESSION['username'] = $username;
                        // $_SESSION['id'] = $result['ID'];
                        // $_SESSION['accountNo'] = $result['AccountNo'];
                        header("Location: ../user/UserData/Dashboard.php");
                        exit();
                    } else {
                        header("Location: ../user/login.php?error=Account not Activated");
                        exit();
                    }
                } elseif ($state == 1) {
                    if ($status == "Super") {
                        session_start();
                        $_SESSION['accountNo'] = $result['AccountNo'];
                        header("Location: ../admin/Dashboard.php");
                        exit();
                    } else {
                        header("Location: ../user/login.php?error=Account not Activated");
                        exit();
                    }
                }
            } else {
                header("Location: ../user/login.php?error=Invalid Credential");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login O B S</title>
    <!-- Favicons -->
    <link href="../assets/img/favicon-32x32.png" rel="icon">
    <link href="../assets/img/apple-icon-180x180.png" rel="apple-touch-icon">

	<!-- div end -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css?family=Karla:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.8.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/login.css">

    <!-- Extra CSS for external module -->
    <style>
        .swal-button {
            padding: 7px 19px;
            border-radius: 2px;
            background: linear-gradient(to right, #8e2de2, #4a00e0);
            font-size: 12px;
            color: white;
        }

        .swal-button:hover {
            opacity: 0.8;
            background-color: transparent;
        }
    </style>


</head>

<body>
    <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
        <div class="container">
            <div class="card login-card">
                <div class="row no-gutters">
                    <div class="col-md-5">
                        <img src="../assets/img/PageImage/lgnimg.png" alt="login" class="login-card-img">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body">
                            <div class="brand-wrapper">
                                <img src="../assets/img/lg-1328.png" alt="logo" class="logo">
                                <p>Online Banking System</p>
                            </div>
                            <p class="login-card-description">Log into your account</p>

                            <!-- Login Form -->
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

                                <?php if (isset($_GET['error'])) {  ?>

                                    <p style="color: red;"> *<?php echo $_GET['error'] ?> ! </p>

                                <?php } ?>

                                <div class="form-group">
                                    <label for="username" class="sr-only">Username</label>
                                    <input type="text" name="username" id="Username" class="form-control" placeholder="Username" autocomplete="off" required>
                                    <p id="alert1" style="color: red;"></p>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="password" class="sr-only">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" autocomplete="off" placeholder="***********" required>
                                </div>
                                <input name="login" id="login" class="btn btn-block login-btn mb-4" type="submit" value="Login">
                            </form>
                            <a href="../user/forgotPassword.php" class="forgot-password-link">Forgot password?</a>
                            <p class="login-card-footer-text">Don't have an account? <a href="../user/CreateAccount.php" class="text-reset">Register here</a></p>
                            <nav class="login-card-footer-nav">
                                <a href="../terms.html">Terms of use.</a>
                                <a href="../privacypolicy.html">Privacy policy</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
	<!-- div end -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="../assets/js/sweetalert.min.js"></script>
    <script src="../assets/js/showHidePass.js"></script>
    <script>
        <?php if (isset($_GET['error'])) { ?>
            swal({
                title: "Account Alert!",
                text: "<?php echo $_GET['error'] ?>",
                icon: "error",
            });


        <?php } ?>
    </script>
    <script>
        $(document).ready(function() {
            $('input[type=\'password\']').showHidePassword();

            // $('#OldPassword').showHidePassword();
        });
    </script>
</body>

</html>