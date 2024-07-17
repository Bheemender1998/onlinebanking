<?php
session_start();
if (!isset($_SESSION['accountNo'])) {
    header("Location: ../user/login.php");
}


include '../user/connection.php';
include "../admin/Notification.php";
include "../admin/adminData.php";

$mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

// Get collection instances
$customerCollection = $mongoDB->customer_detail;
$loginCollection = $mongoDB->login;
$transactionCollection = $mongoDB->transaction;

// Count total customers
$TotalCustomer = $customerCollection->countDocuments();

// Count active accounts
$ActiveAccount = $loginCollection->countDocuments(['Status' => 'Active']);

// Count inactive accounts
$InactiveAccount = $loginCollection->countDocuments(['Status' => 'Inactive']);

// Count deactivated accounts
$DeactiveAccount = $loginCollection->countDocuments(['Status' => 'Deactivated']);

// Count total transactions
$TotalTransaction = $transactionCollection->countDocuments();

// Count total transactions in the last 24 hours
$twentyFourHoursAgo = new MongoDB\BSON\UTCDateTime((time() - 24 * 60 * 60) * 1000); // Get UTCDateTime 24 hours ago
$TotalTransactions24 = $transactionCollection->countDocuments(['Date' => ['$gt' => $twentyFourHoursAgo]]);





// Aggregate to get count of customers created per day
$pipeline = [
    ['$addFields' => [
        'date' => ['$toDate' => '$Create_Date'] // Convert Create_Date to BSON date object
    ]],
    ['$group' => [
        '_id' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$date']], 
        'count' => ['$sum' => 1]
    ]],
    ['$project' => [
        '_id' => 0, // Exclude _id field
        'date' => '$_id', // Rename _id field to date
        'count' => 1
    ]],
    ['$sort' => ['date' => 1]] // Sort by date in ascending order
];
$cursor = $customerCollection->aggregate($pipeline);
$date = [];
$data = [];

foreach ($cursor as $document) {
    $date[] = $document['date'];
    $data[] = $document['count'];
}

//   echo "There are " . $rows . " rows in my table.";



/* 

set id from 1 in sql

SET @autoid := 0;
UPDATE login SET ID = @autoid := (@autoid+1);
ALTER TABLE login AUTO_INCREMENT = 1; 

127.0.0.1/obs/customer_detail/		http://localhost/phpmyadmin/tbl_sql.php?db=obs&table=customer_detail
 Showing rows 0 -  4 (5 total, Query took 0.0030 seconds.)

SELECT
    DATE(Create_Date) AS DATE,
    COUNT(C_No)
FROM
    customer_detail
GROUP BY
    DATE(Create_Date)



*/

?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Dashboard O B S</title>

    <!-- Favicons -->
    <link href="../assets/img/favicon-32x32.png" rel="icon">
    <link href="../assets/img/apple-icon-180x180.png" rel="apple-touch-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/boxicons/css/boxicons.css">
    <link rel="stylesheet" href="../assets/vendor/boxicons/css/boxicons.min.css">
    <link rel="stylesheet" href="../assets/vendor/boxicons/css/animations.css">
    <link rel="stylesheet" href="../assets/vendor/boxicons/css/transformations.css">

	<!-- div end -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!--fontawesome-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../admin/css/AdminDash.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.2.0/dist/chart.min.js"></script>

    <style>
        .btn-circle.btn-md {
            width: 65px;
            height: 65px;
            padding: 7px 10px;
            border-radius: 45px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;

            
        }
        @media (max-width: 768px) {
            .ShowHide{
                display: none;
            }
        }

    </style>


</head>

<body class="">

    <div id="wrapper">
        <div class="overlay"></div>

        <!-- Sidebar -->
        <nav class="fixed-top align-top" id="sidebar-wrapper" role="navigation">
            <div class="simplebar-content" style="padding: 0px;">
                <a class="sidebar-brand" href="../index.php">
                <img src="../assets/img/lg-1328.png" alt="" width="24px;" class="img-fluid"><span class="align-middle"> O B S</span>
                </a>

                <ul class="navbar-nav align-self-stretch">

                    <!-- <li class="sidebar-header">
                        Pages
                    </li> -->
                    <li class="">

                        <a class="nav-link text-left active" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="flaticon-bar-chart-1"></i><i class="bx bxs-dashboard ico"></i> Dashboard
                        </a>
                    </li>

                    <li class="has-sub menuHover">
                        <!-- this link href="collapseExample1" shows submenue  -->
                        <a class="nav-link collapsed text-left" href="#collapseExample1" role="button" data-toggle="collapse">
                            <i class="flaticon-user"></i> <i class="bx bxs-wallet-alt Profile ico"></i> Wallet
                        </a>
                        <!-- id is a collapseExample1 -->
                        <div class="collapse menu mega-dropdown" id="collapseExample1">
                            <div class="dropmenu" aria-labelledby="navbarDropdown">
                                <div class="container-fluid ">
                                    <div class="row">
                                        <div class="col-lg-12 px-2">
                                            <div class="submenu-box">
                                                <ul class="list-unstyled m-0">
                                                    <li><a href="../admin/wallet/Withdraw.php">Withdraw Money</a></li>
                                                    <li><a href="../admin/wallet/Deposit.php">Deposit Money</a></li>

                                                </ul>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
						<!-- div end -->
                    </li>


                    <li class="menuHover">
                        <a href="../admin/TransferMoney.php" class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i><i class="bx bx-transfer ico"></i> Transfer
                        </a>
                    </li>

                    <li class="has-sub menuHover">
                        <a class="nav-link collapsed text-left" href="#collapseExample2" role="button" data-toggle="collapse">
                            <i class="flaticon-user"></i> <i class="bx bx-user-circle Profile ico"></i> Customer Accounts
                        </a>
                        <div class="collapse menu mega-dropdown" id="collapseExample2">
                            <div class="dropmenu" aria-labelledby="navbarDropdown">
                                <div class="container-fluid ">
                                    <div class="row">
                                        <div class="col-lg-12 px-2">
                                            <div class="submenu-box">
                                                <ul class="list-unstyled m-0">
                                                    <!-- <li><a href="../admin/accounts/OpenAccount.php">Open Account</a></li> -->
                                                    <li><a href="../admin/accounts/EditAccount.php">Edit Account</a></li>
                                                    <li><a href="../admin/accounts/ActivateAccount.php">Activate Account</a></li>
                                                    <li><a href="../admin/accounts/DeactivateAccount.php">Deactivate Account</a></li>
                                                    <li><a href="../admin/accounts/CloseAccount.php">Close Account</a></li>


                                                </ul>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
						<!-- div end -->
                    </li>

                    <li class="menuHover box-icon">
                        <a href="../admin/VerifyAccount.php" class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bx-check-circle ico"></i> Verify Account <span class="badge badge-success" style="font-size: 12px; margin-left: 50px;"> <?php echo $inactiveAccountsCount; ?> new</span>
                        </a>
                    </li>

                    <li class="menuHover" id="Transaction">
                        <a href="transactions.php" class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bx-history ico"></i> Transactions
                        </a>
                    </li>






                    <!-- <li class="sidebar-header">
                        tools and component
                    </li> -->

                    <!-- <li class="menuHover box-icon">
                        <a class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bx-dollar-circle ico"></i>Insurance Requests
                        </a>
                    </li> -->

                    <!-- <li class="menuHover box-icon">
                        <a class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i><i class="bx bxs-coin ico"></i> Loan Requests
                        </a>
                    </li> -->
                    
                    <li class="menuHover">
                        <a href="../admin/cards.php" class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bxs-credit-card ico"></i>Cards Requests <span class="badge badge-success" style="font-size: 12px; margin-left: 50px;"> <?php echo $unverifiedCardsCount; ?> new</span>
                        </a>
                    </li>  

                    <!-- <li class="sidebar-header">
                        tools and component
                    </li> -->
                    <!-- <li class="menuHover">
                        <a class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bxs-cog ico"></i> Setting
                        </a>
                    </li> -->
                    <li class="menuHover">
                        <a class="nav-link text-left" role="button" href="../admin/logout.php">
                            <i class="flaticon-map"></i><i class="bx bx-log-out ico"></i> Logout
                        </a>
                    </li>

                </ul>


            </div>


        </nav>
        <!-- /#sidebar-wrapper -->


        <!-- Page Content -->
        <div id="page-content-wrapper">


            <div id="content">

                <div class="container-fluid p-0 px-lg-0 px-md-0">
                    <!-- Topbar -->
                    <nav class="navbar navbar-expand navbar-light gray_bg my-navbar">

                        <!-- Sidebar Toggle (Topbar) -->
                        <div type="button" id="bar" class="nav-icon1 hamburger animated fadeInLeft is-closed" data-toggle="offcanvas">
                            <span class="light_bg"></span>
                            <span class="light_bg"></span>
                            <span class="light_bg"></span>
                        </div>


                        <!-- Topbar Navbar -->
                        <ul class="navbar-nav ml-auto">

                            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                            <li class="nav-item dropdown  d-sm-none">

                                <!-- Dropdown - Messages -->
                                <div class="dropdown-menu dropdown-menu-right p-3">
                                    <form class="form-inline mr-auto w-100 navbar-search">
                                        <div class="input-group">
                                            <input type="text" class="form-control border-0 small" placeholder="Search for...">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button">
                                                    <i class="fas fa-search fa-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </li>

                            <!-- Nav Item - Pending Customer Accounts -->
                            <li class="nav-item dropdown">
                                <a class="nav-icon dropdown" href="#" id="alertsDropdown" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-plus"></i>
                            
                                    <span class="badge badge-danger badge-counter"><?php echo $inactiveAccountsCount; ?></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right py-0" aria-labelledby="alertsDropdown">
                                    <div class="dropdown-menu-header">
                                        Notifications
                                    </div>
                                    <div class="list-group">
                                        <a href="VerifyAccount.php" class="list-group-item">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle text-danger">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                                    </svg>
                                                </div>
                                                <div class="col-10">
                                                    <div class="text-dark"><b><?php echo $inactiveAccountsCount; ?></b> New Pending Accounts</div>
                                                    <div class="text-muted small mt-1">Check the "Verify Account" section from the sidebar for more details.</div>
                                                </div>
                                            </div>
                                        </a>

                                    </div>
									<!-- div end -->
                                </div>
                            </li>
                            <!-- Nav Item - Pending Customer Accounts ENDS -->

                            <!-- Nav Item - User Information -->
                            <li>
                                <a class="nav-link" href="#" role="button">
                                    <span class="mr-2 d-none d-lg-inline small"><?php echo $Admin ?></span>
                                    <img id="AdminDropdown" class="img-profile rounded-circle" src="<?php echo $AdminProfile; ?>">
                                </a>
                            </li>

                        </ul>

                    </nav>
                    <!-- End of Topbar -->

                    <!-- Begin Page Content -->
                    <div class="container-fluid px-lg-4">
                        <div class="row">
                            <div class="col-md-12 mt-lg-4 mt-4">
                                <!-- Page Heading -->
                                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                    <h1 class="h3 mb-0">Dashboard</h1>
                                    <div>
                                        <a href="../admin/wallet/Deposit.php" role="button" class="btn btn-success btn-circle btn-md ShowHide mr-5">
                                            <div><i class='bx bxs-down-arrow-alt bx-sm'></i></div>Deposit
                                        </a>
                                        <a href="../admin/wallet/Withdraw.php" role="button" class="btn btn-danger btn-circle btn-md ShowHide mr-5">
                                            <div><i class='bx bxs-up-arrow-alt bx-sm'></i></div>Withdraw
                                        </a>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="card">
                                            <div class="card-body card-shadow">
                                                <h5 class="card-title mb-4 ">Total Customer</h5>
                                                <h1 class="display-5 mt-1 mb-3"><?php echo "$TotalCustomer"; ?></h1>
                                                <div class="mb-1">
                                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-sm-3">
                                        <div class="card">
                                            <div class="card-body card-shadow">
                                                <h5 class="card-title mb-4">Active Accounts</h5>
                                                <h1 class="display-5 mt-1 mb-3"><?php echo $ActiveAccount; ?></h1>
                                                <div class="mb-1">
                                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
									<!-- div end -->
                                    <div class="col-sm-3">
                                        <div class="card">
                                            <div class="card-body card-shadow">
                                                <h5 class="card-title mb-4">Pending Accounts</h5>
                                                <h1 class="display-5 mt-1 mb-3"><?php echo $InactiveAccount; ?></h1>
                                                <div class="mb-1">
                                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-sm-3">
                                        <div class="card">
                                            <div class="card-body card-shadow">
                                                <h5 class="card-title mb-4">Deactivated Accounts</h5>
                                                <h1 class="display-5 mt-1 mb-3"><?php echo $DeactiveAccount; ?></h1>
                                                <div class="mb-1">
                                                    <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>


                                </div>


                            </div>
							<!-- div end -->

                            <div class="col-md-12">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body card-shadow">
                                                <h5 class="card-title mb-4 ">Daily Account Created</h5>
                                                <canvas id="Customer" width="600" height="200"></canvas>

                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">

                                        <div class="row">
                                            <div class="col-sm-6">

                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <!-- <div class="card">
                                                            <div class="card-body card-shadow">
                                        
                                                                <div class="d-flex justify-content-center">
                                                                    <a role="button" href="../admin/accounts/EditAccount.php" class="btn text-decoration-none btn-info mt-3 mb-3"><i class='bx bxs-pencil'></i> Edit Account</a>
                                                                </div>

                                                            </div>
                                                        </div> -->
                                                        <div class="card">
                                                            <div class="card-body card-shadow">
                                                                <h5 class="card-title mb-4 ">Total Transactions </h5>
                                                                <h1 class="display-5 mt-1 mb-3"><?php echo "$TotalTransaction"; ?></h1>
                                                                <div class="mb-1">
                                                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                                                    <span class="text-muted"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="card">
                                                            <div class="card-body card-shadow">
                                                                <h5 class="card-title mb-4 ">Total Transactions [24 hrs]</h5>
                                                                <h1 class="display-5 mt-1 mb-3"><?php echo "$TotalTransactions24"; ?></h1>
                                                                <div class="mb-1">
                                                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                                                    <span class="text-muted"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-sm-6">
                                                <div class="card">
                                                    <div class="card-body card-shadow">
                                                        <h5 class="card-title mb-4">Account Status</h5>
                                                        <canvas id="Accounts"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
							<!-- div end -->
                        </div>

                    </div>


                </div>

                <footer class="footer gray_bg">
                    <div class="container-fluid">
                        <div class="row text-muted">
                            <div class="col-6 text-left">
                                <p class="mb-0">
                                    <a href="../index.php" class="text-muted light">&copy<strong> Online Banking System
                                        </strong> - Developed by Bheem</a> 
                                </p>
                            </div>
                            <div class="col-6 text-right">
                                <ul class="list-inline">
                                    <!-- <li class="footer-item">
                                        <a class="text-muted light" href="#">Support</a>
                                    </li>
                                    <li class="footer-item">
                                        <a class="text-muted light" href="#">Help Center</a>
                                    </li> -->
                                    <li class="footer-item">
                                        <a class="text-muted light" href="../privacypolicy.html">Privacy</a>
                                    </li>
                                    <li class="footer-item">
                                        <a class="text-muted light" href="../terms.html">Terms</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer>


            </div>
			<!-- div end -->
        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->





    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>


    <script>
        $('#bar').click(function() {
            $(this).toggleClass('open');
            $('#page-content-wrapper ,#sidebar-wrapper').toggleClass('toggled');

        });

        // logout popover on profile 




        $("#AdminDropdown").click(function() {
            $(this).popover({

                title: 'Profile Detail',
                html: true,
                container: "body",
                placement: 'bottom',
                content: ` <a href="../admin/logout.php" role="button" class="btn btn-danger nav-link">Logout</a>`

            })


        });
    </script>

    <script>
        var ctx = document.getElementById('Accounts').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'Deactivated'],
                datasets: [{
                    label: '# of Votes',
                    data: [<?php echo $ActiveAccount ?>, <?php echo $InactiveAccount ?>, <?php echo $DeactiveAccount ?>],
                    backgroundColor: [
                        'rgba(87, 161, 88)',
                        'rgba(224, 191, 4)',
                        'rgb(207, 54, 54)'

                    ],
                    borderColor: [
                        'rgba(87, 161, 88)',
                        'rgba(224, 191, 4)',
                        'rgb(207, 54, 54)'

                    ],
                    borderWidth: 1,
                    barThickness: 70,

                }]
            },
            options: {
                cutout: "70%",
                radius: "90%",
                // responsive: false,
                layout: {
                    padding: {
                        left: -40,
                    },

                }
            }
        });


        var ctx = document.getElementById('Customer').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($date); ?>,
                datasets: [{
                    label: 'Accounts Created',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: [
                        'rgb(2, 117, 216)',

                    ],
                    borderColor: 'rgb(2, 117, 216)',
                    borderWidth: 2
                }]
            },
            options: {

                // responsive:false,
            }
        });
    </script>






</body>

</html>