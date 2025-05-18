<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$guru=$_SESSION['id_ref'];

$check = $conn->query("SELECT m.* FROM guru m 
                            LEFT JOIN tm_user u ON m.id_guru = u.id_ref 
                            WHERE u.id_ref = ".$guru." AND m.id_status_guru=1
                            ");
$result = $check->fetch_assoc();

if(!$result){
    header("Location: belum_validasi.php");
    exit;
}