<?php
require_once("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$query = 'DELETE FROM `service_out` WHERE `ID`=' . $_GET['ID'];

$result = mysqli_query($link, $query);

$query = 'DELETE FROM `client_info` WHERE `ID`=' . $_GET['ID'];

$result = mysqli_query($link, $query);

#region Дисконт відновити
    $query = 'UPDATE `discount_list` SET `from_ID` = NULL WHERE `from_ID` = ' . $_GET['ID'];
    mysqli_query($link, $query);
#endregion

phpAlert('Запис успішно видалено.', 'work');
?>