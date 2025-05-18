<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['role'])){
    require ('./login.php');
    exit;
}

if($_SESSION['role'] !=1 && !isset($_SESSION['id_ref'])){
    header('./register_public.php');
    exit;
}