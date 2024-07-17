<?php
$mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name

$customerCollection = $mongoDB->customer_detail;

$accountNo = $_SESSION['accountNo'];

$query = ['Account_No' => $accountNo];
$projection = ['projection' => [
    'C_First_Name' => 1,
    'C_Last_Name' => 1,
    'C_Citizenship_No' => 1,
    'C_Pan_No' => 1,
    'C_Mobile_No' => 1,
    'ProfileImage' => 1
]];

$cursor = $customerCollection->find($query, $projection);

$admin = null;
$adminProfile = null;
$adminProfileInner = null;

foreach ($cursor as $document) {
    $Fname = $document['C_First_Name'];
    $Lname = $document['C_Last_Name'];
    $CitizenshipNo = $document['C_Citizenship_No'];
    $PanNo = $document['C_Pan_No'];
    $MobileNo = $document['C_Mobile_No'];
    $Profile = $document['ProfileImage'];
  
    $Admin = $Fname . " " . $Lname;
    $AdminProfile = "../user/" . $Profile;
    $AdminProfileInner = "../../user/" . $Profile;
}


?>