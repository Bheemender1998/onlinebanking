<?php
session_start();
if (!isset($_SESSION['accountNo'])) {
    header("Location: ../user/login.php");
}

include "../admin/connection.php";
include "../admin/Notification.php";
include "../admin/adminData.php";
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

    <title>Verify Account O B S</title>

    <!-- Favicons -->
    <link href="../assets/img/favicon-32x32.png" rel="icon">
    <link href="../assets/img/apple-icon-180x180.png" rel="apple-touch-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>



    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!--fontawesome-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../admin/css/accounts/OpenAccount.css">

    <style>
        .table-bordered>tbody>td>tr,
        .table-bordered>thead>td>tr,
        .table-bordered {
            border-bottom: 0;
            border-top: 0;
        }

        .close {
            float: right;
            font-size: 21px;
            font-weight: 700;
            line-height: 1;
            color: #000;
            text-shadow: 0 1px 0 #fff;
            filter: alpha(opacity=20);
            opacity: .2;
        }

        .close {
            color: #fff;
            opacity: 1;
        }

        /* Style the Image Used to Trigger the Modal */
        .kycImg {
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .kycImg:hover {
            opacity: 0.7;
        }

        /* The Modal (background) */
        .customodal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            padding-top: 100px;
            /* Location of the box */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.9);
            /* Black w/ opacity */
        }

        /* Modal Content (Image) */
        .customodal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }

        /* Caption of Modal Image (Image Text) - Same Width as the Image */
        #caption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: #ccc;
            padding: 10px 0;
            height: 150px;
        }

        /* Add Animation - Zoom in the Modal */
        .customodal-content,
        #caption {
            animation-name: zoom;
            animation-duration: 0.6s;
        }

        @keyframes zoom {
            from {
                transform: scale(0)
            }

            to {
                transform: scale(1)
            }
        }

        /* The Close Button */
        .closebtn {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .closebtn:hover,
        .closebtn:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        /* 100% Image Width on Smaller Screens */
        @media only screen and (max-width: 700px) {
            .modal-content {
                width: 100%;
            }
        }
    </style>

    <!-- Javascrip -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.2.0/dist/chart.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


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
                    <li class="menuHover">

                        <a href="../admin/Dashboard.php" class="nav-link text-left" role="button" aria-haspopup="true" aria-expanded="false">
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
                        <!-- Show class show dropdown by default -->
                        <div class="collapse menu mega-dropdown " id="collapseExample2">
                            <div class="dropmenu" aria-labelledby="navbarDropdown">
                                <div class="container-fluid ">
                                    <div class="row">
                                        <div class="col-lg-12 px-2">
                                            <div class="submenu-box">
                                                <ul class="list-unstyled m-0">
                                                    <!-- active class for helight on which page we are -->
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
                    </li>

                    <li class="menuHover box-icon">
                        <a class="nav-link text-left active" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bx-check-circle ico"></i> Verify Account <span class="badge badge-success" style="font-size: 12px; margin-left: 50px;"> <?php echo $inactiveAccountsCount; ?> new</span>
                        </a>
                    </li>

                    <li class="menuHover" id="Transaction">
                        <a href="transactions.php" class="nav-link text-left" role="button">
                            <i class="flaticon-bar-chart-1"></i> <i class="bx bx-history ico"></i> Transactions
                        </a>
                    </li>


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
                                </div>
                            </li>
                            <!-- Nav Item - Pending Customer Accounts ENDS -->

                            <!-- Nav Item - User Information -->
                            <li class="nav-item ">
                                <a class="nav-link " href="#" role="button">
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
                                    <h1 class="h3 mb-0">Verify Account</h1>
                                    <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm light btn-primary "><i class="bx bx-log-out-circle ico"></i>
                                        Logout</a> -->
                                </div>


                            </div>

                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card">
                                            <div class="card-body ">
                                                <!-- <h5 class="card-title mb-4 "></h5> -->


                                                <?php

$mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

$inactiveAccountsCollection = $mongoDB->login;
$inactiveAccountsCursor = $inactiveAccountsCollection->find(['Status' => 'Inactive']);

// Convert cursor to array
$inactiveAccountsArray = iterator_to_array($inactiveAccountsCursor);

// Count the number of inactive accounts
$inactiveAccountsCount = count($inactiveAccountsArray);

// Check if there are inactive accounts
if ($inactiveAccountsCount > 0) {
    // There are inactive accounts
    

                                                ?>

                                                    <div class="table-responsive">

                                                        <table id="EditTable" class="table table-striped table-hover v-middle" style="margin-top: 30px;">
                                                            <thead class="thead-dark">
                                                                <tr>
                                                                    <th scope="col">#ID</th>
                                                                    <th scope="col">Account No</th>
                                                                    <th scope="col">Username</th>
                                                                    <th scope="col">Status</th>
                                                                    <th scope="col">View Account</th>
                                                                    <th scope="col">Actions</th>
                                                                 

                                                                </tr>
                                                            </thead>
                                                            <tbody class="">

                                                                <?php
                                                                foreach ($inactiveAccountsArray as $row) {



                                                                ?>
                                                                    <tr>
                                                                        <th  scope="row"><?php echo $row['_id']; ?></th>

                                                                        <td ><?php echo $row['AccountNo']; ?></td>

                                                                        <td ><?php echo $row['Username']; ?></td>

                                                                        <td ><?php echo $row['Status']; ?></td>

                                                                        <td >

                                                                            <button name="view_btn" id="<?php echo $row['AccountNo']; ?>" class="btn view_data btn btn-primary" data-toggle="modal" data-target="#exampleModalLong"><i class='bx bx-show-alt'></i> View</button>

                                                                        </td>
                                                                        <td >

                                                                            <button id="<?php echo $row['AccountNo']; ?>" class="btn btn-success verify_data"><i class='bx bx-user-check'></i> Verify</button>

                                                                        <!-- </td>
                                                                        <td > -->
                                                                            <button id="<?php echo $row['AccountNo'];
                                                                                        ?>" class="btn btn-danger reject_data"><i class='bx bx-user-x'></i> Reject</button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>

                                                                <?php
                                                                }
                                                            } else {

                                                                ?>
                                                                <h1  style="text-align: center;">No Account for Verification</h1>

                                                            <?php
                                                            }
                                                            ?>

                                                            </tbody>
                                                        </table>

                                                    </div>

                                                    <!-- Search Table -->


                                            </div>
                                        </div>

                                    </div>


                                </div>


                            </div>

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
        </div>
        <!-- /#page-content-wrapper -->

    </div>

    <!-- Button trigger modal -->


    <!-- Customer Detail Modal -->
    <div class="modal fade bd-example-modal-lg" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle" style="margin-left: 40%;">Customer Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 style="margin-bottom: 30px; text-align:center; margin-top:20px;border-bottom: 1px solid;display: table;">Customer Detail: </h6>

                    <!-- Detail Table -->
                    <table class="table table-striped table-bordered">
                        <tbody>

                            <tr>
                                <th >First Name</th>
                                <td  id="Fname"></td>
                            </tr>
                            <tr>
                                <th >Last Name</th>
                                <td  id="Lname"></td>

                            </tr>
                            <tr>
                                <th >Father Name</th>
                                <td  id="Faname"></td>
                            </tr>
                            <tr>
                                <th >Mother Name</th>
                                <td  id="Maname"></td>
                            </tr>
                            <tr>
                                <th >Birth Date</th>
                                <td  id="Bdate"></td>
                            </tr>
                            <tr>
                                <th >Citizenship No</th>
                                <td  id="CitizenshipNo"></td>
                            </tr>
                            <tr>
                                <th >Pan No</th>
                                <td  id="PanNo"></td>
                            </tr>
                            <tr>
                                <th >Mobile No</th>
                                <td  id="MobileNo"></td>
                            </tr>
                            <tr>
                                <th >Email</th>
                                <td  id="Email"></td>
                            </tr>
                            <tr>
                                <th >Pincode</th>
                                <td  id="Pincode"></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- KYC Document -->
                    <h6 style="margin-bottom: 30px;margin-top: 30px;border-bottom: 1px solid;display: table;">KYC Documents Uploaded: </h6>




                    <!-- Trigger the Modal -->
                    <img id="CitizenshipImg" class="kycImg" alt="Citizenship Card" style="width:100%;max-width:300px">
                    <img id="PanImg" class="kycImg" alt="Pan Card" style="width:100%;max-width:300px;margin-left: 20%;">

                    <!-- The Modal -->
                    <div id="myModal" class="customodal">

                        <!-- The Close Button -->
                        <span class="closebtn">&times;</span>

                        <!-- Modal Content (The Image) -->
                        <img class="modal-content" id="img01">
                        <img class="modal-content" id="img02">

                        <!-- Modal Caption (Image Text) -->
                        <div id="caption"></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
                    <button type="button" class="btn btn-success" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /#wrapper -->


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>


    <!-- sweet alert cdn  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous"></script>

    <script src="../admin/js/verifyAc.js"></script>
    <script>
        $('#bar').click(function() {
            $(this).toggleClass('open');
            $('#page-content-wrapper ,#sidebar-wrapper').toggleClass('toggled');

        });

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
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

    <script>
        var modal = document.getElementById("myModal");

        // Get the image and insert it inside the modal - use its "alt" text as a caption
        var img = document.getElementById("CitizenshipImg");
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }
        var Panimg = document.getElementById("PanImg");
        var modalImg2 = document.getElementById("img02");
        var captionText = document.getElementById("caption");
        Panimg.onclick = function() {
            modal.style.display = "block";
            modalImg2.src = this.src;
            captionText.innerHTML = this.alt;
        }

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("closebtn")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
    </script>


</body>

</html>