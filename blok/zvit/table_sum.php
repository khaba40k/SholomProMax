<?php

//$_GET['ot'] = '2000-01-01';
//$_GET['do'] = '2024-02-15';

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";

$table = new HTEL('table .=tbl_sum');

$table(new HTEL('caption .=onlyPrint/Звіт за період: [0]р. - [1]р.', [dateToNorm($_GET['ot']), dateToNorm($_GET['do'])]));

$tbody = new HTEL('tbody');

$tr = new HTEL('tr');

$tr(new HTEL('th /Назва послуги'));
$tr(new HTEL('th colspan=2/Витрати'));
$tr(new HTEL('th /Списано'));
$tr(new HTEL('th colspan=2/Надходження'));
$tr(new HTEL('th /ВСЬОГО'));

$tbody($tr);

//Вибірка послуг

mysqli_query($link, 'SET SQL_BIG_SELECTS = 1');

$arr_serv_ids = array();
$arr_serv_types = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_serv_ids[] = $row["ID"];
}

$query = 'SELECT service_ID, type_ID FROM type_ids';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_serv_types[$row['service_ID']][] = $row['type_ID'];
}

$arr_serv_types[19] = $arr_serv_ids;

$type_variants = array();

//Заповнення таблиці

foreach ($arr_serv_ids as $i) {

    $type_variants = $arr_serv_types[$i] ?? [1];

    foreach ($type_variants as $t){

        $query = 'SELECT * FROM (SELECT string_name, string_type, SUM(_count) as _cnt_in, SUM(_cost) AS _sum_in FROM get_view_in WHERE _id = ' . $i . ' AND _type = ' . $t . ' AND _count > 0 AND _date >= "' . $_GET['ot'] . '" AND _date <= "' . $_GET['do'] . '") _in
                 INNER JOIN (SELECT SUM(_count)*-1 as _cnt_sps, SUM(_cost)*-1 AS _sum_sps FROM get_view_in WHERE _id = ' . $i . ' AND _type = ' . $t . ' AND _count < 0 AND _date >= "' . $_GET['ot'] . '" AND _date <= "' . $_GET['do'] . '") _sps
                 INNER JOIN (SELECT string_name AS string_name2, string_type AS string_type2, SUM(_count) AS _cnt_out, SUM(_cost) AS _sum_out FROM get_view_out WHERE _id = ' . $i . ' AND _type = ' . $t . '  AND _date >= "'.$_GET['ot'].'" AND _date <= "' . $_GET['do'] . '") _out';

        $result = mysqli_query($link, $query);

        foreach ($result as $row){
            $result = $row;
            break;
        }

        $name_ind = $result['string_name'] !== null ? '' : '2';

        if ($result['_sum_out'] != null || $result['_sum_in'] != null || $result['_cnt_sps'] != null){
            $tr = new HTEL('tr &=border-style:inherit;');

            $tr(new HTEL('td !=[0]_[1] .=sum_info &=text-align:left;/[2]', [
                $i,
                $t,
                $result['string_name'. $name_ind] .
                ($result['string_type'. $name_ind] !== null ? ' (' . $result['string_type'. $name_ind] . ')' : '')
            ]));
            $tr(new HTEL('td &=text-align:center;/[0]', $result['_cnt_in'] ?? '-'));
            $tr(new HTEL('td &=text-align:right;/[0]', $result['_sum_in'] ?? '-'));
            $tr(new HTEL('td &=text-align:center;/[0]', $result['_cnt_sps'] ?? '-'));
            $tr(new HTEL('td &=text-align:center;/[0]', $result['_cnt_out'] ?? '-'));
            $tr(new HTEL('td &=text-align:right;/[0]', CostOut(round($result['_sum_out'], 2), '-')));
            $tr(new HTEL('td &=text-align:right;font-weight:bold;/[0]',
            CostOut(round($result['_sum_out'], 2) - $result['_sum_in'] - $result['_sum_sps'], '-')));

            $tbody($tr);
        }

    }

}

//<tr style="border-style:inherit;">
//                                <td id="10_1" class="sum_info" style="text-align:left;">
//                                        Заробітна плата (працівники)
//                                </td>
//                                <td style="text-align:center;">
//                                        17
//                                </td>
//                                <td style="text-align:right;">
//                                        23600.00
//                                </td>
//                                <td style="text-align:center;">
//                                        -
//                                </td>
//                                <td style="text-align:center;">
//                                </td>
//                                <td style="text-align:right;">
//                                        -
//                                </td>
//                                <td style="text-align:right;font-weight:bold;">
//                                        -23600.00
//                                </td>
//                        </tr>

$tr = new HTEL('tr');

$tr(new HTEL('th &=text-align:center;/РАЗОМ'));

$sum_result = 0;

$result = mysqli_query($link, 'SELECT SUM(_cost) as _cost FROM get_view_in WHERE _count > 0 AND _date >= "' . $_GET['ot'] . '" AND _date <= "' . $_GET['do'] . '"');
foreach ($result as $row) {
    $tr(new HTEL('th colspan=2 &=text-align:right;/[0]', $row['_cost']));
    $sum_result -= $row['_cost'];
}

$result = mysqli_query($link, 'SELECT SUM(_count)*-1 as _count FROM get_view_in WHERE _count < 0 AND _date >= "' . $_GET['ot'] . '" AND _date <= "' . $_GET['do'] . '"');
foreach ($result as $row) {
    $tr(new HTEL('th &=text-align:center;/[0]', $row['_count']));
}

$result = mysqli_query($link, 'SELECT SUM(_cost) as _cost FROM get_view_out WHERE _date >= "' . $_GET['ot'] . '" AND _date <= "' . $_GET['do'] . '"');
foreach ($result as $row) {
    $tr(new HTEL('th colspan=2 &=text-align:right;/[0]', CostOut($row['_cost'])));
    $sum_result += CostOut($row['_cost']);
}

$tr(new HTEL('th &=text-align:right;/[0]', CostOut($sum_result)));

$tbody($tr);

$table($tbody);

echo $table;

$link->close();

?>


<script>

    $('.sum_info').on('click', function () {

        var id_type = $(this).attr('id').split('_');

        $.ajax({
        url: 'blok/zvit/sum_info.php',
        method: 'GET',
            dataType: 'html',
            data: 'id=' + id_type[0] + '&type=' + id_type[1],
        success: function (data) {
            $('#workfield').html(data);
        }
    });

    });

</script>