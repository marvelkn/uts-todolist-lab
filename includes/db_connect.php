<?php
$dsn = 'mysql:host=localhost;dbname=uts_lab_webpro';
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uts_lab_webpro";

// Create connection
$connect = new mysqli($servername, $username, $password, $dbname);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=uts_lab_webpro', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
