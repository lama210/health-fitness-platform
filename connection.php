<?php
$server = "localhost";
$username = "root";
$password = "";
$db = "health";

 // PDO object
 try{
    $pdo = new PDO("mysql:host=$server;dbname=$db",$username,$password);
    // echo "connection";
}catch(PDOException $exception){
    echo "Connection to database failed! Error : " . $exception->getMessage();
    die();
}