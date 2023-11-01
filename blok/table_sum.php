<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
require "conn_local.php";

$table = new HTEL('table .=tbl_sum');

$table(new HTEL('caption .=onlyPrint/Звіт за період: [0]р. - [1]р.',[dateToNorm($_GET['ot']), dateToNorm($_GET['do'])]));

$tbody = new HTEL('tbody');

$tr = new HTEL('tr');

$tr(new HTEL('th /Назва послуги'));
$tr(new HTEL('th colspan=2/Витрати'));
$tr(new HTEL('th /Списано'));
$tr(new HTEL('th colspan=2/Надходження'));
$tr(new HTEL('th /ВСЬОГО'));

$tbody($tr);

$arr_sum_in = array();
$arr_cnt_in = array();
$arr_sps_in = array();
$arr_com_in = array();
$arr_com_sps = array();

$arr_sum_out = array();
$arr_cnt_out = array();

//Вибірка послуг

$arr_serv_name = array();
$arr_types = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_serv_name[$row["ID"]] = $row["NAME"];

    $arr_types[$row["ID"]][1] = '';

    if ($row["ID"] != 19){
        $arr_types[19][$row["ID"]] = "(" . $row["NAME"] . ")";
    }
}

//ВИБІРКА ІСНУЮЧИХ ТИПІВ

$query = 'SELECT * FROM `type_ids`';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_types[$row["service_ID"]][$row["type_ID"]] = "(" . $row["name"] . ")";
}

//Вибірка витрат

foreach ($arr_serv_name as $i=>$n) {
    foreach ($arr_types[$i] as $t => $tn) {
        $query = 'SELECT `costs`,`count`,`comm` FROM `service_in`
              where `date_in` >= "' . $_GET['ot'] . '"
              and `date_in` <= "' . $_GET['do'] . '"
              and `service_ID` = ' . $i .
              ' AND `type_ID` = ' . $t;

        $result = mysqli_query($link, $query);

        $arr_sum_in[$i][$t] = 0;
        $arr_cnt_in[$i][$t] = 0;
        $arr_sps_in[$i][$t] = 0;

        if (mysqli_num_rows($result) > 0) {
            $sum_cost = 0;
            $sum_count = 0;
            $sps_count = 0;

            while ($row = $result->fetch_assoc()) {

                if (!is_null($row['comm'])) { //вибірка по коментарям (сума, списання)
                    $com = trim($row['comm']);

                    isset($arr_com_in[$i][$t][$com]['cost']) ?
                        $arr_com_in[$i][$t][$com]['cost'] += $row["costs"] :
                        $arr_com_in[$i][$t][$com]['cost'] = $row["costs"];

                    if ($row['count'] >= 0) {
                        isset($arr_com_in[$i][$t][$com]['count']) ? $arr_com_in[$i][$t][$com]['count'] += $row['count'] :
                            $arr_com_in[$i][$t][$com]['count'] = $row['count'];
                    }
                    else{
                        isset($arr_com_in[$i][$t][$com]['sps']) ? $arr_com_in[$i][$t][$com]['sps'] += $row['count'] :
                            $arr_com_in[$i][$t][$com]['sps'] = $row['count'];
                    }
                }

                if ($row["count"] > 0) {
                    $sum_cost += $row["costs"];
                    $sum_count += $row["count"];
                } else {
                    $sps_count += $row["count"];
                }

            }

            $arr_sum_in[$i][$t] = $sum_cost;
            $arr_cnt_in[$i][$t] = $sum_count;
            $arr_sps_in[$i][$t] = abs($sps_count);
        }
    }
}

//Отримання ID за період

$arr_ids = array();

$query = 'SELECT `ID` FROM `client_info`
              where `date_out` >= "' . $_GET['ot'] . '"
              and `date_out` <= "' . $_GET['do'] . '"';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) > 0) {
    foreach ($result as $row) {
        $arr_ids[] = $row["ID"];
    }
}
//Заливка надходжень

foreach ($arr_serv_name as $i=>$n) {
    foreach ($arr_types[$i] as $t => $tn) {
        $arr_sum_out[$i][$t] = 0;
        $arr_cnt_out[$i][$t] = 0;

            foreach ($arr_ids as $num) {

                $query = 'SELECT * FROM `service_out`
                          where `ID` = "' . $num . '"
                          and `service_ID` = ' . $i .
                         ' AND `type_ID` = ' . $t;

                $result = mysqli_query($link, $query);

                if (mysqli_num_rows($result) > 0) {

                    while ($row = $result->fetch_assoc()) {
                        $arr_sum_out[$i][$t] += $row["costs"];
                        $arr_cnt_out[$i][$t] += $row["count"];
                    }
                }
            }
    }
}

//Заповнення таблиці

foreach ($arr_serv_name as $i=>$n) {
    foreach ($arr_types[$i] as $t => $tn) {
        $sum_row = $arr_sum_out[$i][$t] - $arr_sum_in[$i][$t];

        $cell = array();

        $cell[0] = $n;
        $cell[1] = $arr_cnt_in[$i][$t] != 0 ? $arr_cnt_in[$i][$t] : '';
        $cell[2] = $arr_sum_in[$i][$t] != 0 ? CostOut($arr_sum_in[$i][$t]) : '-';
        $cell[3] = $arr_sps_in[$i][$t] != 0 ? $arr_sps_in[$i][$t] : '-';
        $cell[4] = $arr_cnt_out[$i][$t] != 0 ? $arr_cnt_out[$i][$t] : '';
        $cell[5] = $arr_sum_out[$i][$t] != 0 ? CostOut($arr_sum_out[$i][$t]) : '-';
        $cell[6] = $sum_row != 0 ? CostOut($sum_row) : '-';

        if ($arr_sum_in[$i][$t] + $arr_sum_out[$i][$t] > 0 || $arr_cnt_in[$i][$t] + $arr_cnt_out[$i][$t] > 0 || $arr_sps_in[$i][$t] != 0) {
            $tr = new HTEL('tr &=border-style:inherit;');

            for ($ii = 0; $ii < count($cell); $ii++) {

                switch ($ii) {
                    case 0:
                        $tr(new HTEL('td &=text-align:left;/[0] [1]', [$cell[$ii], $tn]));
                        break;
                    case 1:
                    case 3:
                    case 4:
                        $tr(new HTEL('td &=text-align:center;/[0]', $cell[$ii]));
                        break;
                    case 6:
                        $tr(new HTEL('td &=text-align:right;font-weight:bold;/[0]', $cell[$ii]));
                        break;
                    default:
                        $tr(new HTEL('td &=text-align:right;/[0]', $cell[$ii]));
                        break;
                }
            }

            $tbody($tr);

            if (isset($arr_com_in[$i][$t])){

                foreach ($arr_com_in[$i][$t] as $com=>$arr){
                    $tr = new HTEL('tr .=row_comm');
                    $tr(new HTEL("td/[0]", $com));
                    $tr(new HTEL("td &=text-align:center;/[0]", isset($arr['count']) ? $arr['count'] : ''));
                    $tr(new HTEL("td/[0]",
                            $arr['cost'] >  0 ? CostOut($arr['cost']) : '-'));
                    $tr(new HTEL("td &=text-align:center;/[0]", isset($arr['sps']) ? abs($arr['sps']) : '-'));
                    $tr(new HTEL("td colspan=3"));
                    $tbody($tr);
                }
            }
        }
    }
}

$_s_in = floatval(sumArray($arr_sum_in));
$_s_out = floatval(sumArray($arr_sum_out));

$tr = new HTEL('tr', [CostOut($_s_in), CostOut($_s_out), CostOut($_s_out - $_s_in)]);

$tr(new HTEL('th &=text-align:center;/РАЗОМ'));

$tr(new HTEL('th colspan=2 &=text-align:right;/[0]'));
$tr(new HTEL('th &=text-align:center;/[0]', sumArray($arr_sps_in)));
$tr(new HTEL('th colspan=2 &=text-align:right;/[1]'));
$tr(new HTEL('th &=text-align:right;/[2]'));

$tbody($tr);

$table($tbody);

echo $table;

$link->close();

?>