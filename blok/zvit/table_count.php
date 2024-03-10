<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$arr_cnt = array();

$conn = new SQLconn();

#region Вибірка послуг
     $arr_serv_name = array();
     $arr_types = array();

     $result = $conn('SELECT `NAME`, `ID` FROM `service_ids` where `color` = 1 ORDER BY `order` ASC');

     foreach ($result as $row) {
         $arr_serv_name[$row["ID"]] = $row["NAME"];
         $arr_types[$row["ID"]][1] = '';
     }
#endregion

#region ВИБІРКА ІСНУЮЧИХ ТИПІВ
     $result = $conn('SELECT * FROM type_ids');

     foreach ($result as $row) {
         $arr_types[$row["service_ID"]][$row["type_ID"]] = "(" . $row["name"] . ")";
     }
#endregion

#region Отримання списку кольорів
$_COLORS = array();

$result = $conn('SELECT * FROM colors');

$map = $conn('SELECT * FROM color_map');

foreach ($result as $row) {
    $_COLORS[$row['ID']] = new MyColor2($row['ID'], $row['color'], $map, $row['css_name'], $row['is_def']);
}
#endregion

#region Отримання масиву виконаних ІД $arr_done_id
    $arr_done_id= array();

    $query = 'SELECT `ID` FROM `client_info`
              where `TTN_IN` IS NOT NULL OR `TTN_OUT` IS NOT NULL';

    $result = $conn($query);

    foreach ($result as $row) {
        $arr_done_id[] = $row["ID"];
    }
#endregion

#region Вибірка приходу товарів на склад

foreach ($arr_serv_name as $i => $n) {
    $query = 'SELECT `type_ID`,`color`,`count` FROM `service_in`
              where `service_ID` = ' . $i . ' AND `color` IS NOT NULL';

    $result = $conn($query);

    foreach ($result as $row) {
        if (!isset($arr_cnt[$i][$row["type_ID"]][$row["color"]]))
            $arr_cnt[$i][$row["type_ID"]][$row["color"]] = 0;

        $arr_cnt[$i][$row["type_ID"]][$row["color"]] += $row["count"];
    }
}

#endregion

#region Вибірка відправлених комплектуючих

foreach ($arr_done_id as $ID){

    $query = 'SELECT * FROM `service_out`
              where `ID` = ' . $ID . ' AND `color` IS NOT NULL';

    $result = $conn($query);

    foreach ($result as $row) {
        if (!isset($arr_cnt[$row["service_ID"]][$row["type_ID"]][$row["color"]]))
            $arr_cnt[$row["service_ID"]][$row["type_ID"]][$row["color"]] = 0;

        $arr_cnt[$row["service_ID"]][$row["type_ID"]][$row["color"]] -= $row["count"];
    }
}

#endregion

$conn->close();

#region Заповнення таблиці
$table = new HTEL('table .=tbl_count');
$table(new HTEL('caption/ЗАЛИШКИ КОМПЛЕКТУЮЧИХ станом на [0]p.', date('d.m.Y')));

$tbody = new HTEL('tbody');

$tr = new HTEL('tr');
$tr(new HTEL('th/НАЗВА'));

foreach($_COLORS as $c){
    $tr(new HTEL('th &=background-color:[1]; .=color_cell/[0]', [$c->NAME, $c->CSS_ANALOG] ));
}

$tr(new HTEL('th/РАЗОМ'));

$tbody($tr);

foreach ($arr_serv_name as $id => $ni) {
    foreach ($arr_types[$id] as $t => $nt) {
        if (isset($arr_cnt[$id][$t])) {
            $tr = new HTEL('tr', [$ni, $nt, $id, $t]);
            $tr(new HTEL('td !=[2]_[3] .=info_cnt_cell &=text-align:left;/[0] [1]'));

            foreach ($_COLORS as $c) {
                $cnt = isset($arr_cnt[$id][$t][$c->ID]) ? $arr_cnt[$id][$t][$c->ID] : 0;
                $tr(new HTEL('td !=[2]_[3]_[4] .=info_cnt_cell_color &=text-align:center;background-color:[1];/[0]', [MyVal($cnt), ($cnt < 3 && $cnt != 0) ? '#E3242B':'auto', 4=>$c->ID]));
            }

            $sum_arr = sumArray($arr_cnt[$id][$t]);

            $tr(new HTEL('td &=text-align:center;font-weight:bold;background-color:[1];/[0]',
            [MyVal($sum_arr), $sum_arr < 0 ? '#E3242B' : 'auto' ]));

            if ($sum_arr < 0)  $tr->setAtr('style' , 'border: 2px solid red;background-color:#FA8072;');

            $tbody($tr);
        }
    }
}

$table($tbody);

echo $table;

echo new HTEL('div !=detal_info .=no-print &=margin-top:50px;');

#endregion

function MyVal($val):string{

    if ($val == 0)
        return '-';

    return $val;
}

?>

<script>
    $('.info_cnt_cell').on('click', function () {
        var id_type = $(this).attr('id').split('_');
        var name = $(this).text();

        $.ajax({
            url: 'blok/zvit/count_info.php',
            method: 'get',
            dataType: 'html',
            data: 'ID=' + id_type[0] + '&TYPE=' + id_type[1] + '&NAME=' + name,
            success: function (data) {
                $('#detal_info').html(data);
            }

        });
    });

    $('.info_cnt_cell_color').on('click', function () {
        var id_type_color = $(this).attr('id').split('_');
        var name = $('#' + id_type_color[0] + '_' + id_type_color[1]).text();

        $.ajax({
            url: 'blok/zvit/count_info.php',
            method: 'get',
            dataType: 'html',
            data: 'ID=' + id_type_color[0] + '&TYPE=' + id_type_color[1] + '&NAME=' + name + '&COLOR=' + id_type_color[2],
            success: function (data) {
                $('#detal_info').html(data);
            }

        });
    });
</script>