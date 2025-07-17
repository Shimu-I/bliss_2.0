<?php
$host = 'localhost';
$db = 'daycare_management';
$user = 'root';
$pass = 'admin';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
