<?php
$ID = $_GET['ID'] ?? -1;
$TYPE = $_GET['TYPE'] ?? 1;
$COLOR = $_GET['COLOR'] ?? null;

if ($ID < 0)
    exit;

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";

$OUT_ARR = array();
$OUT_VALID = 0;

#region Отримання масиву виконаних ІД $arr_done_id
$arr_done_id = array();

$query = 'SELECT ID, sholom_num, sold_number, date_out FROM client_info
               WHERE TTN_IN IS NOT NULL OR sold_number IS NOT NULL ORDER BY date_out DESC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
        $nom = $row["sholom_num"] === null ? $row["sold_number"] : $row["sholom_num"];

        $date = $row['date_out'] !== null ? $row['date_out'] : 'в роботі';

        $arr_done_id[$row["ID"]] = [$date, $nom];
    }
}
#endregion

$colorSet = $COLOR !== null ? ' AND service_in.color = ' . $COLOR : '';

#region Вибірка приходу товарів на склад

$query = 'SELECT date_in,colors.color,count,comm FROM `service_in` JOIN colors ON service_in.color=colors.ID
              where `service_ID` = ' . $ID . ' AND `type_ID` = ' . $TYPE . $colorSet . ' ORDER BY `date_in` DESC';

$result = mysqli_query($link, $query);

$OUT_VALID += mysqli_num_rows($result);

if (mysqli_num_rows($result) > 0) {

    while ($row = $result->fetch_assoc()) {
        $comm = $row['comm'] !== null ? $row['comm'] : '';

        $znak = $row['count'] > 0 ? '+' : '';

        $OUT_ARR[$row['date_in']][$row['color']][] = [$znak . $row['count'] , $comm];
    }
}

#endregion

$colorSet = $COLOR !== null ? ' AND service_out.color = ' . $COLOR : '';

#region Вибірка відправлених комплектуючих

foreach ($arr_done_id as $z_id=>$info) {

    $query = 'SELECT colors.color, count FROM service_out JOIN colors ON service_out.color=colors.ID
              where service_out.ID = ' . $z_id . ' AND service_ID = ' . $ID . ' AND type_ID = '. $TYPE . $colorSet;

    $result = mysqli_query($link, $query);

    $OUT_VALID += mysqli_num_rows($result);

    if (mysqli_num_rows($result) > 0) {

        while ($row = $result->fetch_assoc()) {
            $OUT_ARR[$info[0]][$row['color']][] = [$row['count'] * -1 , '№ ' . $info[1]];
        }
    }
}

#endregion

$link->close();

if ($OUT_VALID > 0){
    $tbody = new HTEL('tbody');

    krsort($OUT_ARR);

    $tr = new HTEL('tr');

    $tr([
        new HTEL('th /дата'),
        new HTEL('th /колір'),
        new HTEL('th /пояснення'),
        new HTEL('th /рух')
    ]);

    $tbody($tr);

    foreach ($OUT_ARR as $date => $color) {
        foreach ($color as $c => $arr) {
            foreach ($arr as $a) {
                $tr = new HTEL('tr');
                $dat = dateToNorm($date, true);
                $tr([
                    new HTEL('td /[0]', $dat),
                    new HTEL('td /[0]', $c),
                    new HTEL('td /[0]', $a[1]),
                    new HTEL('td &=font-weight:bold;[1]/[0]', [$a[0], $a[0]<0? 'background-color:green;color:white;':''])
                ]);

                $tbody($tr);
            }
        }
    }

    $table = new HTEL('table .=info_cnt_tbl &=font-size:70%;text-align:center;', [
        new HTEL('caption &=font-weight:bold;font-size:140%;color:blue;/[0]', $_GET['NAME']),
        $tbody
    ]);

    echo $table;
}
else {
    echo 'Руху по [' . $_GET['NAME'] . '] не знайдено!';
}


?>