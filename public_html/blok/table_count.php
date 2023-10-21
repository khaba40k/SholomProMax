<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
require "conn_local.php";

$table = new HTEL('table .=tbl_count');
$table(new HTEL('caption/ЗАЛИШКИ КОМПЛЕКТУЮЧИХ станом на [0]p.', date('d.m.Y')));

$arr_cnt = array();

//Вибірка послуг

$arr_serv_name = array();
$arr_types = array();

$query = 'SELECT `NAME`, `ID` FROM `service_ids` where `color` = 1 ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_serv_name[$row["ID"]] = $row["NAME"];
    $arr_types[$row["ID"]][1] = '';
}

//ВИБІРКА ІСНУЮЧИХ ТИПІВ

$query = 'SELECT * FROM `type_ids`';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_types[$row["service_ID"]][$row["type_ID"]] = "(" . $row["name"] . ")";
}

#region Отримання списку кольорів
$_COLORS = array();

$query = 'SELECT * FROM `colors`';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_COLORS[] = new MyColor($row['ID'], $row['color'], $row['serv_ids'], $row['css_name']);
    }
}
#endregion

$tbody = new HTEL('tbody');

$tr = new HTEL('tr');
$tr(new HTEL('th/НАЗВА'));

foreach($_COLORS as $c){
    $tr(new HTEL('th &=background-color:[1]; .=color_cell/[0]', [$c->NAME, $c->CSS_ANALOG] ));
}

$tr(new HTEL('th/РАЗОМ'));

$tbody($tr);

//Вибірка плюсів/мінусів

foreach ($arr_serv_name as $i => $n) {
    $query = 'SELECT `type_ID`,`color`,`count` FROM `service_in`
              where `service_ID` = ' . $i . ' AND `color` IS NOT NULL';

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {

        while ($row = $result->fetch_assoc()) {
            if (!isset($arr_cnt[$i][$row["type_ID"]][$row["color"]]))
                $arr_cnt[$i][$row["type_ID"]][$row["color"]] = 0;

            $arr_cnt[$i][$row["type_ID"]][$row["color"]] += $row["count"];
        }
    }

    $query = 'SELECT `type_ID`,`color`,`count` FROM `service_out`
              where `service_ID` = ' . $i . ' AND `color` IS NOT NULL';

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {

        while ($row = $result->fetch_assoc()) {
            if (!isset($arr_cnt[$i][$row["type_ID"]][$row["color"]]))
                $arr_cnt[$i][$row["type_ID"]][$row["color"]] = 0;

            $arr_cnt[$i][$row["type_ID"]][$row["color"]] -= $row["count"];
        }
    }
}

$link->close();

//Заповнення таблиці

foreach ($arr_serv_name as $id => $ni) {
    foreach ($arr_types[$id] as $t => $nt) {
        if (isset($arr_cnt[$id][$t])) {
            $tr = new HTEL('tr');
            $tr(new HTEL('td &=text-align:left;/[0] [1]', [$ni, $nt]));

            foreach ($_COLORS as $c) {
                $cnt = isset($arr_cnt[$id][$t][$c->ID]) ? $arr_cnt[$id][$t][$c->ID] : '-';
                $tr(new HTEL('td &=text-align:center;/[0]', MyVal($cnt)));
            }

            $tr(new HTEL('td &=text-align:center;font-weight:bold;/[0]', MyVal(sumArray($arr_cnt[$id][$t]))));

            $tbody($tr);
        }
    }
}

$table($tbody);

echo $table;

function MyVal($val):string{

    if ($val == 0)
        return '-';

    return $val;
}

?>