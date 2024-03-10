<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$conn = new SQLconn();

$conn('DELETE FROM `service_out` WHERE `ID`=' . $_GET['ID']);

$conn('DELETE FROM `client_info` WHERE `ID`=' . $_GET['ID']);

#region Дисконт відновити
    $conn('UPDATE `discount_list` SET `from_ID` = NULL WHERE `from_ID` = ' . $_GET['ID'], 0);
#endregion

phpAlert('Запис успішно видалено.', 'work');
?>