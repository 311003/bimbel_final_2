<?php
$host = 'localhost';
$dbname = 'bimbel'; // atau nama database kamu
$username = 'root'; // default user di XAMPP
$password = '';     // default password biasanya kosong

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
// echo "Connected successfully"; // aktifkan ini jika mau tes
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>
