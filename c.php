<?php
 require_once("connection.php");
 $sql = "SELECT * FROM Shop" ;
 $stmt = $pdo->prepare($sql);
 $stmt->execute();
 $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
 print_r($categories);