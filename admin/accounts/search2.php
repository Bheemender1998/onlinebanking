<?php
session_start();
include "../connection.php";

if (isset($_POST['AccountNumber'])) {
    $AccountNo = $_POST['AccountNumber'];

    

    // Select database and collection
    $mongoDB = $mongoClient->selectDatabase("onlinebanking");
    $collection = $mongoDB->selectCollection("login");

    // Query MongoDB collection
    $document = $collection->findOne(['AccountNo' => $AccountNo, 'Status' => 'Deactivated']);

    if ($document) {
        $id = $document['_id'];
        $Ac = $document['AccountNo'];
        $Username = $document['Username'];
        $Status = $document['Status'];

        $_SESSION["ActiveAccountNo"] = $AccountNo;
        $idString = (string) $id;
        $data = array(
            'id' => $idString,
            'Ac' => $Ac,
            'Username' => $Username,
            'Status' => $Status
        );

        echo json_encode($data);
    }
}

if (isset($_POST['Deactive_AccountNumber'])) {
    $AccountNo = $_POST['Deactive_AccountNumber'];

    // Select database and collection
    $mongoDB = $mongoClient->selectDatabase("onlinebanking");
    $collection = $mongoDB->selectCollection("login");

    // Query MongoDB collection
    $document = $collection->findOne(['AccountNo' => $AccountNo, 'Status' => 'Active']);

    if ($document) {
        $id = $document['_id'];
        $Ac = $document['AccountNo'];
        $Username = $document['Username'];
        $Status = $document['Status'];

        $_SESSION["De_ActiveAccountNo"] = $AccountNo;
        $idString = (string) $id;
        $data = array(
            'id' => $idString,
            'Ac' => $Ac,
            'Username' => $Username,
            'Status' => $Status
        );

        echo json_encode($data);
    }
}

if (isset($_POST['CloseAccountNo'])) {
    $CloseAc = $_POST['CloseAccountNo'];
    $_SESSION["CloseAcNo"] = $CloseAc;


    // Select database and collection
    $mongoDB = $mongoClient->selectDatabase("onlinebanking");
    $customerDetailCollection = $mongoDB->selectCollection("customer_detail");
    $accountsCollection = $mongoDB->selectCollection("accounts");

    // Query MongoDB collections
    $customerDocument = $customerDetailCollection->findOne(['Account_No' => $CloseAc]);
    $accountsDocument = $accountsCollection->findOne(['AccountNo' => $CloseAc]);

    if ($customerDocument && $accountsDocument) {
        $id = $customerDocument['_id'];
        $fname = $customerDocument['C_First_Name'];
        $lname = $customerDocument['C_Last_Name'];
        $Ac = $customerDocument['Account_No'];
        $Balance = $accountsDocument['Balance'];
        $AcType = $accountsDocument['AccountType'];

        $data = array(
            'id' => $id,
            'fname' => $fname,
            'lname' => $lname,
            'Ac' => $Ac,
            'Balance' => $Balance,
            'AcType' => $AcType,
            'message' => null
        );

        echo json_encode($data);
    } else {
        $data = array(
            'message' => "Account Not Found"
        );
        echo json_encode($data);
    }
}

if (isset($_POST['CloseAc'])) {
    $AccountNo = $_POST['CloseAc'];

    // Select database and collections
    $mongoDB = $mongoClient->selectDatabase("onlinebanking");
    $customerDetailCollection = $mongoDB->selectCollection("customer_detail");
    $loginCollection = $mongoDB->selectCollection("login");
    $accountsCollection = $mongoDB->selectCollection("accounts");
    $cardsCollection = $mongoDB->selectCollection("cards");

    // Query MongoDB collections
    $customerDocument = $customerDetailCollection->findOne(['Account_No' => $AccountNo]);
    $accountsDocument = $accountsCollection->findOne(['AccountNo' => $AccountNo]);
    $balance = $accountsDocument ? $accountsDocument['Balance'] : null;
 
    if ($customerDocument && ($balance == 0.0 || $balance == 0)) {
        // Delete documents from MongoDB collections
        $customerDetailCollection->deleteOne(['Account_No' => $AccountNo]);
        $loginCollection->deleteOne(['AccountNo' => $AccountNo]);
        $accountsCollection->deleteOne(['AccountNo' => $AccountNo]);
        $cardsCollection->deleteMany(['AccountNo' => $AccountNo]);

        echo "Success";
    } else {
        echo "fail";
    }
}