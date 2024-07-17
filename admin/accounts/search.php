<?php
    session_start();
    include "../connection.php";

    $mongoDB = $mongoClient->selectDatabase("onlinebanking");
    $collection = $mongoDB->selectCollection("customer_detail");
    
    if (isset($_POST['AccountNumber'])) {
        $AccountNo = $_POST['AccountNumber'];
    
        // Query MongoDB collection
        $filter = ['Account_No' => $AccountNo];
        $document = $collection->findOne($filter);
    
        if ($document) {
            // Process the document
            $id = $document['_id'];
            $Ac = $document['Account_No'];
            $Fname = $document['C_First_Name'];
            $Lname = $document['C_Last_Name'];
    
            $_SESSION["EditAccountNo"] = $AccountNo;
            $idString = (string) $id;
            $data = array(
                'id' => $idString,
                'Ac' => $Ac,
                'Fname' => $Fname,
                'Lname' => $Lname
            );
    
            echo json_encode($data);
        } else {
            echo "No document found matching the criteria.";
        }
    }
