<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user=$_SESSION['id_user'];
$murid=$_SESSION['id_ref'];

$check = $conn->query("SELECT m.* FROM master_murid m 
                            LEFT JOIN tm_user u ON m.id_murid = u.id_ref 
                            LEFT JOIN registrasi_murid k ON k.id_murid=m.id_murid 
                            WHERE u.id_ref = ".$murid." AND k.konfirmasi_registrasi='Divalidasi'
                            ");
$result = $check->fetch_assoc();

if(!$result){
    header("Location: belum_validasi.php");
    exit;
}