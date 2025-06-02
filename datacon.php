<?php

$servername = 'localhost';
$username = 'root';
$password = '123456789';
$dbname = 'elitefit2';

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
  die("connection failed: " . mysqli_connect_error());
}

?>