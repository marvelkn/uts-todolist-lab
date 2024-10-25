<?php
$host = 'localhost'; // or your database server
$user = 'root'; // or your database username
$password = ''; // your MySQL root password (often blank for XAMPP)
$database = 'uts_lab_webpro'; // this should be the correct database name

$connect = mysqli_connect($host, $user, $password, $database);

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
?>