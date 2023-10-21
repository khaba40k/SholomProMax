<?php
    $host = '127.0.0.1';
    $database = 'sholompr_data';
    $user = 'sholompr_admin';
    $password = 'R[$cB{&A5n]$';
    //R[$cB{&A5n]$

$link = mysqli_connect($host, $user, $password, $database)
    or die("MySql connect ERROR: " . mysqli_error($link));

//mysqli_query($link, "SET NAMES 'utf8' COLLATE 'utf8mb4_general_ci'");
//mysqli_query($link, "SET CHARACTER SET 'utf8'");

mysqli_query($link, "SET collation_connection = utf8_general_ci");
mysqli_query($link, "SET NAMES utf8");

?>