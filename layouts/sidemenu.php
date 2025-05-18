<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'];

switch ($role) {
    case 1:
        require('sidemenu_owner.php');
        break;
    case 2:
        require('sidemenu_guru.php');

        break;
    case 3:
        require('sidemenu_murid.php');
        break;
    }
?>