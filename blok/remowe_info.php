<?php
require_once("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$query = 'DELETE FROM `service_out` WHERE `ID`=' . $_GET['ID'];

$result = mysqli_query($link, $query);

$query = 'DELETE FROM `client_info` WHERE `ID`=' . $_GET['ID'];

$result = mysqli_query($link, $query);

phpAlert('Запис успішно видалено.', 'work');
?>