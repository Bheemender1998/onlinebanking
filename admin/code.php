<?php
include "../admin/connection.php";
// include "../mail/TransactionMail.php";

$mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

// Check if 'CAccountNo' is set in the POST request
if (isset($_POST['CAccountNo'])) {
    $accountNo = $_POST['CAccountNo'];

    // Query MongoDB to find the document with the matching 'Account_No'
    $customerCollection = $mongoDB->customer_detail;
    $customerDocument = $customerCollection->findOne(['Account_No' => $accountNo]);

    // Check if a document was found
    if ($customerDocument) {
        // Extract data from the document
        $Fname = $customerDocument['C_First_Name'];
        $Lname = $customerDocument['C_Last_Name'];
        $Faname = $customerDocument['C_Father_Name'];
        $Maname = $customerDocument['C_Mother_Name'];
        $Bdate = $customerDocument['C_Birth_Date'];
        $CitizenshipNo = $customerDocument['C_Citizenship_No'];
        $PanNo = $customerDocument['C_Pan_No'];
        $MobileNo = $customerDocument['C_Mobile_No'];
        $Email = $customerDocument['C_Email'];
        $Pincode = $customerDocument['C_Pincode'];
        $CitizenshipDoc = $customerDocument['C_Citizenship_Doc'];
        $PanDoc = $customerDocument['C_Pan_Doc'];

        // Prepare the data to be sent as JSON response
        $data = array(
            'Fname' => $Fname,
            'Lname' => $Lname,
            'Faname' => $Faname,
            'Maname' => $Maname,
            'Bdate' => $Bdate,
            'CitizenshipNo' => $CitizenshipNo,
            'PanNo' => $PanNo,
            'MobileNo' => $MobileNo,
            'Email' => $Email,
            'Pincode' => $Pincode,
            'CitizenshipDoc' => $CitizenshipDoc,
            'PanDoc' => $PanDoc
        );

        // Send the data as JSON response
        echo json_encode($data);
    }
}

// Verify / Verify Account Code

// Check if 'VerifyAc' is set in the POST request
if (isset($_POST['VerifyAc'])) {
    $accountNo = $_POST['VerifyAc'];

    // Update the status of the document in the 'login' collection to 'Active'
    $loginCollection = $mongoDB->login;
    $updateResult = $loginCollection->updateOne(['AccountNo' => $accountNo], ['$set' => ['Status' => 'Active']]);

    if ($updateResult->getModifiedCount() > 0) {
        echo "Success";
    } else {
        echo "Failed to update status";
    }
}

// Check if 'RejectAc' is set in the POST request
if (isset($_POST['RejectAc'])) {
    $accountNo = $_POST['RejectAc'];

    // Delete the documents associated with the account number from the collections
    $customerDetailCollection = $mongoDB->customer_detail;
    $deleteResult1 = $customerDetailCollection->deleteOne(['Account_No' => $accountNo]);

    $loginCollection = $mongoDB->login;
    $deleteResult2 = $loginCollection->deleteOne(['AccountNo' => $accountNo]);

    $accountsCollection = $mongoDB->accounts;
    $deleteResult3 = $accountsCollection->deleteOne(['AccountNo' => $accountNo]);

    // Check if any document was deleted
    if ($deleteResult1->getDeletedCount() > 0 && $deleteResult2->getDeletedCount() > 0 && $deleteResult3->getDeletedCount() > 0) {
        echo "Success";
    } else {
        echo "Failed to delete documents";
    }
}

// verify/ reset id code

if (isset($_POST['done'])) {

    $resetQuery = "SET @autoid := 0;
        UPDATE accounts SET ID = @autoid := (@autoid+1);
        ALTER TABLE accounts AUTO_INCREMENT = 1;
        SET @autoid := 0;
        UPDATE customer_detail SET C_No = @autoid := (@autoid+1);
        ALTER TABLE customer_detail AUTO_INCREMENT = 1;
        SET @autoid := 0;
        UPDATE login SET ID = @autoid := (@autoid+1);
        ALTER TABLE login AUTO_INCREMENT = 1";
    mysqli_multi_query($conn, $resetQuery) or die(mysqli_error($conn));
}



// Debit Cards

// Check Debit card Code

// Check if 'DebitCardCheck' is set in the POST request
if (isset($_POST['DebitCardCheck'])) {
    $accountNo = $_POST['DebitCardCheck'];
    $issuedDate = date('d/m/y');
    $expiryDate = date('m/y', strtotime('+10 years'));

    // Update the card document in MongoDB
    $cardsCollection = $mongoDB->cards;
    $updateResult = $cardsCollection->updateOne(
        ['AccountNo' => $accountNo],
        ['$set' => [
            'Status' => 'Active',
            'IssuedDate' => $issuedDate,
            'ExpiryDate' => $expiryDate,
            'Verified' => 'Yes'
        ]]
    );

    if ($updateResult->getModifiedCount() > 0) {
        echo "Success";
    } else {
        echo "Fail"; // Or handle the case where no document was updated
    }
}

// Check if 'DebitCardReject' is set in the POST request
if (isset($_POST['DebitCardReject'])) {
    $accountNo = $_POST['DebitCardReject'];

    // Delete the card document from MongoDB
    $cardsCollection = $mongoDB->cards;
    $deleteResult = $cardsCollection->deleteOne(['AccountNo' => $accountNo]);

    if ($deleteResult->getDeletedCount() > 0) {
        echo "Success";
    } else {
        echo "Fail"; // Or handle the case where no document was deleted
    }
}
// Check if 'SenderAcNo' is set in the POST request
if (isset($_POST['SenderAcNo'])) {
    $accountNo = $_POST['SenderAcNo'];

    // Query MongoDB to find the customer details
    $customerCollection = $mongoDB->customer_detail;
    $accountDocument = $customerCollection->findOne(['Account_No' => $accountNo]);

    if ($accountDocument) {
        $Fname = $accountDocument['C_First_Name'];
        $Lname = $accountDocument['C_Last_Name'];
        $CitizenshipNo = $accountDocument['C_Citizenship_No'];
        $PanNo = $accountDocument['C_Pan_No'];
        $MobileNo = $accountDocument['C_Mobile_No'];

        // Query MongoDB to find the account details
        $loginCollection = $mongoDB->login;
        $accountDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);

        if ($accountDocument) {
            $Balance = $accountDocument['Balance'];
            $Status = $accountDocument['Status'];

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
            $flag = "fail";
            $data = array(
                'Flag' => $flag
            );

            echo json_encode($data);
        }
    } else {
        $flag = "fail";
        $data = array(
            'Flag' => $flag
        );

        echo json_encode($data);
    }
}

// Check if 'ReceiverAcNo' is set in the POST request
if (isset($_POST['ReceiverAcNo'])) {
    $accountNo = $_POST['ReceiverAcNo'];

    // Query MongoDB to find the customer and account details
    $customerCollection = $mongoDB->customer_detail;
    $loginCollection = $mongoDB->login;

    $accountDocument = $customerCollection->findOne(['Account_No' => $accountNo]);
    if ($accountDocument) {
        $Fname = $accountDocument['C_First_Name'];
        $Lname = $accountDocument['C_Last_Name'];
        $CitizenshipNo = $accountDocument['C_Citizenship_No'];
        $PanNo = $accountDocument['C_Pan_No'];
        $MobileNo = $accountDocument['C_Mobile_No'];

        $loginDocument = $loginCollection->findOne(['AccountNo' => $accountNo]);
        if ($loginDocument) {
            $Balance = $loginDocument['Balance'];
            $Status = $loginDocument['Status'];

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
            $flag = "fail";
            $data = array(
                'Flag' => $flag
            );

            echo json_encode($data);
        }
    } else {
        $flag = "fail";
        $data = array(
            'Flag' => $flag
        );

        echo json_encode($data);
    }
}


// Check if 'BalanceCheck' is set in the POST request
if (isset($_POST['BalanceCheck'])) {
    $accountNo = $_POST['BalanceCheck'];

    // Query MongoDB to find the account details
    $accountsCollection = $mongoDB->accounts;

    $accountDocument = $accountsCollection->findOne(['AccountNo' => $accountNo]);
    if ($accountDocument) {
        $Balance = $accountDocument['Balance'];
        echo $Balance;
    }
}


// Check if 'TSenderAc' is set in the POST request
if (isset($_POST['TSenderAc'])) {
    $SenderAc = $_POST['TSenderAc'];
    $ReceiverAc = $_POST['TReceiverAc'];
    $Amount = $_POST['MainAmount'];
    $currentDate = new MongoDB\BSON\UTCDateTime();
    if ($SenderAc == $ReceiverAc) {
        echo "Can't Transfer Money in same account";
    } else {

        if ($Amount >= 50) {

            // Query MongoDB to find sender account details
            $senderAccountsCollection = $mongoDB->accounts; // Replace 'sender_accounts' with your actual collection name
            $senderAccountDocument = $senderAccountsCollection->findOne(['AccountNo' => $SenderAc]);
            $loginCollection = $mongoDB->login;
            $loginDocument = $loginCollection->findOne(['AccountNo' => $SenderAc]);
            $customerCollection = $mongoDB->customer_detail;
            $accountDocument = $customerCollection->findOne(['Account_No' => $SenderAc]);
        
            if ($senderAccountDocument) {
                $SBalance = $senderAccountDocument['Balance'];
                $SStatus = $loginDocument['Status'];
                $SName = $accountDocument['C_First_Name'];
                $SLName = $accountDocument['C_Last_Name'];
                $SEmail = $accountDocument['C_Email'];
                $SProColor = $accountDocument['ProfileColor'];

                // Query MongoDB to find receiver account details
                $receiverAccountsCollection = $mongoDB->accounts; // Replace 'receiver_accounts' with your actual collection name
                $receiverAccountDocument = $receiverAccountsCollection->findOne(['AccountNo' => $ReceiverAc]);
                $loginCollections = $mongoDB->login;
                $loginDocuments = $loginCollection->findOne(['AccountNo' => $ReceiverAc]);
                $customerCollections = $mongoDB->customer_detail;
                $accountDocuments = $customerCollection->findOne(['Account_No' => $ReceiverAc]);
            
                if ($receiverAccountDocument) {
                    $RBalance = $receiverAccountDocument['Balance'];
                    $RStatus = $loginDocuments['Status'];
                    $RName = $accountDocuments['C_First_Name'];
                    $RLName = $accountDocuments['C_Last_Name'];
                    $REmail = $accountDocuments['C_Email'];
                    $RProColor = $accountDocuments['ProfileColor'];

                    if ($SStatus == "Active" && $RStatus == "Active") {

                        if ($SBalance != 0) {

                            if ($SBalance > $Amount) {

                                $Rtotal = $RBalance + $Amount;
                                $Stotal = $SBalance - $Amount;
                                $SenderName = $SName . " " . $SLName;
                                $ReceiverName = $RName . " " . $RLName;
                                $DebitAmount = -$Amount;                            // Update receiver's account balance
                                                $receiverAccountsCollection->updateOne(
                                                    ['AccountNo' => $ReceiverAc],
                                                    ['$set' => ['Balance' => $Rtotal]]
                                                );

                                                // Update sender's account balance
                                                $senderAccountsCollection->updateOne(
                                                    ['AccountNo' => $SenderAc],
                                                    ['$set' => ['Balance' => $Stotal]]
                                                );
                                                $transactionsCollection = $mongoDB->transaction; // Replace 'transactions' with your transactions collection name

                                                // Insert transaction record for receiver
                                                $transactionsCollection->insertOne([
                                                    'AccountNo' => $ReceiverAc,
                                                    'FAccountNo' => $SenderAc,
                                                    'Name' => $SenderName,
                                                    'Amount' => $Amount,
                                                    'Status' => 'Credited',
                                                    'ProfileColor' => $SProColor,
                                                    'Credit' => $Amount,
                                                    'Debit' => '0.0',
                                                    'Date' => $currentDate
                                                ]);

                                                // Insert transaction record for sender
                                                $transactionsCollection->insertOne([
                                                    'AccountNo' => $SenderAc,
                                                    'FAccountNo' => $ReceiverAc,
                                                    'Name' => $ReceiverName,
                                                    'Amount' => $DebitAmount,
                                                    'Status' => 'Debited',
                                                    'ProfileColor' => $RProColor,
                                                    'Credit' => '0.0',
                                                    'Debit' => $Amount,
                                                    'Date' => $currentDate
                                                ]);


                                // Send email notifications, etc.

                                echo "Success";
                            } else {
                                echo "Insufficient Account Balance";
                            }
                        } else {
                            echo "No Balance In Sender's Account!";
                        }
                    } else {
                        echo "Transaction Fail: Sender or Receiver Account is not Active!";
                    }
                }
            }
        } else {
            echo "Transaction Fail: Minimum amount required $50";
        }
    }
}