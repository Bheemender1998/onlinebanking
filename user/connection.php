<?php
require 'vendor/autoload.php'; // Make sure the autoload file is included

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

// You can proceed with using $mongoDB for further operations like CRUD, etc.
?>
