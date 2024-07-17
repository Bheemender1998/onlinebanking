<?php
include "../connection.php";

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
}
$username = $_SESSION['username'];

$mongoDB = $mongoClient->selectDatabase("onlinebanking");

// Perform aggregation
$pipeline = [
    [
        '$lookup' => [
            'from' => 'login', // Name of the login collection
            'localField' => 'Account_No',
            'foreignField' => 'AccountNo',
            'as' => 'login_info'
        ]
    ],
    [
        '$lookup' => [
            'from' => 'accounts', // Name of the accounts collection
            'localField' => 'Account_No',
            'foreignField' => 'AccountNo',
            'as' => 'account_info'
        ]
    ],
    [
        '$match' => [
            'login_info.Username' => $username
        ]
    ]
];

$collection = $mongoDB->selectCollection("customer_detail");
$cursor = $collection->aggregate($pipeline);

foreach ($cursor as $document) {
    // Extract data from the document
    $AccountNo = $document['Account_No'];
    $Fname = $document['C_First_Name'];
    $Lname = $document['C_Last_Name'];
    $color = $document['ProfileColor'];

   
    $ProfileText = substr($Fname, 0, 1);

    // Store data in session
    $_SESSION['AccountNo'] = $AccountNo;
    $_SESSION['ProfileText'] = $ProfileText;
    $_SESSION['ProfileColor'] = $color;
}

$collection = $mongoDB->selectCollection("transaction");

// Define aggregation pipeline
$pipeline = [
    [
        '$match' => ['AccountNo' => $AccountNo]
    ],
    [
        '$group' => [
            '_id' => '$Date', // Group by date
            'credit' => ['$sum' => '$Credit'],
            'debit' => ['$sum' => '$Debit']
        ]
    ]
];

// Execute aggregation pipeline
$cursor = $collection->aggregate($pipeline);


// Initialize arrays to store results
$date = [];
$credit = [];
$debit = [];

// Iterate over aggregation results
foreach ($cursor as $document) {
    // Extract data from document
    $date[] = $document['_id']->toDateTime()->format('Y-m-d H:i:s');
    $credit[] = $document['credit'];
    $debit[] = $document['debit'];
}


// Counting daily Transaction 

// $debitChart = "SELECT DATE(Date) AS DATE, SUM(Status = 'Debited') AS status FROM transaction WHERE AccountNo = '$AccountNo' AND Status = 'Debited' OR Status = 'Credited' GROUP BY DATE(Date)";


// $result = mysqli_query($conn, $debitChart);
// $Ddate = array();
// $Debitdata = array();

// if (mysqli_num_rows($result) > 0) {

//     while ($row = mysqli_fetch_assoc($result)) {

//         $Ddate[] = $row['DATE'];
//         $Debitdata[] = (int)$row['status'];
//     }
// }




$collection = $mongoDB->selectCollection("transaction");

// Calculate total credited amount
$creditPipeline = [
    [
        '$match' => ['AccountNo' => $AccountNo, 'Status' => 'Credited']
    ],
    [
        '$group' => [
            '_id' => null,
            'totalCredit' => ['$sum' => '$Amount']
        ]
    ]
];

$creditCursor = $collection->aggregate($creditPipeline);
$CreditTotal = 0;
foreach ($creditCursor as $document) {
    $CreditTotal = $document['totalCredit'];
}

// Calculate total debited amount
$debitPipeline = [
    [
        '$match' => ['AccountNo' => $AccountNo, 'Status' => 'Debited']
    ],
    [
        '$group' => [
            '_id' => null,
            'totalDebit' => ['$sum' => '$Amount']
        ]
    ]
];

$debitCursor = $collection->aggregate($debitPipeline);
$DebitTotal = 0;
foreach ($debitCursor as $document) {
    $debitTotal = $document['totalDebit'];
}


?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard O B S</title>

    <!-- Favicons -->
    <link href="../../assets/img/favicon-32x32.png" rel="icon">
    <link href="../../assets/img/apple-icon-180x180.png" rel="apple-touch-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
	<!-- div end -->
    <link rel="stylesheet" href="../../assets/vendor/boxicons/css/boxicons.css">
    <link rel="stylesheet" href="../../assets/vendor/boxicons/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/vendor/boxicons/css/animations.css">
    <link rel="stylesheet" href="../../assets/vendor/boxicons/css/transformations.css">



    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!--fontawesome-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel="stylesheet" href="../../assets/css/UserDash.css">
    <style>
        @media only screen and (min-width:992px) {
            #credit {
                display: block;
                box-sizing: border-box;
                height: 181px;
                width: 363px;
            }
        }
    </style>

</head>

<body>

    <?php include "header.php" ?>
    <!-- End of Topbar -->

    <!-- Begin Page Content -->
    <div class="container-fluid px-lg-4">
        <div class="row">
            <div class="col-md-12 mt-lg-4 mt-4">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i>
                        Generate Report</a> -->
                </div>
            </div>
			<!-- div end -->
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Balance</h5>
                                <h1 id="BalanceDisplay" class="display-5 mt-1 mb-3"></h1>
                                <div class="mb-1">
                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                    <span class="text-muted">Total Balance</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Saving</h5>
                                <h1 id="SavingDisplay" class="display-5 mt-1 mb-3"></h1>
                                <div class="mb-1">
                                    <span class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                    <span class="text-muted">Total Savings</span>
                                </div>
                            </div>
                        </div>

                    </div>
					<!-- div end -->
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4 ">Credited this Month</h5>
                                <h1 id="CreditDisplay" class="display-5 mt-1 mb-3 text-success"></h1>
                                <div class="mb-1">
                                    <span id="CreditLastM" class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i></span>
                                    <span class="text-muted">Since last Month</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Debited this Month</h5>
                                <h1 id="DebitDisplay" class="display-5 mt-1 mb-3 text-danger"></h1>
                                <div class="mb-1">
                                    <span id="DebitLastM" class="text-danger"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                    <span class="text-muted">Since last Month</span>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </div>

            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h4 class="card-title">Transaction Graph</h4>
                            <h6 class="card-subtitle">Overview of Daily transaction</h6>
                        </div>
                        <div class="d-md-flex align-items-center">

                            <canvas id="Credit" width="300" height="100"></canvas>
                            <div class="ml-auto">


                            </div>
                        </div>

                    </div>
                </div>
            </div>
			<!-- div end -->
            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h4 class="card-title">Cash Flow Graph</h4>
                            <h6 class="card-subtitle">Overview of Total Cash flow</h6>
                        </div>
                        <div class="d-md-flex align-items-center">

                            <canvas id="Balance" width="300" height="100"></canvas>
                            <div class="ml-auto">


                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!-- column -->
            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <!-- title -->
                        <div class="d-md-flex align-items-center">
                            <div>
                                <h4 class="card-title">My Transaction History</h4>
                                <h5 class="card-subtitle">Overview of recent transaction</h5>
                            </div>
                            <div class="ml-auto">
                                <a href="T_history.php" class="btn btn-info" role="button">View More</a>
                                <!-- <div class="dl">
                                    <select class="custom-select">
                                        <option value="0" selected="">Monthly</option>
                                        <option value="1">Daily</option>
                                        <option value="2">Weekly</option>
                                        <option value="3">Yearly</option>
                                    </select>
                                </div> -->
                            </div>
                        </div>
                        <!-- title -->
                    </div>
                    <div class="table-responsive">
                        <table class="table v-middle table-striped table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th class="border-top-0">#</th>
                                    <th class="border-top-0">Name</th>
                                    <th class="border-top-0">Account No.</th>
                                    <th class="border-top-0">Date</th>
                                    <th class="border-top-0">Amount</th>
                                    <th class="border-top-0">Status</th>


                                </tr>
                            </thead>
                            <tbody>
                                <?php
                               $collection = $mongoDB->selectCollection("transaction");

                               // Define query to find latest 5 transactions for the account
                               $filter = ['AccountNo' => $AccountNo];
                               $options = [
                                   'sort' => ['_id' => -1], // Sort by id in descending order
                                   'limit' => 5 // Limit to 5 documents
                               ];
                               
                               // Execute the find operation
                               $cursor = $collection->find($filter, $options);
                               
                               // Initialize increment counter
                               $increment = 1;
                               
                               // Iterate over the cursor to process each transaction
                               foreach ($cursor as $row) {

                                ?>
                                        <tr>
                                            <td><?php echo $increment; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="m-r-10"><a style="font-size: 13px; background-color:<?php echo $row['ProfileColor'] ?>" class="btn btn-circle text-white"> <?php
                                                                                                                                                                                            $name = $row['Name'];
                                                                                                                                                                                            $pices = explode(" ", $name);
                                                                                                                                                                                            echo substr($pices[0], 0, 1);
                                                                                                                                                                                            echo substr($pices[1], 0, 1);
                                                                                                                                                                                            ?></a>
                                                    </div>
                                                    <div class="">
                                                        <h4 class="m-b-0 font-16"><?php echo $row['Name'] ?></h4>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $row['FAccountNo'] ?></td>
                                            <td><?php echo  $row['Date']->toDateTime()->format('Y-m-d H:i:s'); ?></td>
                                            <td>
                                                <label class="label label-danger"><?php echo '$'.$row['Amount'] ?></label>
                                            </td>
                                            <td>

                                                <span class="Status
                                            
                                            <?php
                                            if ($row['Status'] == 'Debited')
                                                echo "text-danger";
                                            else
                                                echo "text-success";
                                            ?>"><?php echo $row['Status'] ?></span>

                                            </td>

                                        </tr>
                                    <?php
                                        $increment++;
                                    } ?>
                                <?php  ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>

    </div>

    <?php include "footer.php" ?>
    <!-- Wraper Ends Here -->
	<!-- div end -->




    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.2.1/dist/chart.min.js"></script>
    <script src="../UserData/js/profileInfo.js"></script>
    <script src="../UserData/js/dashboard.js"></script>


    <script>
        $('#bar').click(function() {
            $(this).toggleClass('open');
            $('#page-content-wrapper ,#sidebar-wrapper').toggleClass('toggled');

        });
    </script>

    <script>
        var ctx = document.getElementById('Credit').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($date); ?>,
                datasets: [{
                        label: '# Credited',
                        data: <?php echo json_encode($credit); ?>,

                        // We Have to compare array two array i.e Debit data and data for expected result
                        backgroundColor: [
                            'rgba(2, 158, 59)',

                        ],
                        borderColor: 'rgb(2, 158, 59)',
                        borderWidth: 2
                    },
                    {
                        label: '# Debited',
                        data: <?php echo json_encode($debit); ?>,
                        backgroundColor: [
                            'rgba(232, 26, 26)',

                        ],
                        borderColor: 'rgb(232, 26, 26)',
                        borderWidth: 2
                    }
                ]
            },

            options: {

                // responsive: false,
                
            }
        });


        var ctx = document.getElementById('Balance').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Credit', 'Debit'],
                datasets: [{
                        label: 'Cash Flow Chart',
                        data: [<?php echo $CreditTotal ?>, <?php echo abs($DebitTotal) ?>],
                        backgroundColor: [
                            'rgba(2, 158, 59)',
                            'rgba(232, 26, 26)',

                        ],
                        borderColor: [
                            'rgba(2, 158, 59)',
                            'rgba(232, 26, 26)',
                        ],

                        borderWidth: 2,
                        barThickness: 70
                    }

                ]
            },

            options: {

                // responsive: false,
            }
        });


        // Send Bar Chart
    </script>





</body>

</html>