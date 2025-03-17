<?php
 //DATABASE CONNECTION STRING CREDENTIALS
 $dbConn = "mysql:host=127.0.0.1;dbname=db_api_capstone";
 $user = "root";
 $pass = ""; //for mobile users password: root
 //CREATE PDO DATABASE CONNECTION
 $pdo = new PDO($dbConn, $user, $pass); 
?>
