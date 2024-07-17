<?php

    $mongoDB = $mongoClient->selectDatabase('onlinebanking'); // Replace 'onlinebanking' with your actual database name


    $loginCollection = $mongoDB->login;
    $cardsCollection = $mongoDB->cards;
    
    // Count inactive accounts
    $inactiveAccountsCount = $loginCollection->countDocuments(['Status' => 'Inactive']);
    
    // Count unverified cards
    $unverifiedCardsCount = $cardsCollection->countDocuments(['Verified' => 'No']);
    
?>