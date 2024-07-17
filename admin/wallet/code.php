<?php

include "../connection.php";
// include "../mail/TransactionMail.php";

$mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

// Check if 'AcNo' is set in the POST request
if (isset($_POST['AcNo'])) {
    $accountNo = $_POST['AcNo'];

    // Define the MongoDB collections
    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;
    $accountsCollection = $mongoDB->accounts;

    // Query the MongoDB collections
    $document = $customerDetailCollection->findOne(['Account_No' => $accountNo]);

    if ($document) {
        // Extract relevant fields from the document
        $Fname = $document['C_First_Name'];
        $Lname = $document['C_Last_Name'];
        $CitizenshipNo = $document['C_Citizenship_No'];
        $PanNo = $document['C_Pan_No'];
        $MobileNo = $document['C_Mobile_No'];

        // Query the 'login' collection to get the status
        $loginDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);
        $Status = $loginDocument ? $loginDocument['Status'] : null;

        // Query the 'accounts' collection to get the balance
        $accountsDocument = $accountsCollection->findOne(['AccountNo' => $accountNo]);
        $Balance = $accountsDocument ? $accountsDocument['Balance'] : null;

        // Prepare the response data
        $flag = "success";
        $data = array(
            'Flag' => $flag,
            'Fname' => $Fname,
            'Lname' => $Lname,
            'CitizenshipNo' => $CitizenshipNo,
            'PanNo' => $PanNo,
            'MobileNo' => $MobileNo,
            'Balance' => $Balance,
            'Status' => $Status
        );

        echo json_encode($data);
    } else {
        // If no document found, return fail
        $flag = "fail";
        $data = array(
            'Flag' => $flag
        );

        echo json_encode($data);
    }
}

// Check if 'AcState' is set in the POST request
if (isset($_POST['AcState'])) {
    $accountNo = $_POST['AcState'];

    // Define the MongoDB collections
    $customerDetailCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;

    // Perform the aggregation query to find the status of the account
    $pipeline = [
        ['$match' => ['Account_No' => $accountNo]], // Filter documents based on the account number
        ['$lookup' => [ // Perform a join with the 'login' collection
            'from' => 'login',
            'localField' => 'Account_No',
            'foreignField' => 'AccountNo',
            'as' => 'loginData'
        ]],
        ['$unwind' => '$loginData'], // Unwind the resulting array
        ['$project' => ['Status' => '$loginData.Status']] // Project only the 'Status' field
    ];

    $result = $customerDetailCollection->aggregate($pipeline)->toArray();

    if (!empty($result)) {
        // If the result is not empty, extract the 'Status' field value
        $status = $result[0]['Status'];
        echo $status;
    } else {
        // If no document found, echo "fail"
        echo "fail";
    }
}

// Check if 'DepositAc' and 'MainAmount' are set in the POST request
if (isset($_POST['DepositAc']) && isset($_POST['MainAmount'])) {
    $accountNo = $_POST['DepositAc'];
    $amount = floatval($_POST['MainAmount']); // Convert amount to float

    if ($amount >= 100) {
        // Find the account document based on the account number
        $accountCollection = $mongoDB->accounts;
        $accountDocument = $accountCollection->findOne(['AccountNo' => $accountNo]);

        // Find the login document based on the account number
        $loginCollection = $mongoDB->login;
        $loginDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);
        $currentDate = new MongoDB\BSON\UTCDateTime();
        if ($accountDocument && $loginDocument) {
            // Extract required fields from the documents
            $status = $loginDocument['Status'];
            $balance = floatval($accountDocument['Balance']); // Convert balance to float

            if ($status == "Active") {
                // Calculate the new total balance
                $total = $balance + $amount;
                $senderName = "BANKING SYSTEM";

                // Start a session to perform the transaction
                $session = $mongoClient->startSession();

                try {
                    // Start a transaction
                    $session->startTransaction();

                    // Update the account balance
                    $accountCollection->updateOne(['AccountNo' => $accountNo], ['$set' => ['Balance' => $total]]);

                    // Insert a transaction record
                    $transactionCollection = $mongoDB->transaction;
                    $transactionData = [
                        'AccountNo' => $accountNo,
                        'FAccountNo' => 'NA',
                        'Name' => $senderName,
                        'Amount' => $amount,
                        'Status' => 'Credited',
                        'ProfileColor' => 'blue',
                        'Credit' => $amount,
                        'Debit' => 0.0,
                        'Date' => $currentDate
                    ];
                    $transactionCollection->insertOne($transactionData);

                    // Commit the transaction
                    $session->commitTransaction();
                    echo "Success";
                } catch (Exception $e) {
                    // Rollback the transaction on failure
                    $session->abortTransaction();
                    echo "fail";
                } finally {
                    // End the session
                    $session->endSession();
                }
            } else {
                echo "Transaction Fail Account Not Active";
            }
        } else {
            echo "Transaction Fail Account Not Found";
        }
    } else {
        echo "Transaction Fail minimum amount required 100 rs";
    }
}
// ------------------------------------------- Withdraw Money Code -------------------------------------------------

// Check if 'WAcNo' is set in the POST request
if (isset($_POST['WAcNo'])) {
    $accountNo = $_POST['WAcNo'];

    // Find the account document based on the account number
    $customerCollection = $mongoDB->customer_detail;
    $accountDocument = $customerCollection->findOne(['Account_No' => $accountNo]);

    // Check if the account document exists
    if ($accountDocument) {
        // Extract required fields from the document
        $fname = $accountDocument['C_First_Name'];
        $lname = $accountDocument['C_Last_Name'];
        $citizenshipNo = $accountDocument['C_Citizenship_No'];
        $panNo = $accountDocument['C_Pan_No'];
        $mobileNo = $accountDocument['C_Mobile_No'];
        
        // Find the login document based on the account number
        $loginCollection = $mongoDB->login;
        $loginDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);

        // Check if the login document exists
        if ($loginDocument) {
            $balance = $loginDocument['Balance'];
            $status = $loginDocument['Status'];

            $flag = "success";
            $data = [
                'Flag' => $flag,
                'Fname' => $fname,
                'Lname' => $lname,
                'CitizenshipNo' => $citizenshipNo,
                'PanNo' => $panNo,
                'MobileNo' => $mobileNo,
                'Balance' => $balance,
                'Status' => $status
            ];
        } else {
            $flag = "fail";
            $data = ['Flag' => $flag];
        }
    } else {
        $flag = "fail";
        $data = ['Flag' => $flag];
    }

    echo json_encode($data);
}

// Check if 'WAcState' is set in the POST request
if (isset($_POST['WAcState'])) {
    $accountNo = $_POST['WAcState'];

    // Find the login document based on the account number
    $loginCollection = $mongoDB->login;
    $loginDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);

    // Check if the login document exists
    if ($loginDocument) {
        $status = $loginDocument['Status'];
        echo $status;
    } else {
        echo "fail";
    }
}




// Check if 'WDepositAc' is set in the POST request
if (isset($_POST['WDepositAc'])) {
    $accountNo = $_POST['WDepositAc'];
    $amount = $_POST['WMainAmount'];

    if ($amount >= 100) {
        // Find the customer document based on the account number
        $customerCollection = $mongoDB->customer_detail;
        $customerDocument = $customerCollection->findOne(['Account_No' => $accountNo]);
        $loginCollection = $mongoDB->login;
        $loginDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);
        $accountCollection = $mongoDB->accounts;
        $accountDocument = $accountCollection->findOne(['AccountNo' => $accountNo]);
        $currentDate = new MongoDB\BSON\UTCDateTime();
        // Check if the customer document exists
        if ($customerDocument) {
            $status = $loginDocument['Status'];
            $balance = $accountDocument['Balance'];
            $name = $customerDocument['C_First_Name'];
            $lname = $customerDocument['C_Last_Name'];
            $email = $customerDocument['C_Email'];

            if ($balance != 0) {
                if ($status == "Active") {
                    // Calculate the new balance
                    $newBalance = $balance - $amount;

                    // Perform the transaction
                    try {
                        // Start a MongoDB transaction
                        $session = $mongoClient->startSession();

                        // Start a transaction
                        $session->startTransaction();

                        // Update the balance in the customer document
                        $accountCollection->updateOne(
                            ['AccountNo' => $accountNo],
                            ['$set' => ['Balance' => $newBalance]]
                        );

                        // Insert the transaction record
                        $transactionCollection = $mongoDB->transaction;
                        $transactionCollection->insertOne([
                            'AccountNo' => $accountNo,
                            'FAccountNo' => 'NA',
                            'Name' => 'BANKING SYSTEM',
                            'Amount' => -$amount,
                            'Status' => 'Debited',
                            'ProfileColor' => 'blue',
                            'Credit' => 0.0,
                            'Debit' => $amount,
                            'Date' => $currentDate
                        ]);

                        // Commit the transaction
                        $session->commitTransaction();

                        // End the session
                        $session->endSession();

                        echo "Success";
                    } catch (Exception $e) {
                        // Abort the transaction if an error occurs
                        $session->abortTransaction();
                        echo "fail";
                    }
                } else {
                    echo "Transaction Fail Account Not Active";
                }
            } else {
                echo "Insufficient Balance In Your Account!";
            }
        }
    } else {
        echo "Transaction Fail minimum amount required 100 rs";
    }
}