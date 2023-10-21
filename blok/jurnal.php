<?php

require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";

//ВИДАЛЕННЯ РЯДКА

if (isset($_GET['del'])){

    $split = explode('_', $_GET['del'], 4);

    if (count($split) ==  4){

        $query = 'DELETE FROM `service_in` WHERE
        `date_in`="' . $split[0] . '" AND
        `service_ID`=' . $split[1] . ' AND
        `type_ID`=' . $split[2] . ' AND
        `costs`=' . $split[3] . ' LIMIT 1';

        $link->query($query);

        $link->close();

        $dat = DateTime::createFromFormat('Y-m-d', $split[0]);

        echo ' від ' . $dat->format('d.m.y') . ' на суму ' . $split[3] . ' грн. ';

        exit;
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//Вибірка послуг

$arr_serv_name = array();
$arr_types = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_serv_name[$row["ID"]] = $row["NAME"];

    $arr_types[$row["ID"]][1] = '';
}

//ВИБІРКА ІСНУЮЧИХ ТИПІВ

$query = 'SELECT * FROM `type_ids`';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_types[$row["service_ID"]][$row["type_ID"]] = " (" . $row["name"] . ")";
}

#region Отримання списку кольорів
$_COLORS = array();

$query = 'SELECT * FROM `colors`';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_COLORS[$row['ID']] = new MyColor($row['ID'], $row['color'], $row['serv_ids'], $row['css_name']);
    }
}
#endregion

#region Вибірка витрат

$serv_out = array();
$serv_info = array();

$query = 'SELECT * FROM `service_in` order by `date_in` DESC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
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
}

$link->close();

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
                new HTEL('td/[0] шт.', $arr['count']),
                new HTEL('td &=text-align:right;/[0]', CostOut($arr['cost'])),
                new HTEL('td/[0]', $arr['comm']),
                new HTEL('td .=del_cell', new HTEL('input .=delrow ?=[0]_[1]_[2]_[3] #=X [ro]', [$date,$arr['id'],$arr['type'],$arr['cost']]))
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

echo $table($tbody);

?>

<script>

    $('.delrow').on('click', function () {
        $name = $(this).attr('name');
        if (!confirm('Підтвердіть видалення ' + $name)) return false;

        $.get('blok/jurnal.php', 'del=' + $name, function (result) {
            alert('Запис [' + result + '] видалено.');
            document.location = 'work?page=jurnal';
        });
    });

</script>