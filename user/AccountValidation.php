<?php
    
    session_start();

    // Include connection page into this page
    include 'connection.php';

    // Checking Citizenship Number is set or not
    if (isset($_POST['CitizenshipNumber'])) {
        
      $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'your_database_name' with your actual database name

      $collection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name
      
      $CitizenshipNo = $_POST['CitizenshipNumber']; // No need to escape for MongoDB
      
      $filter = ['C_Citizenship_No' => $CitizenshipNo];
      
      $result = $collection->countDocuments($filter);
      
      echo $result;
    }


    // checking pan number is set or not
    if (isset($_POST['PanNumber'])) {

        /* storing Pan Number in varible using mysqli_real_escape_string function to remove special charaters like double quotes or etc */
        $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Assuming 'onlinebanking' is your actual database name

        $collection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name
        
        $PanNo = $_POST['PanNumber']; // No need to escape for MongoDB
        
        $filter = ['C_Pan_No' => $PanNo];
        
        $result = $collection->countDocuments($filter);
        
        echo $result;
    }

    // checking Email is set or not
    if (isset($_POST['EmailAddress'])) {

        /* storing Email in varible using mysqli_real_escape_string function to remove special charaters like double quotes or etc */
        $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Assuming 'onlinebanking' is your actual database name

        $collection = $mongoDB->customer_detail; // Assuming 'customer_detail' is your collection name
        
        $Email = $_POST['EmailAddress']; // No need to escape for MongoDB
        
        $filter = ['C_Email' => $Email];
        
        $result = $collection->countDocuments($filter);
        
        echo $result;
    }


    // Sending otp Email to Customer

    if(isset($_POST['MailSend'])){

      // Include Mail sending file
      include '../mail/mail_config.php';
      $mail = $_POST['MailSend'];
      $name = $_POST['Name'];
      echo "<br>";
  
      // Create Otp
      $otp = 699600;
  
      echo "<br>";

      // storing otp to server 
      $_SESSION['otp'] = $otp;
      
      // Calling Otp Function to send email
      $sucess = sendOtp($mail, $otp, $name);
      

    }

    // Validating Otp
    if(isset($_POST['OTP'])){

        $userOtp = trim($_POST['OTP']);
        $SessionOtp = trim($_SESSION['otp']);

        if($SessionOtp == $userOtp){
          
          echo "Valid";
        }
        else{
          echo "Invalid";
        }

    }


    // checking Username is set or not
    if (isset($_POST['Username'])) {

      $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Assuming 'onlinebanking' is your actual database name

      $collection = $mongoDB->login; // Assuming 'login' is your collection name
      
      $Username = $_POST['Username']; // No need to escape for MongoDB
      
      $filter = ['Username' => $Username];
      
      $result = $collection->countDocuments($filter);
      
      echo $result;
  }
