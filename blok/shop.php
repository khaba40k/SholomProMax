<?php

//var_dump($_GET);
//exit;

//Вхідні дані
//["count_16_1"]=> string(1) "1" ["cost_16_1"]=> string(1) "0" ["color_6_2"]=> string(1) "1" ["count_6_2"]=> string(1) "1" ["cost_6_2"]=> string(1) "0"

require("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//1 замовлення (абонент)
//2 замовлення (вручну)
//4 покупка (абонент)
//8 покупка (вручну)
//16 витрати

$attr = 4;

#region Отримання масиву послуг
$_service_name = array();
$_service_type = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
         if (inclAttr($attr, $row['atr'])){
            $_service_name[$row['ID']] = $row['NAME'];
            $_service_type[$row['ID']][1] = '';
        }
    }
}
#endregion

#region Отримання існуючих типів
$query = 'SELECT * FROM `type_ids`';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_service_type[$row['service_ID']][$row['type_ID']] = $row['name'];
    }
}

#endregion

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

#region ВИБІРКА ЦІН
$arr_cst = array();

$query = 'SELECT * FROM `price_list`';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_cst[$row["service_id"]][$row["type_id"]] = $row["cost"];
}
#endregion

$link->close();

$div = new HTEL('div !=shop_div');

$counter = 1;

foreach ($_service_name as $id => $name) {

    foreach ($_service_type[$id] as $t=>$type) {

        $temp1 = new HTEL('div !=[0]_[1]_cell .=shop_cell', [$id, $t, $counter]);
        //$temp1(new HTEL('div .=shop_img ', '/img/' . $id . '.' . $t));
        //&=background-image:url(..[0].png);
        $temp1_2 = new HTEL('div .=shop_bott');

        $name_type = $type != '' ? $name . ' ('  . $type . ')':$name;

        $temp1_2(new HTEL('lable/[0]', $name_type));

        $temp1_2(new HTEL('input *=number !=s_[2] ?=s_[2] #=[0] min=0 &=display:none; [ro]'));
        $temp1_2(new HTEL('input *=number ?=type_[2] #=[1] min=0 &=display:none; [ro]'));

        $temp1_2(new HTEL('input *=number !=cost_[2] #=[0] min=0 &=display:none; [ro]', $arr_cst[$id][$t]));
        $temp1_2(new HTEL('input *=number ?=price_[2] #=0 min=0 &=display:none; [ro]'));

        $temp1_3 = new HTEL('div .=shop_counter');
        $temp1_3(new HTEL('button !=[0]_[1]_up .=but_up/+'));
        $temp1_3(new HTEL('input *=number !=[0]_[1] ?=count_[2] .=count_inp #=0 min=0 [ro]'));
        $temp1_3(new HTEL('button !=[0]_[1]_dw .=but_dw/-'));

        $temp1([$temp1_2, $temp1_3]);

        $div($temp1);

        $counter++;
    }

}

echo $div;

?>

<script>

    $('.but_up').on('click', function () {

        var id = $(this).attr('id').split('_');

        var curinp = $('#' + id[0] + '_' + id[1]).val();

        $('#' + id[0] + '_' + id[1]).val(0);

        curinp++;

        $('#' + id[0] + '_' + id[1]).val(curinp);

        if (curinp > 0) {
            $('#'+ id[0] + '_' + id[1] + '_cell').attr('style', 'background-color: yellow;');
        } 

    });

    $('.but_dw').on('click', function () {

        var id = $(this).attr('id').split('_');

        var curinp = $('#' + id[0] + '_' + id[1]).val();

        //$('#' + id[0] + '_' + id[1]).val(0);

        curinp--;

        if (curinp > 0) {
            $('#' + id[0] + '_' + id[1]).val(curinp);
        }
        else {
            $('#' + id[0] + '_' + id[1]).val(0);
            $('#' + id[0] + '_' + id[1] + '_cell').attr('style', 'background-color: olivedrab;');
        }

    });

</script>