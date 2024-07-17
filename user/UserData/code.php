<?php
include "../connection.php";
// include "../mail/TransactionMail.php";
session_start();

$username = $_SESSION['username'];
// echo $username;
$AcNo = $_SESSION['AccountNo'];


// echo "run";

if (isset($_POST['BalanceCheck'])) {
    $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;
    $accountsCollection = $mongoDB->accounts;
    $transactionCollection = $mongoDB->transaction;
    
    $username = $_SESSION['username'];
    
    // Query to get customer details, including account balance
    $customerDetailResult = $loginCollection->findOne(['Username' => $username]);
    $AccountNo = $customerDetailResult['AccountNo'];
    
    $customerDetailResults = $accountsCollection->findOne(['AccountNo' => $AccountNo]);
    $Balance = $customerDetailResults['Balance'];
    $Saving = $customerDetailResults['SavingBalance'];
    
    // Query to get last month credit total
    $lastMonthCreditTotalCursor = $transactionCollection->aggregate([
        ['$match' => ['Date' => ['$gte' => new MongoDB\BSON\UTCDateTime(strtotime('first day of last month') * 1000), '$lt' => new MongoDB\BSON\UTCDateTime(strtotime('first day of this month') * 1000)], 'Status' => 'Credited', 'AccountNo' => $AccountNo]],
        ['$group' => ['_id' => null, 'totalCredit' => ['$sum' => '$Amount']]]
    ]);
    $lastMonthCreditTotal = 0;
    foreach ($lastMonthCreditTotalCursor as $row) {
        $lastMonthCreditTotal = $row['totalCredit'];
    }
    
    // Query to get last month debit total
    $lastMonthDebitTotalCursor = $transactionCollection->aggregate([
        ['$match' => ['Date' => ['$gte' => new MongoDB\BSON\UTCDateTime(strtotime('first day of last month') * 1000), '$lt' => new MongoDB\BSON\UTCDateTime(strtotime('first day of this month') * 1000)], 'Status' => 'Debited', 'AccountNo' => $AccountNo]],
        ['$group' => ['_id' => null, 'totalDebit' => ['$sum' => '$Amount']]]
    ]);
    $lastMonthDebitTotal = 0;
    foreach ($lastMonthDebitTotalCursor as $row) {
        $lastMonthDebitTotal = $row['totalDebit'];
    }
    
    // Query to get this month credit total
    $thisMonthCreditTotalCursor = $transactionCollection->aggregate([
        ['$match' => ['Date' => ['$gte' => new MongoDB\BSON\UTCDateTime(strtotime('first day of this month') * 1000), '$lt' => new MongoDB\BSON\UTCDateTime(strtotime('first day of next month') * 1000)], 'Status' => 'Credited', 'AccountNo' => $AccountNo]],
        ['$group' => ['_id' => null, 'totalCredit' => ['$sum' => '$Amount']]]
    ]);
    $thisMonthCreditTotal = 0;
    foreach ($thisMonthCreditTotalCursor as $row) {
        $thisMonthCreditTotal = $row['totalCredit'];
    }
    
    // Query to get this month debit total
    $thisMonthDebitTotalCursor = $transactionCollection->aggregate([
        ['$match' => ['Date' => ['$gte' => new MongoDB\BSON\UTCDateTime(strtotime('first day of this month') * 1000), '$lt' => new MongoDB\BSON\UTCDateTime(strtotime('first day of next month') * 1000)], 'Status' => 'Debited', 'AccountNo' => $AccountNo]],
        ['$group' => ['_id' => null, 'totalDebit' => ['$sum' => '$Amount']]]
    ]);
    $thisMonthDebitTotal = 0;
    foreach ($thisMonthDebitTotalCursor as $row) {
        $thisMonthDebitTotal = $row['totalDebit'];
    }
    
    $data = [
        'Balance' => $Balance,
        'Saving' => $Saving,
        'AccountNo' => $AccountNo,
        'CreditTotal' => $lastMonthCreditTotal,
        'DebitTotal' => $lastMonthDebitTotal,
        'CreditThisMonth' => $thisMonthCreditTotal,
        'DebitThisMonth' => $thisMonthDebitTotal
    ];
    
    echo json_encode($data);
}


// Checking Status 
if (isset($_POST['AcState'])) {
    $AccountNo = $_POST['AcState'];
    $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;
    
    // Query to get the status from customer_detail collection
    $customerDetailResult = $customerDetailCollection->findOne(['Account_No' => $AccountNo]);
    $customerDetailResults = $loginCollection->findOne(['AccountNo' => $AccountNo]);
    if ($customerDetailResults) {
        $Status = $customerDetailResults['Status'];
        echo $Status;
    } else {
        echo "fail";
    }
}

// Sending Money to receiver
if (isset($_POST['DepositAc'])) {
    //use MongoDB\BSON\UTCDateTime;

    $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name
    
    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;
    $accountsCollection = $mongoDB->accounts;
    $transactionCollection = $mongoDB->transaction;
    
    $ReceiverAc = $_POST['DepositAc'];
    $Amount = $_POST['MainAmount'];
    $SenderAc = $AcNo;
    
    if ($Amount > 0) {
        if ($SenderAc == $ReceiverAc) {
            echo "Can't Transfer Money to the same account";
        } else {
            $senderDetails = $accountsCollection->findOne(['AccountNo' => $SenderAc]);
            $receiverDetails = $accountsCollection->findOne(['AccountNo' => $ReceiverAc]);
            $senderloginDetails = $loginCollection->findOne(['AccountNo' => $SenderAc]);
            $receiverloginDetails = $loginCollection->findOne(['AccountNo' => $ReceiverAc]);
            $senderDetailsc = $customerDetailCollection->findOne(['Account_No' => $SenderAc]);
            $receiverDetailsc = $customerDetailCollection->findOne(['Account_No' => $ReceiverAc]);
            if ($senderDetails && $receiverDetails) {
                $SBalance = $senderDetails['Balance'];
                $RBalance = $receiverDetails['Balance'];
    
                if ($SBalance != "0") {
                    if ($SBalance >= $Amount) {
                        if ($senderloginDetails['Status'] == "Active" && $receiverloginDetails['Status'] == "Active") {
                            $SName = $senderDetailsc['C_First_Name'];
                            $SLName = $senderDetailsc['C_Last_Name'];
                            $RName = $receiverDetailsc['C_First_Name'];
                            $RLName = $receiverDetailsc['C_Last_Name'];
                            $SProColor = $senderDetailsc['ProfileColor'];
                            $RProColor = $receiverDetailsc['ProfileColor'];
    
                            $Rtotal = $Amount + $RBalance;
                            $Stotal = $SBalance - $Amount;
                            $SenderName = $SName . " " . $SLName;
                            $ReceiverName = $RName . " " . $RLName;
                            $DebitAmount = -$Amount;
    
                            try {
                                $transactionCollection->insertOne([
                                    'AccountNo' => $ReceiverAc,
                                    'FAccountNo' => $SenderAc,
                                    'Name' => $SenderName,
                                    'Amount' => $Amount,
                                    'Status' => 'Credited',
                                    'ProfileColor' => $SProColor,
                                    'Credit' => $Amount,
                                    'Debit' => 0.0,
                                    'Date' => new MongoDB\BSON\UTCDateTime(strtotime('now') * 1000)
                                ]);
    
                                $transactionCollection->insertOne([
                                    'AccountNo' => $SenderAc,
                                    'FAccountNo' => $ReceiverAc,
                                    'Name' => $ReceiverName,
                                    'Amount' => $DebitAmount,
                                    'Status' => 'Debited',
                                    'ProfileColor' => $RProColor,
                                    'Credit' => 0.0,
                                    'Debit' => $Amount,
                                    'Date' => new MongoDB\BSON\UTCDateTime(strtotime('now') * 1000)
                                ]);
    
                                $accountsCollection->updateOne(
                                    ['AccountNo' => $ReceiverAc],
                                    ['$set' => ['Balance' => $Rtotal]]
                                );
    
                                $accountsCollection->updateOne(
                                    ['AccountNo' => $SenderAc],
                                    ['$set' => ['Balance' => $Stotal]]
                                );
    
                                echo "Success";
                            } catch (Throwable $th) {
                                echo "fail";
                            }
                        } else {
                            echo "Transaction Fail: Sender or Receiver account is not active";
                        }
                    } else {
                        echo "Transaction Fail: Sender account does not have sufficient balance";
                    }
                } else {
                    echo "Transaction Fail: Sender account does not have any balance";
                }
            } else {
                echo "Transaction Fail: Sender or Receiver account not found";
            }
        }
    } else {
        echo "Invalid amount";
    }
}

// ------------------------------------------------------- SAVING SECTION ------------------------------------

// fetching Basic Details

if (isset($_POST['BasicDetail'])) {
    $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;
    $accountsCollection = $mongoDB->accounts;
    
    $query = ['AccountNo' => $AcNo];
    
    $customerDetailResult = $customerDetailCollection->findOne($query);
    $accountsCollectionResult = $accountsCollection->findOne($query);
    if ($accountsCollectionResult) {
        $SavingBalance = $accountsCollectionResult['SavingBalance'];
        $SavingTarget = $accountsCollectionResult['SavingTarget'];
    
        $data = [
            'SavingBalance' => $SavingBalance,
            'SavingTarget' => $SavingTarget
        ];
    
        echo json_encode($data);
    } else {
        echo "No Data Found";
    }
}


// Add Amount To saving 

if (isset($_POST['Amount'])) {

    $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;
    $accountsCollection = $mongoDB->accounts;
    
    $Amount = $_POST['Amount'];
    $regex = '/^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/';
    
    if (preg_match_all($regex, $Amount)) {
        if ($Amount > 0) {
            $customerDetailResult = $customerDetailCollection->findOne(['Account_No' => $AcNo]);
            $accountsCollectionResult = $accountsCollection->findOne(['AccountNo' => $AcNo]);
            $loginCollectionResult = $loginCollection->findOne(['AccountNo' => $AcNo]);
            if ($accountsCollectionResult) {
                $Status = $loginCollectionResult['Status'];
                $Balance = $accountsCollectionResult['Balance'];
                $SavingBalance = isset($accountsCollectionResult['SavingBalance']) ? $accountsCollectionResult['SavingBalance'] : 0.0;
    
                if ($Status == "Active") {
                    if ($Balance > $Amount) {
                        $totalSaving = (float)$Amount + (float)$SavingBalance;
                        $total = (float)$Balance - (float)$Amount;
    
                        try {
                            $accountsCollection->updateOne(
                                ['AccountNo' => $AcNo],
                                ['$set' => ['Balance' => $total, 'SavingBalance' => $totalSaving]]
                            );
    
                            echo "success";
                        } catch (Throwable $th) {
                            echo "fail";
                        }
                    } else {
                        echo "Insufficient Account Balance";
                    }
                } else {
                    echo "Account is not Activated";
                }
            } else {
                echo "Server Error, Please Try After Some Time";
            }
        } else {
            echo "Please Enter Minimum Amount $1";
        }
    } else {
        echo "Please Enter Amount In Numbers";
    }
}

$mongoDB = $mongoClient->selectDatabase('onlinebanking');
// Set New Saving Target
if (isset($_POST['SavingTarget'])) {
     // Replace 'onlinebanking' with your actual database name

    $accountsCollection = $mongoDB->accounts;
    
    $Amount = $_POST['SavingTarget'];
    $regex = '/^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/';
    
    if (preg_match_all($regex, $Amount)) {
        if ($Amount >= 1000) {
            $updateResult = $accountsCollection->updateOne(
                ['AccountNo' => $AcNo],
                ['$set' => ['SavingTarget' => $Amount]]
            );
    
            if ($updateResult->getModifiedCount() > 0) {
                echo "success";
            } else {
                echo "Server Error, Please Try Again!";
            }
        } else {
            echo "Set Target Greater Than or equals to $1000";
        }
    } else {
        echo "Please Enter Amount In Numbers";
    }
}


// Transfer Saving to main account

$collection = $mongoDB->selectCollection("accounts");

$customerDetailCollection = $mongoDB->customer_detail;
$loginCollection = $mongoDB->login;
$customerCollectionResult = $customerDetailCollection->findOne(['Account_No' => $AcNo]);
$loginCollectionResult = $loginCollection->findOne(['AccountNo' => $AcNo]);
if (isset($_POST['TransferBalance'])) {
    $Amount = $_POST['TransferBalance'];

    // Validate the amount
    $regex = '/^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/';
    if (preg_match_all($regex, $Amount)) {
        $Amount = (float)$Amount;

        if ($Amount > 0) {
            // Query MongoDB to find the account by account number
            $query = ['AccountNo' => $AcNo];
            $account = $collection->findOne($query);

            if ($account) {
                $Status = $loginCollectionResult['Status'];
                $Balance = $account['Balance'];
                $SavingBalance = $account['SavingBalance'];

                if ($Status == "Active") {
                    if ($SavingBalance >= $Amount) {
                        $totalSaving = (float)$SavingBalance - $Amount;
                        $total = (float)$Balance + $Amount;

                        // Update the account balances in MongoDB
                        $updateResult = $collection->updateOne(
                            $query,
                            ['$set' => ['Balance' => $total, 'SavingBalance' => $totalSaving]]
                        );

                        if ($updateResult->getModifiedCount() > 0) {
                            echo "success";
                        } else {
                            echo "Server Error, Please Try Again!";
                        }
                    } else {
                        echo "Insufficient Saving Balance";
                    }
                } else {
                    echo "Account is not Activated";
                }
            } else {
                echo "Account not found";
            }
        } else {
            echo "Please Enter Minimum Amount $1";
        }
    } else {
        echo "Please Enter Amount In Numbers";
    }
}

// --------------------------------------------------- Profile Section ------------------------------------------------

$collection = $mongoDB->selectCollection("customer_detail"); // Assuming you have a collection named "customer_details" in your MongoDB
$loginCollection = $mongoDB->login;
$accountsCollection = $mongoDB->accounts;
$loginCollectionResult = $loginCollection->findOne(['AccountNo' => $AcNo]);
$accountsCollectionResult = $accountsCollection->findOne(['AccountNo' => $AcNo]);
if (isset($_POST['profileDetail'])) {
    $query = ['Account_No' => $AcNo];
    $result = $collection->findOne($query);

    if ($result) {
        $FName = $result['C_First_Name'];
        $LName = $result['C_Last_Name'];
        $ProfileColor = $result['ProfileColor'];
        $ProfileImage = $result['ProfileImage'];
        $Balance = $accountsCollectionResult['Balance'];
        $SavingBalance = $accountsCollectionResult['SavingBalance'];
        $AccountNo = $result['Account_No'];
        $CitizenshipNo = $result['C_Citizenship_No'];
        $PanNo = $result['C_Pan_No'];
        $MobileNo = $result['C_Mobile_No'];
        $Email = $result['C_Email'];
        $Status = $loginCollectionResult['Status'];
        $AccountType = $accountsCollectionResult['AccountType'];
        $Bio = $result['Bio'];
        $Gender = $result['Gender'];

        $TagName = substr($FName, 0, 1);

        $data = array(
            'FName' => $FName,
            'LName' => $LName,
            'ProfileColor' => $ProfileColor,
            'ProfileImage' => $ProfileImage,
            'TagName' =>  $TagName,
            'Balance' =>  $Balance,
            'SavingBalance' => $SavingBalance,
            'AccountNo' =>  $AccountNo,
            'CitizenshipNo' => $CitizenshipNo,
            'PanNo' =>  $PanNo,
            'MobileNo' => $MobileNo,
            'Email' => $Email,
            'Status' => $Status,
            'AccountType' => $AccountType,
            'Bio' => $Bio,
            'Gender' => $Gender
        );

        echo json_encode($data);
    } else {
        echo "No Data Found";
    }
}

// Edit Profile

$collection = $mongoDB->selectCollection("customer_detail"); // Assuming you have a collection named "customer_details" in your MongoDB

if (isset($_POST['submit'])) {
    if (!isset($_POST['gender'])) {
        $gender = "null";
    } else {
        $gender = $_POST['gender'];
    }

    $bio = $_POST['bio'];

    // File Variables
    $Files = $_FILES['upload'];
    $fileName = $Files['name'];
    $fileName = preg_replace('/\s/', '_', $fileName); // replacing space with underscore
    $fileType = $Files['type'];
    $fileError = $Files['error'];
    $fileTempName = $Files['tmp_name'];
    $fileSize = $Files['size'];
    $Up_error = false;

    $Valid_Extention = array('png', 'jpg', 'jpeg');

    // use built-in function ( pathinfo() ) to separate file name and store them in separate variable

    $file_extention = pathinfo($fileName, PATHINFO_EXTENSION);
    $file_Name = pathinfo($fileName, PATHINFO_FILENAME);

    // Generating unique name with date and time 
    $Unique_Name = $file_Name . $username . "." . $file_extention;

    // Update MongoDB document
    $updateResult = $collection->updateOne(
        ['Account_No' => $AcNo],
        ['$set' => ['Gender' => $gender, 'Bio' => $bio]]
    );

    if (!empty($file_Name)) {
        // Setting file size condition
        if ($fileSize <= 8000000) {
            // checking file extension
            if (in_array($file_extention, $Valid_Extention)) {
                // Destination Variable
                $destinationFile = '../customer_data/Profile_Img/' . $Unique_Name;

                // Move uploaded file to destination
                $Pan_Upload = move_uploaded_file($fileTempName, $destinationFile);

                // Update MongoDB document with the profile image path
                $updateResult = $collection->updateOne(
                    ['Account_No' => $AcNo],
                    ['$set' => ['ProfileImage' => $destinationFile]]
                );
            } else {
                echo $Up_error = 'invalid file extension';
            }
        } else {
            echo $Up_error = 'File is too large';
        }
    }

    // Redirect to profile.php after processing
    header("Location: profile.php");
}
// ------------------------------------------------------- Profile Info Js ---------------------------------------

$collection = $mongoDB->selectCollection("customer_detail"); // Assuming you have a collection named "customer_details" in your MongoDB

if (isset($_POST['ProfileData'])) {
    $document = $collection->findOne(['Account_No' => $AcNo]);

    if ($document) {
        $ProfileImage = $document['ProfileImage'];
        echo $ProfileImage;
    } else {
        echo "No Data Found";
    }
}

// --------------------------------------------------- Secure Account ----------------------------------------

$collection = $mongoDB->selectCollection("login"); // Assuming you have a collection named "login" in your MongoDB

if (isset($_POST['NewUsernameCheck'])) {
    $NewUsername = $_POST['NewUsernameCheck'];

    $document = $collection->findOne(['Username' => $NewUsername]);

    if ($document) {
        echo "pass";
    } else {
        echo "fail";
    }
}

$collection = $mongoDB->selectCollection("login"); // Assuming you have a collection named "login" in your MongoDB

if (isset($_POST['UpdateNewUsername'])) {
    $NewUsername = $_POST['UpdateNewUsername'];
    $OldUsername = $_POST['OldUsername'];
    $Password = $_POST['UserPassword'];

    $HashPassword = md5($Password);

    if (preg_match('/^[A-Za-z]{1}[A-Za-z0-9]{5,31}$/', $NewUsername)) {

        // Perform the query to find the document with the old username and password
        $document = $collection->findOne([
            'Username' => $OldUsername,
            'Password' => $HashPassword,
            'AccountNo' => $AcNo
        ]);

        if ($document) {
            // Update the document with the new username
            $updateResult = $collection->updateOne(
                ['AccountNo' => $AcNo],
                ['$set' => ['Username' => $NewUsername]]
            );

            if ($updateResult->getModifiedCount() > 0) {
                echo "success";
            } else {
                echo "Update failed";
            }
        } else {
            echo "Invalid Username and Password";
        }
    } else {
        echo "Invalid Username Format";
    }
}

// ------------------------------------------- Change Password -------------------------------------------------

$collection = $mongoDB->selectCollection("login"); // Assuming you have a collection named "login" in your MongoDB

if (isset($_POST['PasswordCheck'])) {
    $Password = $_POST['PasswordCheck'];
    $HashPassword = md5($Password);

    // Perform the query to find the document with the specified hashed password and account number
    $document = $collection->findOne([
        'Password' => $HashPassword,
        'AccountNo' => $AcNo
    ]);

    if ($document) {
        echo "success";
    } else {
        echo "Invalid Username and Password";
    }
}

$collection = $mongoDB->selectCollection("login"); // Assuming you have a collection named "login" in your MongoDB

if (isset($_POST['UpdatePass'])) {
    $Password = $_POST['UpdatePass'];
    $HashPassword = md5($Password);

    if (preg_match_all('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.* )(?=.*[^a-zA-Z0-9]).{8,16}$/m', $Password)) {

        // Perform the update query to update the password for the specified account number
        $updateResult = $collection->updateOne(
            ['AccountNo' => $AcNo],
            ['$set' => ['Password' => $HashPassword]]
        );

        if ($updateResult->getModifiedCount() > 0) {
            echo "success";
        } else {
            echo "Password Not Changed";
        }
    } else {
        echo "Invalid Password Format";
    }
}

$collection = $mongoDB->selectCollection("login"); // Assuming you have a collection named "login" in your MongoDB

if (isset($_POST['UpdateStatus'])) {
    $UpdateStatus = $_POST['UpdateStatus'];

    // Perform the update query to update the status for the specified account number
    $updateResult = $collection->updateOne(
        ['AccountNo' => $AcNo],
        ['$set' => ['Status' => $UpdateStatus]]
    );

    if ($updateResult->getModifiedCount() > 0) {
        echo "success";
    } else {
        echo "Status Not Changed";
    }
}

$collection = $mongoDB->selectCollection("login"); // Assuming you have a collection named "login" in your MongoDB

if (isset($_POST['Switch'])) {
    // Fetch the account document based on the AccountNo
    $accountDocument = $collection->findOne(['AccountNo' => $AcNo]);

    if ($accountDocument) {
        // Extract the status from the document
        $status = $accountDocument['Status'];
        echo $status;
    } else {
        echo "Account Not Found";
    }
}

// --------------------------------------------------- DebitCard Application -----------------------------------------

if (isset($_POST['DebitCardApp'])) {
    // Assuming $AcNo contains the account number

    // Fetch customer details
    $query = ['Account_No' => $AcNo];
    $customer_detail = $mongoDB->selectCollection("customer_detail");
    $result = $customer_detail->findOne($query);

    if (!empty($result)) {
        // Extract first name and last name from the customer details
        $FName = $result['C_First_Name'];
        $LName = $result['C_Last_Name'];
        $Name = strtoupper($FName . " " . $LName);

        // Generate card number components
        $sufix = substr($AcNo, 0, 2);
        $prefix = substr($AcNo, 2, 2);

        // Generate a unique card number based on the current date and time
        $DebitCard_No = $prefix . date('ndyHisL') . $sufix;

        // Generate a random CVV
        $cvv = strval(rand(100, 999));

        // Insert card details into the MongoDB collection
        $cardsCollection = $mongoDB->selectCollection("cards");
        $cardDocument = [
            'AccountNo' => $AcNo,
            'Name' => $Name,
            'CardNo' => $DebitCard_No,
            'cvv' => $cvv,
            'Status' => 'Inactive',
            'Verified' => 'No',
            'ExpiryDate' => '',
            'IssuedDate' => ''
            // You can add more fields here as needed
        ];
        $insertResult = $cardsCollection->insertOne($cardDocument);

        if ($insertResult->getInsertedCount() > 0) {
            echo "success";
        } else {
            echo "fail";
        }
    } else {
        echo "fail";
    }
}