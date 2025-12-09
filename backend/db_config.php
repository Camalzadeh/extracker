<?php
$servername = "localhost";
$username = "root";
$password = "humbet2006";
$dbname = "hwp_project";
$port = "3306";
try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Bağlantı xətası: " . $e->getMessage();
}
?>