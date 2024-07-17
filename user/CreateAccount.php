<?php
session_start();
include "connection.php";
include '../mail/mail_config.php';
// checking Submit button is clicked or not by isset function
if (isset($_POST['submit'])) {


    // ----------------------------------------- Basic Detail Section -----------------------------------------

    $Account_Type = "Saving";
    $Account_Status = "Inactive";
    $Balance = "0.0";

    // Storing Form values in variable
    $First_Name = $_POST['FirstName'];
    $Last_Name = $_POST['LastName'];
    $Father_Name = $_POST['FatherName'];
    $Mother_Name = $_POST['MotherName'];
    $Birth_Date = $_POST['BirthDate'];
    $Mobile_Number = $_POST['MobileNumber'];
    $Pan_Number = $_POST['PanNumber'];
    $Citizenship_Number = $_POST['CitizenshipNumber'];
    $Account_Number = date('ndyHisL');
    $Create_Date = date('Y-m-d');

    if(strlen($Account_Number)>12){
        $Account_Number = substr($Account_Number,0,-1);
    }

    $Email = $_POST['email'];
    $Pincode = $_POST['pincode'];



    //  Error Variables

    $First_Name_error =  $Last_Name_error = $Father_Name_error = $Mother_Name_error = null;
    $Birth_Date_error = $Mobile_Number_error =  $Pan_Number_error = $Citizenship_Number_error = null;
    $Email_error = $Pincode_error = null;

    // Validate Name of customer
    /* 
            1] Preg_match_all(): This function check any number is avaible in string or not
            2] !\d+! : passing this regular expression in above function which conatin numeric data pattern
            3] Varible : this parameter contains string to be check
            4] logic explain: if() ant numeric value found in string and it is == 1 
        
     */
	// div end
    if (preg_match_all('!\d+!', $First_Name) == 1) {
        $First_Name_error = "* Numeric value not allowed in First Name";
    }
    if (preg_match_all('!\d+!', $Last_Name) == 1) {
        $Last_Name_error = "* Numeric value not allowed in Last Name";
    }
    if (preg_match_all('!\d+!', $Father_Name) == 1) {
        $Father_Name_error = "* Numeric value not allowed in Father's Name";
    }
    if (preg_match_all('!\d+!', $Mother_Name) == 1) {
        $Mother_Name_error = "* Numeric value not allowed in Mother's Name";
    }


    // ********************************* Pan Number Validation *********************************************

    if ($Pan_Number != null) {
        // Regular Expression to validate pan number
        $regex = '/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/';

        // if pan number not match with above pattern
        if (!preg_match_all($regex, $Pan_Number)) {
            $Pan_Number_error = "* INVALID PAN NUMBER";
        } else {
            // $Pan_Number = mysqli_real_escape_string($conn, $_POST['PanNumber']);
            // $query =  $query = "SELECT * FROM customer_detail WHERE C_Pan_No = '" . $Pan_Number . "'";

            // $result =  mysqli_query($conn, $query);

            // if (mysqli_num_rows($result) > 0) {
            //     $Pan_Number_error = "* Pan Number Already Exist";
            // }
            $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

            $collection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name

            $Pan_Number = $_POST['PanNumber']; // No need to escape for MongoDB

            $filter = ['C_Pan_No' => $Pan_Number];

            $result = $collection->findOne($filter);
            print_r($result);
            if ($result) {
                $Pan_Number_error = "* Pan Number Already Exist";
            }
        }
    } else {
        $Pan_Number_error = "* Please Enter Pan Number";
    }



    // ********************************** Birth Date Validation *********************************************

    $today = new DateTime();
    $diff = $today->diff(new datetime($Birth_Date));
    $age = $diff->y;

    if ($age < 18) {

        $Birth_Date_error = "* You Are Not Eligible to Open Online Account.";
    }

    if (!is_numeric($Mobile_Number) || is_null($Mobile_Number) || !preg_match('/^[0-9]{10}+$/', $Mobile_Number)) {
        $Mobile_Number_error = "Invalid Mobile Number";
    }


    // **************************************** Citizenship Validation *******************************************************
    if (!is_numeric($Citizenship_Number) || is_null($Citizenship_Number) || !preg_match('/^[0-9]{12}+$/', $Citizenship_Number)) {
        $Citizenship_Number_error = "Invalid Citizenship Number";
    } else {

        // Citizenship Number Exist in database or not validation 
        $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Assuming 'onlinebanking' is your actual database name

        $collection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name
        
        $Citizenship_Number = $_POST['CitizenshipNumber']; // No need to escape for MongoDB
        
        $filter = ['C_Citizenship_No' => $Citizenship_Number];
        
        $result = $collection->countDocuments($filter);
        
        if ($result > 0) {
            $Citizenship_Number_error = "* Citizenship Number Already Exist";
        }
    }

    // ************************************************** Email Validation *********************************************


    if (!empty($Email)) {
        if (!preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $Email)) {
            $Email_error = "* Invalid Email ID";
        } else {
            $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Assuming 'onlinebanking' is your actual database name

            $collection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name
            
            $Email = $_POST['email']; // No need to escape for MongoDB
            
            $filter = ['C_Email' => $Email];
            
            $result = $collection->countDocuments($filter);
            
            if ($result > 0) {
                $Email_error = "* Email Already Exist";
            }
        }
    } else {
        $Email_error = "* Enter Your Email";
    }


    // ************************************************** Picode Validation *********************************************


    if (!empty($Pincode)) {
        $match = '/^[1-9]{1}[0-9]{2}\s{0,1}[0-9]{3}$/';
        if (!preg_match_all($match, $Pincode)) {
            $Pincode_error = "* Invalid Pincode";
        }
    } else {
        $Pincode_error = "* Enter Your Area Pincode";
    }





    // ++++++++++++++++++++++++++++++++++++++++++++++ Basic Detail Ends Here +++++++++++++++++++++++++++++++++++++++++




    // -------------------------------------------- USERNAME AND PASSWORD VERIFICATION -------------------------------


    $Username = $_POST['Username'];
    $Password  = $_POST['Password'];
    $ConfirmPass = $_POST['ConfirmPass'];

    $UsernameError =  $PasswordError  = $ConfirmPassError = false;

    if (!empty($Username)) {
        if (!preg_match_all('/^[A-Za-z]{1}[A-Za-z0-9]{5,31}$/', $Username)) {

            $UsernameError = "* Please Enter Valid Username";
        } else {
            $UsernameError = false;

            $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

            $collection = $mongoDB->login; // Assuming 'login' is your collection name

            $Username = $_POST['Username']; // No need to escape for MongoDB

            $filter = ['Username' => $Username];

            $result = $collection->countDocuments($filter);

            if ($result > 0) {
                $UsernameError = "* Username Already Exist";
            }
        }
    } else {
        $UsernameError = "* Username Cannot Empty";
    }

    // ----------------------------------------- Password Verification ---------------------------------------------
    if (!empty($Password)) {
        if (!preg_match_all('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{8,16}$/m', $Password)) {
            $PasswordError  = "* Password must contain Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character";
        } else {
            $hashPass = md5($Password);
            $PasswordError = false;
        }
    } else {
        $PasswordError = "Password Cannot be empty";
    }

    if (!empty($ConfirmPass)) {

        if ($ConfirmPass != $Password) {
            $ConfirmPassError = "Please Enter same Password";
        } else {
            $ConfirmPassError = false;
            unset($_SESSION['otp']);
            header('Location: ../user/login.php');
        }
    } else {
        $ConfirmPassError = "Please Confirm Password";
    }

    // --------------------------------------------------- Random Color Hex Generator for Profile ----------------------- 

    $hex = '#';

    //Create a loop.
    foreach (array('r', 'g', 'b') as $color) {
        //Random number between 0 and 255.
        $val = mt_rand(0, 255);
        //Convert the random number into a Hex value.
        $dechex = dechex($val);
        //Pad with a 0 if length is less than 2.
        if (strlen($dechex) < 2) {
            $dechex = "0" . $dechex;
        }
        //Concatenate
        $hex .= $dechex;
    }

    //Print out our random hex color.
    // echo $hex;

    // ----------------------------------------- KYC Document Upload Section -----------------------------------------


    // Storing Form values in variable

    // Pan Card Variable

    $Pan_Files = $_FILES['PanCardUp'];
    $Pan_fileName = $Pan_Files['name'];
    $Pan_fileName = preg_replace('/\s/', '_', $Pan_fileName); // replacing space with underscore
    $Pan_fileType = $Pan_Files['type'];
    $Pan_fileError = $Pan_Files['error'];
    $Pan_fileTempName = $Pan_Files['tmp_name'];
    $Pan_fileSize = $Pan_Files['size'];
    $Pan_Up_error = false;

    // Citizenship Card Variable
    $Citizenship_Files = $_FILES['CitizenshipCardUp'];
    $Citizenship_fileName = $Citizenship_Files['name'];
    $Citizenship_fileName = preg_replace('/\s/', '_', $Citizenship_fileName); // replacing space with underscore
    $Citizenship_fileType = $Citizenship_Files['type'];
    $Citizenship_fileError = $Citizenship_Files['error'];
    $Citizenship_fileTempName = $Citizenship_Files['tmp_name'];
    $Citizenship_fileSize = $Citizenship_Files['size'];
    $Citizenship_Up_error = false;

    // Array storing file extention global version
    $Valid_Extention = array('png', 'jpg', 'jpeg');



    // ************************************ Validating Pan Card Document **********************************************

    // use built in function ( pathinfo() ) to seprate file name and store them in seprate variable

    $Pan_file_extention = pathinfo($Pan_fileName, PATHINFO_EXTENSION);
    $Pan_fileName = pathinfo($Pan_fileName, PATHINFO_FILENAME);

    $Citizenship_file_extention = pathinfo($Citizenship_fileName, PATHINFO_EXTENSION);
    $Citizenship_fileName = pathinfo($Citizenship_fileName, PATHINFO_FILENAME);

    // Generating unique name with date and time 
    $Pan_Unique_Name = $Pan_fileName . date('mjYHis') . "." . $Pan_file_extention;
    $Citizenship_Unique_Name = $Citizenship_fileName . date('mjYHis') . "." . $Citizenship_file_extention;


    // Validating Pan Card


    if (!empty($Pan_fileName) && !empty($Citizenship_fileName)) {

        // Setting file size condition
        if ($Pan_fileSize <= 2000000 && $Citizenship_fileSize <= 2000000) {

            // checking file extention
            if (in_array($Pan_file_extention, $Valid_Extention) && in_array($Citizenship_file_extention, $Valid_Extention)) {

                // Pancard Destination Variable
                $Pan_destinationFile = 'customer_data/Pan_doc/' . $Pan_Unique_Name;


                // Citizenshipcard Destination Variable
                $Citizenship_destinationFile = 'customer_data/Citizenship_doc/' . $Citizenship_Unique_Name;





                // Validating All Error Are values are null or not means checking any error in form or not
                if ($First_Name_error == null && $Last_Name_error == null && $Father_Name_error == null && $Mother_Name_error == null && $Birth_Date_error == null && $Pan_Number_error == null && $Citizenship_Number_error == null && $Mobile_Number_error == null && $Pan_Up_error == false && $Citizenship_Up_error == false && $Email_error == null && $Pincode_error == null && $UsernameError == false && $PasswordError == false && $ConfirmPassError == false) {


                    // Uploading Document to server
                    $Citizenship_Upload = move_uploaded_file($Citizenship_fileTempName, $Citizenship_destinationFile);
                    $Pan_Upload = move_uploaded_file($Pan_fileTempName, $Pan_destinationFile);

                    // Pan And Citizenship is upload or not
                    if ($Pan_Upload && $Citizenship_Upload) {

                        try {
                            // mysql query for customer table
                            // $Upload_query = "INSERT INTO customer_detail(Account_No, C_First_Name, C_Last_Name, C_Father_Name, C_Mother_Name, C_Birth_Date, C_Citizenship_No, C_Pan_No, C_Mobile_No, C_Email, C_Pincode, C_Citizenship_Doc, C_Pan_Doc, Create_Date, ProfileColor, Gender, ProfileImage, Bio) VALUES('$Account_Number', '$First_Name', '$Last_Name', '$Father_Name', '$Mother_Name', '$Birth_Date', '$Citizenship_Number', '$Pan_Number', '$Mobile_Number', '$Email', '$Pincode', '$Citizenship_destinationFile', '$Pan_destinationFile', '$Create_Date' , '$hex', 'Not Available', '', '')";


                            // // sql query for login table
                            // $login_query = "INSERT INTO login(AccountNo, Username, Password, Status, State) VALUES('$Account_Number', '$Username', '$hashPass', '$Account_Status', '0')";

                            // // sql query for Accounts table
                            // $account_query = "INSERT INTO accounts(AccountNo, Balance, AccountType, SavingBalance, SavingTarget, State) VALUES('$Account_Number', '$Balance', '$Account_Type', '0.0', '', '0')";

                            // // query execution

                            // mysqli_query($conn, $Upload_query) or die(mysqli_error($conn));
                            // mysqli_query($conn, $login_query) or die(mysqli_error($conn));
                            // mysqli_query($conn, $account_query) or die(mysqli_error($conn));
                            $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

                            $customerCollection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name
                            $loginCollection = $mongoDB->login; // Assuming 'login' is your collection name
                            $accountsCollection = $mongoDB->accounts; // Assuming 'accounts' is your collection name
                            
                            // Define the document to be inserted into the customer_detail collection
                            $customerDocument = [
                                'Account_No' => $Account_Number,
                                'C_First_Name' => $First_Name,
                                'C_Last_Name' => $Last_Name,
                                'C_Father_Name' => $Father_Name,
                                'C_Mother_Name' => $Mother_Name,
                                'C_Birth_Date' => $Birth_Date,
                                'C_Citizenship_No' => $Citizenship_Number,
                                'C_Pan_No' => $Pan_Number,
                                'C_Mobile_No' => $Mobile_Number,
                                'C_Email' => $Email,
                                'C_Pincode' => $Pincode,
                                'C_Citizenship_Doc' => $Citizenship_destinationFile,
                                'C_Pan_Doc' => $Pan_destinationFile,
                                'Create_Date' => $Create_Date,
                                'ProfileColor' => $hex,
                                'Gender' => 'Not Available', // Assuming a default value
                                'ProfileImage' => '', // Assuming a default value
                                'Bio' => '', // Assuming a default value
                            ];
                            
                            // Define the document to be inserted into the login collection
                            $loginDocument = [
                                'AccountNo' => $Account_Number,
                                'Username' => $Username,
                                'Password' => $hashPass,
                                'Status' => $Account_Status,
                                'State' => '0', // Assuming a default value
                            ];
                            
                            // Define the document to be inserted into the accounts collection
                            $accountDocument = [
                                'AccountNo' => $Account_Number,
                                'Balance' => $Balance,
                                'AccountType' => $Account_Type,
                                'SavingBalance' => '0.0', // Assuming a default value
                                'SavingTarget' => '', // Assuming a default value
                                'State' => '0', // Assuming a default value
                            ];
                            
                            // Insert documents into collections
                            $customerCollection->insertOne($customerDocument);
                            $loginCollection->insertOne($loginDocument);
                            $accountsCollection->insertOne($accountDocument);
                            require '../mail/congraMail.php';
                            sendMessage($Email, $First_Name);
                        } catch (Exception $e) {
                            echo 'Message: ' . $e->getMessage();
                        }
                    }
                }
            } else {
                $Pan_Up_error = 'invalid file extention';
                $Citizenship_Up_error = 'invalid file extention';
                echo ('invalid file extention');
            }
        } else {
            echo "File is too large";
            $Pan_Up_error = 'File is too large';
            $Citizenship_Up_error = 'File is too large';
        }
    } else {
        echo " Please Give name to file";
        $Pan_Up_error = 'Please Give name to file';
        $Citizenship_Up_error = 'Please Give name to file';
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account O B S</title>

    <!-- Favicons -->
    <link href="../assets/img/favicon-32x32.png" rel="icon">
    <link href="../assets/img/apple-icon-180x180.png" rel="apple-touch-icon">

    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <!-- Project CSS -->
    <link rel="stylesheet" href="../assets/css/createAccount.css">


    <!-- Javascrip -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="../assets/js/createAc.js"></script>


</head>

<body>



    <form id="regForm" action="<?php htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
        <h1 class="mb-3">Create an Account:</h1>

        <!-- Tab 1 -->

        <div class="tab mb-3">
            <h3 class="mb-3 stepHead">Step 1/4</h3>
            <p class="SubAction">Personal Detail:</p>
            <div class="row g-2 mb-3">
                <div class="col-md">
                    <div class="form-floating">
                        <input type="text" name="FirstName" class="form-control" id="FirstName" placeholder="First Name">
                        <label for="floatingInputGrid">First Name</label>

                        <span id="FnameError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                        echo $First_Name_error;
                                                                    } ?></span>
                    </div>
                </div>
				<!-- div end -->
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">

                            <input type="text" name="LastName" class="form-control" id="Lname" placeholder="Last Name">
                            <label for="floatingInputGrid">Last Name</label>

                            <span id="LnameError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                            echo $Last_Name_error;
                                                                        } ?></span>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">

                            <input type="text" name="FatherName" class="form-control" id="FAname" placeholder="Father's Name">
                            <label for="floatingInputGrid">Father's Full Name</label>
                            <span id="FAnameError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                            echo $Father_Name_error;
                                                                        } ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">

                            <input type="text" name="MotherName" class="form-control" id="MAname" placeholder="Mother's Name">
                            <label for="floatingInputGrid">Mother's Full Name</label>
                            <span id="MAnameError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                            echo $Mother_Name_error;
                                                                        } ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="date" name="BirthDate" class="form-control" id="BirthDate" placeholder="Birth Date">
                            <label for="floatingInputGrid">Birth Date</label>
                            <span id="AgeError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                        echo $Birth_Date_error;
                                                                    } ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">
                            <input name="MobileNumber" class="form-control" type="tel" id="MobileNo" pattern="[0-9]{10}" placeholder="Mobile Number" onkeypress="return isNumber(event)">
                            <label for="floatingInputGrid">Mobile Number</label>
                            <span id="MobileNoError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                                echo $Mobile_Number_error;
                                                                            } ?></span>
                        </div>
                    </div>
                </div>
            </div>
			<!-- div end -->
            <div class="row g-2 mb-3">
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="text" name="PanNumber" class="form-control" id="PanNo" placeholder="Pan Number">
                            <label for="floatingInputGrid">Pan Number</label>
                            <span id="PanError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                        echo $Pan_Number_error;
                                                                    } ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">
                            <input name="CitizenshipNumber" class="form-control" type="tel" id="CitizenshipNo" pattern="[0-9]{12}" placeholder="Citizenship Number" onkeypress="return isNumber(event)">
                            <label for="floatingInputGrid">Citizenship Number</label>
                            <span id="CitizenshipError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                            echo $Citizenship_Number_error;
                                                                        } ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email Address">
                            <label for="floatingInputGrid">Email Address</label>
                            <span id="EmailError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                            echo $Email_error;
                                                                        } ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="col-md">
                        <div class="form-floating">
                            <input name="pincode" class="form-control" type="tel" id="pincode" pattern="[0-9]{6}" placeholder="Pin Code" onkeypress="return isNumber(event)">
                            <label for="floatingInputGrid">Pin Code</label>
                            <span id="PincodeError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                            echo $Pincode_error;
                                                                        } ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2 -->

        <div class="tab mb-3" id="KycTab">
            <h3 class="mb-3 stepHead">Step 2/4</h3>
            <p class="SubAction">Upload KYC Document</p>

            <div class="form-group mb-3">
                <label for="exampleFormControlFile1">PAN Card</label>
                <input type="file" name="PanCardUp" class="form-control-file" id="PANCardUp" size="30" accept="image/jpg,image/png,image/jpeg,image/gif">
                <span id="PanUPError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                echo $Pan_Up_error;
                                                            } ?></span>
            </div>
            <div class="form-groupmb-3">
                <label for="exampleFormControlFile1">Upload Citizenship Card</label>
                <input type="file" name="CitizenshipCardUp" class="form-control-file" id="CitizenshipCardUp" size="30" accept="image/jpg,image/png,image/jpeg,image/gif">
                <span id="CitizenshipUpError" style="color: red;"><?php if (isset($_POST['submit'])) {
                                                                echo $Citizenship_Up_error;
                                                            } ?></span>
            </div>
            <span id="mailsendError"></span>
        </div>

        <!-- Tab 3 -->

        <div class="tab">
            <h3 class="mb-3 stepHead">Step 3/4</h3>
            <p class="SubAction">Validate Email Account</p>

            <div class="col-md mb-3">
                <div class="col-md">
                    <div class="alert alert-success" role="alert">
                        OTP has been sent on your email <b> Hint: (OTP: 699600)</b>
                    </div>
                    <div class="form-floating OtpMobile">
                        <input type="tel" class="form-control" name="Otp" id="Otp" placeholder="Enter 6 Digit OTP (699600)" pattern="[0-9]{6}">
                        <label for="floatingInputGrid">Enter 6 Digit OTP (699600)</label>
                        <span style="color: red;" id="OtpError"></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tab 4 -->

        <div class="tab">
            <h3 class="mb-3 stepHead">Step 4/4</h3>
            <p class="SubAction">Create Username and Password</p>

            <div class="col-md mb-3">
                <div class="col-md">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="Username" id="Username" placeholder="Create Username">
                        <label for="floatingInputGrid">Create Username</label>

                        <span style="color: red;" id="UsernameError" name="UsernameError"><?php if (isset($_POST['submit'])) {
                                                                                                echo $UsernameError;
                                                                                            } ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md mb-3">
                <div class="col-md">
                    <div class="form-floating">
                        <input class="form-control" type="password" name="Password" id="Password" placeholder="Enter Password" data-toggle="tooltip" data-placement="top" title="Enter Password with atleast 8 charater long with 1 Capital 1 small 1 number and 1 special charater">
                        <label for="floatingInputGrid">Enter Password</label>

                        <span style="color: red;" id="PasswordError" name="PasswordError"><?php if (isset($_POST['submit'])) {
                                                                                                echo $PasswordError;
                                                                                            } ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md mb-3">
                <div class="col-md">
                    <div class="form-floating">
                        <input class="form-control" type="password" name="ConfirmPass" id="ConfirmPass" placeholder="Confirm Password">
                        <label for="floatingInputGrid">Confirm Password</label>

                        <span style="color: red;" id="ConfirmPassError" name="ConfirmPassError"><?php if (isset($_POST['submit'])) {
                                                                                                    echo $ConfirmPassError;
                                                                                                } ?></span>
                    </div>
                </div>
            </div>
        </div>

        </div>
        <div style="overflow:auto;">
            <div style="float:right;">
                <button type="button" id="prevBtn" class="btn btn-primary" onclick="nextPrev(-1)">Previous</button>
                <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextPrev(1)">Next</button>
                <input type="submit" name="submit" id="submitBtn" class="btn btn-primary" style="display: none;">
            </div>
        </div>
		<!-- div end -->
        <!-- Circles which indicates the steps of the form: -->
        <div style="text-align:center;margin-top:40px;">
            <span class="step"></span>
            <span class="step"></span>
            <span class="step"></span>
            <span class="step"></span>
        </div>
    </form>


    <script src="../assets/js/createAccount.js"></script>

    <!-- Vendor JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>


</body>

</html>