<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//ВИДАЛЕННЯ РЯДКА

$conn = new SQLconn();

if (isset($_GET['del'])){

    $split = explode('_', $_GET['del'], 4);

    if (count($split) ==  4){

        $query = 'DELETE FROM `service_in` WHERE
        `date_in`="' . $split[0] . '" AND
        `service_ID`=' . $split[1] . ' AND
        `type_ID`=' . $split[2] . ' AND
        `costs`=' . $split[3] . ' LIMIT 1';

        $conn($query, 0);

        $dat = DateTime::createFromFormat('Y-m-d', $split[0]);

        echo ' від ' . $dat->format('d.m.y') . ' на суму ' . $split[3] . ' грн. ';

        exit;
    }
}

//Вибірка послуг

$arr_serv_name = array();
$arr_types = array();

$result = $conn('SELECT * FROM service_ids ORDER BY `order` ASC');

foreach ($result as $row) {
    $arr_serv_name[$row["ID"]] = $row["NAME"];

    $arr_types[$row["ID"]][1] = '';
}

//ВИБІРКА ІСНУЮЧИХ ТИПІВ

$result = $conn('SELECT * FROM type_ids');

foreach ($result as $row) {
    $arr_types[$row["service_ID"]][$row["type_ID"]] = " (" . $row["name"] . ")";
}

#region Отримання списку кольорів
$_COLORS = array();

$result = $conn('SELECT * FROM colors');

$map = $conn('SELECT * FROM color_map');

foreach ($result as $row) {
    $_COLORS[$row['ID']] = new MyColor2($row['ID'], $row['color'], $map, $row['css_name'], $row['is_def']);
}
#endregion

#region Вибірка витрат

$serv_out = array();
$serv_info = array();

session_start();

$mnth = $_GET['month'];
$year = $_GET['year'];

$min_date = $year . '-' . $mnth . '-1';

if ($mnth < 12){
    $max_date = $year . '-' . ($mnth + 1) . '-01';
}else{
    $max_date = ($year + 1) . '-01-01';
}

$query = 'SELECT * FROM service_in WHERE date_in >= "'. $min_date .'" AND date_in < "' . $max_date . '"
ORDER by date_in DESC';

if ($_SESSION[$_SESSION['logged']] > 1){
    $query = 'SELECT * FROM service_in WHERE date_in >= "'.$min_date .'" AND date_in < "'.$max_date .
    '" AND redaktor = "'.$_SESSION['logged'].'" order by date_in DESC';
}

$result = $conn($query);

foreach ($result as $row) {

    $serv_info[$row['date_in']][$row['redaktor']][] =
    [
        'id'=> $row['service_ID'],
        'type' => $row['type_ID'],
        'color' => $row['color'],
        'cost' => $row['costs'],
        'count' => $row['count'],
        'comm' => $row['comm'],
    ];
}

$conn->close();

#endregion

$table = new HTEL('table !=jurnal_table .=expenses_list');
$tbody = new HTEL('tbody');
foreach ($serv_info as $date=>$in_date) { //
    $tbody(new HTEL('tr', new HTEL('th colspan=6 &=text-align:left;/[0]', dateToNorm($date))));
    $tbody(new HTEL('tr &=border:none;background-color:darkgrey;', new HTEL('td colspan=6 &=border:none;height:25px;')));
    foreach ($in_date as $red=>$kompl) {
        $costall = 0;
        foreach ($kompl as $arr) {
            $color = isset($_COLORS[$arr['color']]) ? $_COLORS[$arr['color']]->NAME : '';

            $bord_top = $costall == 0 ? 'border-top:3px solid;' : '';
            $tbody(new HTEL('tr &=border-left:2px+solid;border-right:2px+solid;[2]', [ 2=>$bord_top,
                new HTEL('td &=text-align:left;/[0] [1]', [$arr_serv_name[$arr['id']], $arr_types[$arr['id']][$arr['type']]]),
                new HTEL('td/[0]', $color),
                new HTEL('td &=width:10%; /[0] шт.', $arr['count']),
                new HTEL('td &=text-align:right;/[0]', CostOut($arr['cost'])),
                new HTEL('td/[0]', $arr['comm']),
                new HTEL('td .=del_cell+no-print', new HTEL('input .=delrow ?=[0]_[1]_[2]_[3] #=X [ro]', [$date,$arr['id'],$arr['type'],$arr['cost']]))
            ]));
            $costall += $arr['cost'];
        }

        $tbody(new HTEL('tr .=autor_exp', [
             new HTEL('td colspan=4/[0] грн.', $costall),
             new HTEL('td colspan=2 &=color:blue;/[0]', $red)
        ]));

        $tbody(new HTEL('tr &=border:none;background-color:darkgrey;', new HTEL('td colspan=6 &=border:none;height:25px;')));
    }
}

$table($tbody);

echo $table;

?>

<script>

    $('.delrow').on('click', function () {
        var $name = $(this).attr('name');
        var spl = $name.split('_');
        var sum = spl[spl.length - 1];

        if (!confirm('Підтвердіть видалення запису на суму ' + sum + ' грн.')) return false;

        $.get('blok/exp/jurnal.php', 'del=' + $name, function (result) {
            alert('Запис [' + result + '] видалено.');
            document.location = 'work?page=jurnal';
        });
    });

</script>