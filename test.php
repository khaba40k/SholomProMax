<?php
session_start();
if (!isset($_SESSION['logged']) || $_SESSION['logged'] != 'Administrator')
    exit;

//require("conn_local.php");
require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

echo 'TEST => <br /><br />';//-------------------------------------------------

?>