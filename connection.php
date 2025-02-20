<?php
$server ="localhost";
$user   ="root";
$pass   ="";
$db     ="bimbel";
 
$conn = mysqli_connect($server,$user,$pass,$db)
            or
    die(mysqli_error());
?>
 