<?php

require("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$query = 'DELETE FROM `price_list`';
mysqli_query($link, $query);

$record_arr = array();

foreach ($_GET as $k=>$v){
    $split = explode('_', $k);
    $record_arr[$split[0]][$split[1]] = $v;
}

$correct = true;

    foreach ($record_arr as $sid=>$typearr) {

        foreach ($typearr as $t=>$cost){
             $query = 'INSERT INTO `price_list` (service_id, type_id, cost)
             VALUES ('. $sid.','.$t.','.$cost.')';
             $link->query($query);
        }

    }

$link->close();

if ($correct) {
    phpAlert("Ціни оновлено успішно.", 'work');
}

?>