<?php
$ID = $_GET['ID'] ?? -1;
$TYPE = $_GET['TYPE'] ?? 1;

if ($ID < 0)
    exit;

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
require "conn_local.php";

$OUT_ARR = array();

#region Отримання масиву виконаних ІД $arr_done_id
$arr_done_id = array();

$query = 'SELECT `ID`,`sholom_num`,`sold_number`,`date_out` FROM `client_info`
              where `TTN_IN` IS NOT NULL OR `TTN_OUT` IS NOT NULL ORDER BY `date_out` DESC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
        $nom = $row["sholom_num"] === null ? $row["sold_number"] : $row["sholom_num"];

        $date = $row['date_out'] !== null ? $row['date_out'] : 'в роботі';

        $arr_done_id[$row["ID"]] = [$date, $nom];
    }
}
#endregion

#region Вибірка приходу товарів на склад

$query = 'SELECT date_in,colors.color,count,comm FROM `service_in` JOIN colors ON service_in.color=colors.ID
              where `service_ID` = ' . $ID . ' AND `type_ID` = ' . $TYPE . ' ORDER BY `date_in` DESC';

              //JOIN colors ON service_in.color=colors.ID

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) > 0) {

    while ($row = $result->fetch_assoc()) {
        $comm = $row['comm'] !== null ? ' (' . $row['comm'] . ')' : '';

        $znak = $row['count'] > 0 ? '+' : '';

        $OUT_ARR[$row['date_in']][$row['color']][] = $znak . $row['count'] . $comm;
    }
}

#endregion

#region Вибірка відправлених комплектуючих

foreach ($arr_done_id as $z_id=>$info) {

    $query = 'SELECT colors.color, count FROM `service_out` JOIN colors ON service_out.color=colors.ID
              where service_out.ID = ' . $z_id . ' AND `service_ID` = ' . $ID . ' AND `type_ID` = '. $TYPE;

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {

        while ($row = $result->fetch_assoc()) {
            $OUT_ARR[$info[0]][$row['color']][] = ($row['count'] * -1) . ' (№: ' . $info[1] . ')';
        }
    }
}

#endregion

$link->close();

$tbody = new HTEL('tbody');

krsort($OUT_ARR);

$tr = new HTEL('tr');

$tr([
    new HTEL('th /дата'),
    new HTEL('th /колір'),
    new HTEL('th /рух')
]);

$tbody($tr);

foreach($OUT_ARR as $date=>$color){
    foreach($color as $c=>$arr){
        $tr = new HTEL('tr');
        $dat = dateToNorm($date, true);
        $tr([
           new HTEL('td &=padding:2px+7px;/[0]', $dat),
           new HTEL('td &=padding:2px+7px;/[0]', $c),
           new HTEL('td &=padding:2px+7px;/[0]', $arr)
        ]);
        $tbody($tr);
    }
}

$table = new HTEL('table &=font-size:70%;text-align:center;', [
   new HTEL('caption &=font-weight:bold;font-size:140%;color:blue;/[0]', $_GET['NAME']),
   $tbody
]);

echo $table;

?>