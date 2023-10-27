<?php

//var_dump($_GET);
//exit;

//Вхідні дані
//["count_16_1"]=> string(1) "1" ["cost_16_1"]=> string(1) "0" ["color_6_2"]=> string(1) "1" ["count_6_2"]=> string(1) "1" ["cost_6_2"]=> string(1) "0"

require("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

if (!isset($_GET['for'])) $_GET['for'] = 'in';  //in - витрати списання / out - покупка

$attr = $_GET['for'] == 'in' ?  16: 28;

#region Отримання масиву послуг
$_service_id = array();
$_service_name = array();
$_service_has_color = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
         if (inclAttr($attr, $row['atr'])){
                 $_service_id[] = $row['ID'];
                 $_service_name[] = $row['NAME'];
                 $_service_has_color[$row['ID']] = $row['color'];
         }
    }
}
#endregion

#region Отримання типів
$_service_type = array();

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
        $_COLORS[] = new MyColor($row['ID'], $row['color'], $row['serv_ids'], $row['css_name']);
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

#region Перемальовка таблиці динамічна
if (isset($_GET['typeFor'])) {
    echo typeCell($_GET['row_id'], $_GET['typeFor']);
    exit;
}
if (isset($_GET['color_for'])) {
    echo colorCell($_GET['row_id'], $_GET['color_for'], $_GET['type']);
    exit;
}
if (isset($_GET['price_for'])) {
    $count = isset($_GET['price_count']) ? $_GET['price_count'] : 1;

    echo costCell($_GET['row_id'], $_GET['price_for'], $_GET['type'], $count);
    exit;
}
if (isset($_GET['count'])) {
    echo countCell($_GET['row_id'], $_GET['count']);
    exit;
}
if (isset($_GET['newRow'])) {
    echo emptyRow($_GET['newRow']);
    exit;
}
#endregion

?>

<script>
    $row_count = 1;
    $lastId = 1;

    function setLastId(newVal) {
        $lastId = newVal;
    }

    function validateFormMy() {//Внесення даних після перевірки

        var out = true;
        var id;

        $('#exp_tabl_body input').each(function () {

            id = $(this).attr('id');

            if (id.substring(0, 8) == 'count_s_') {//перевірка на кількість != 0
                if ($(this).val() == 0) {
                    out = false;
                    $(this).effect("highlight", 1000);
                }
            }
            else if (id.substring(0, 8) == 'price_s_') {//Перевірка на пусті суми
                if ($('#'+id.substring(6)).val() != '' && $(this).val() == '') {
                    out = false;
                    $(this).effect("highlight", 1000);
                }
            }

        });

        if ($row_count == 1) {
            $('#exp_tabl_body select').each(function () {
                if ($(this).val() == '') {
                    $(this).effect("highlight", 1000);
                    out = false;
                }
            });
        }

        if (out) {//Валідація успішна, запис

            let dataForm = $('#tbl_expenses select').serialize() + "&" + $('#tbl_expenses :input').serialize();

            $.ajax({
                url: 'blok/record_new_in.php',
                method: 'GET',
                dataType: 'html',
                data: dataForm + '&lastind=' + $lastId,
                    success: function (data) {
                        $('#workfield').html(data);
                    }
             });
        }

        return false;
    };

    function addRow($for = 'in') {

        if ($('#s_' + $lastId).val() == '') {
            $('#s_' + $lastId).effect("highlight", 1000);
            return;
        }

        var row = document.getElementById("exp_tabl_body").insertRow();

        $lastId++;

        $row_count++;

        $.get('blok/expenses.php', 'newRow=' + $lastId + '&for=' + $for, function(result)
        {
            row.innerHTML = result;
        });
    };

        $(".expenses_table").on("change", "select", function() {

            var row = $(this).attr('id');//s_1 / t_1
            var serv_id = $(this).val();//serv id/color_id/type_id

            if (row.substring(0, 2) == 's_') {//Якщо змінено назву послуги

                $('#comm_' + row).attr('required', false);
                $('#comm_' + row).attr('placeholder', '');

                if ($lastId > 0 && serv_id == '') {//Видалення рядка при пустих значеннях
                    $(this).parent().parent().remove();
                    $row_count--;
                    if (row == 's_' + $lastId) $lastId--;
                    return;
                }

                var td_color = document.getElementById('colorcell_' + row);
                var td_type = document.getElementById('typecell_' + row);
                var td_count = document.getElementById('countcell_' + row);
                var td_cost = document.getElementById('pricecell_' + row);

                console.log(row + ' - ' + serv_id);

                $.get('blok/expenses.php', 'row_id=' + row.substring(2) + '&typeFor=' + serv_id, function (result) {
                    td_type.innerHTML = result;
                });

                $.get('blok/expenses.php', 'row_id=' + row.substring(2) + '&count=' + serv_id, function (result) {
                    td_count.innerHTML = result;
                });

                $.get('blok/expenses.php', 'row_id=' + row.substring(2) + '&color_for=' + serv_id + '&type=1', function (result) {
                    td_color.innerHTML = result;
                });

                    <?php
                    if ($_GET['for'] == 'out') {
                        echo "$.get('blok/expenses.php', 'row_id=' + row.substring(2) +
                             '&price_for=' + serv_id +
                             '&type=1', function (result) {
                              td_cost.innerHTML = result;
                              });";
                    }

                    ?>

            }
            else if (row.substring(0, 5) == 'type_') {//Якщо змінено назву типу

                var row_type = row.split('_');
                var td_color = document.getElementById('colorcell_s_' + row_type[1]);
                var td_cost = document.getElementById('pricecell_s_' + row_type[1]);
                var type = $(this).val();

                $.get('blok/expenses.php', 'row_id=' + row_type[1] +
                    '&color_for=' + row_type[2] +
                    '&type=' + type, function (result) {
                        td_color.innerHTML = result;
                });

                    <?php
                    if ($_GET['for'] == 'out') {
                        echo "$.get('blok/expenses.php', 'row_id=' + row_type[1] +
                               '&price_for=' + row_type[2] +
                               '&type=' + type, function (result) {
                                   td_cost.innerHTML = result;
                                });";
                    }

                ?>

            }
        });

    $('#exp_tabl_body').on('change', 'input', function () {//Перевірка на обов'язковий коментар

        var idrow = $(this).attr('id');
        var price_cell;
        var comm_cell;
        var count_cell;
        var type_cell;
        var id_cell;

        idrow = idrow.substring(8);

        id_cell = '#s_' + idrow;
        type_cell = '#type_' + idrow + '_' + $(id_cell).val();
        count_cell = '#count_s_' + idrow;
        comm_cell = '#comm_s_' + idrow;
        price_cell = '#price_s_' + idrow;

        var type = $(type_cell).length ? $(type_cell).val() : 1;
        var count = $(count_cell).length ? $(count_cell).val() : 1;

        //Множенн суми

        if ($(count_cell).val() < 0) {
            $(comm_cell).attr('required', true);
            $(comm_cell).attr('minlength', 3);
            $(comm_cell).attr('placeholder', 'Причина списання');

            $(price_cell).val('0.00');
            $(price_cell).attr('disabled', true);
        }
        else {
            $(comm_cell).attr('required', false);
            $(comm_cell).attr('minlength', 0);
            $(comm_cell).attr('placeholder', '');

            $(price_cell).attr('disabled', false);
            if ($(price_cell).val() == 0) {
                $(price_cell).val('');
            } else if ($(this).attr('id') == 'count_s_' + idrow) {
                         <?php
                    if ($_GET['for'] == 'out') {
                        echo "$.get('blok/expenses.php', 'row_id=' + idrow +
                            '&price_for=' + $(id_cell).val() +
                            '&type=' + type + '&price_count=' + count, function (result) {
                                    document.getElementById('pricecell_s_' + idrow).innerHTML = result;
                        });";
                    }

                ?>
            }
        }

    });
</script>


<?php

$form = new HTEL('form !=expenses ?=expensesForm onsubmit=return+validateFormMy()');

$table = new HTEL('table !=tbl_expenses .=expenses_table');

$caption = new HTEL('caption/Витрати (списання) на комплектуючі/послуги за ');

$caption(new HTEL('input ?=date_in *=date #=[0] max=[0]', date("Y-m-d")));

$tbody = new HTEL('tbody !=exp_tabl_body');

$tr = new HTEL('tr');

if ($_GET['for'] == 'in'){
    $tr([
        new HTEL('th &=width:[0]%;/НАЙМЕНУВАННЯ', 30),
        new HTEL('th &=width:[0]%;/Тип', 15),
        new HTEL('th &=width:[0]%;/Колір', 14),
        new HTEL('th &=width:[0]%;/К-сть (+/-)', 8),
        new HTEL('th &=width:[0]%;/Сума (грн)', 13),
        new HTEL('th &=width:[0]%;/ПОЯСНЕННЯ', 20)
    ]);
}else{
    $tr([
        new HTEL('th &=width:[0]%;/НАЙМЕНУВАННЯ', 25),
        new HTEL('th  &=width:[0]%;/Тип', 20),
        new HTEL('th &=width:[0]%;/Колір', 26),
        new HTEL('th &=width:[0]%;/К-сть', 12),
        new HTEL('th &=width:[0]%;/Сума (грн)', 17)
    ]);
}

if ($_GET['for'] == 'out'){//Продаж
    $caption = new HTEL();

    $tbody([
        $tr
    ]);

    $counter = 1;

    foreach ($_service_id as $id){
        if (isset($_service_type[$id])){
             foreach ($_service_type[$id] as $t=>$n){
                if (isset($_GET['count_'.$id.'_'.$t])){
                    $tbody(emptyRow($counter, $id, $t));
                    $counter++;
                }
             }
        }
        else{
            if (isset($_GET['count_' . $id . '_1'])) {
                $tbody(emptyRow($counter, $id));
                $counter++;
            }
        }
    }

    $tbody(new HTEL('script/setLastId([0]);', $counter));
    if ($counter == 1)
        $tbody(emptyRow());
}
else{//Витрати
    $tbody([
        $tr,
        emptyRow()
    ]);
}

$table([
    $caption,
    $tbody
]);

$div = new HTEL('div');
$div(new HTEL('button !=submit *=submit #=click/ВНЕСТИ ЗМІНИ'));

$form([
    $table,
    new HTEL('button !=butaddrow *=button onClick=javascript:addRow(`[0]`);/+', $_GET['for']),
    $div
]);

if ($_GET['for'] == 'in'){
    echo $form;
}
else{
    echo $table;
    echo new HTEL('button !=butaddrow *=button onClick=javascript:addRow(`[0]`);/+', $_GET['for']);
}

function emptyRow($cur_row_id = 1, $serv_id = null, $serv_type = 1):HTEL{

    $append_table = false;

    if (!is_null($serv_id)) {
        if (!isset($_GET['count_' . $serv_id . '_' . $serv_type])){
            return new HTEL();
        }
        $append_table = true;
    }

    $ids = $GLOBALS['_service_id'];

    $names = $GLOBALS['_service_name'];

    $out = new HTEL('tr !=row_[0]', $cur_row_id);

    $td = array();

    $td[0] = new HTEL('td');//name

    $reqv = $_GET['for'] == 'out'?'required':'';

    $select = new HTEL('select !=s_[0] ?=s_[0] [1]',[1=>$reqv]);

    $select(new HTEL('option #/'));

    $td[1] = new HTEL('td !=typecell_s_[0] .=type_cell'); //type

    $td[2] = new HTEL('td !=colorcell_s_[0]'); //color

    $td[3] = new HTEL('td !=countcell_s_[0] .=count_cell'); //count

    $td[4] = new HTEL('td !=pricecell_s_[0] .=price_cell'); //price

    if (!$append_table){//Пуста
        for ($i = 0; $i < count($ids); $i++) {
            $select(new HTEL('option #=[0]/[1]', [$ids[$i], $names[$i]]));
        }

        $td[4](new HTEL('input !=price_s_[0] ?=price_[0] *=number min=0 step=0.01 $=0.00'));
    }
    else{//З даними

        for ($i = 0; $i < count($ids); $i++) {
            $select(new HTEL('option #=[0] [2]/[1]', [$ids[$i], $names[$i], $ids[$i] == $serv_id ? 'selected':'']));
        }

        $td[1](typeCell($cur_row_id, $serv_id, $serv_type));

        $col = isset($_GET['color_' . $serv_id . '_' . $serv_type]) ? $_GET['color_' . $serv_id . '_' . $serv_type] : null;

        $td[2](colorCell($cur_row_id, $serv_id, $serv_type, $col));

        $td[3](countCell($cur_row_id, $serv_id, $_GET['count_' . $serv_id . '_' . $serv_type]));

        $td[4](new HTEL('input !=price_s_[0] ?=price_[0] *=number min=0 step=0.01 #=[1] $=0.00',
        [1=>$_GET['cost_'.$serv_id . '_' . $serv_type]]));
    }

    $td[0]($select);

    if ($_GET['for'] == 'in'){
        $td[5] = new HTEL('td'); //comm

        $td[5](new HTEL('input !=comm_s_[0] ?=comm_[0] *=text'));
    }

    $out($td);

    return $out;
}

function typeCell($row_id, $serv_id, $val = 1):HTEL{
    $types = isset($GLOBALS['_service_type'][$serv_id]) ? $GLOBALS['_service_type'][$serv_id] : null;

    if (!is_null($types)) {
        if (count($types) > 0) {
            $out = new HTEL('select !=type_[1]_[0] ?=type_[1]', [$serv_id, $row_id]);
            $out->LEVEL = 2;
            foreach ($types as $t => $n) {
                $out(new HTEL('option #=[0] [2]/[1]',[$t, $n, $t == $val ? 'selected': '']));
            }
            return $out;
        }
    }

    return new HTEL();
}

function colorCell($row_id, $serv_id, $type=1, $val=-1):HTEL{

    if (is_null($val))
        return new HTEL();

    $hascol = isset($GLOBALS['_service_has_color'][$serv_id]) ? $GLOBALS['_service_has_color'][$serv_id] : false;

    if (!$hascol){
        return new HTEL();
    }

    $colors = $GLOBALS['_COLORS'];

    $out = new HTEL('select !=color_s_[0] ?=color_[0] [r]', $row_id);

    $out->LEVEL = 2;

    $out(new HTEL('option # [d] [s]/x'));

    foreach($colors as $c){
        if ($c->AppleTo($colors, $serv_id, $type))
        {
               $out(new HTEL('option #=[0] [2]/[1]',[$c->ID, $c->NAME, $c->ID == $val ? 'selected' : '']));
        }
    }

    return $out;
}

function countCell($row_id, $serv_id, $val=1):HTEL{
    $hascol = isset($GLOBALS['_service_has_color'][$serv_id]) ? $GLOBALS['_service_has_color'][$serv_id] : false;

    $for_in = $_GET['for'] == 'in';

    if (!$hascol) {
        return new HTEL();
    }
    else{
        if ($for_in) {//Для списання /витрат
            $out = new HTEL('input !=count_s_[0] ?=count_[0] *=number #=[1] [r]', [$row_id, $val]);
        } else {//Для продаж
            $out = new HTEL('input !=count_s_[0] ?=count_[0] *=number #=[1] min=1 [r]', [$row_id, $val]);
        }
        $out->LEVEL = 2;
        return $out;
    }
}

function costCell($row_id, $serv_id, $type = 1, $count = 1): HTEL
{
    $val = isset($GLOBALS['arr_cst'][$serv_id][$type]) ? CostOut($GLOBALS['arr_cst'][$serv_id][$type] * $count) : '';

    $out = new HTEL();

    $out = new HTEL('input !=price_s_[0] ?=price_[0] *=number #=[1] min=0 step=0.01 $=0.00', [$row_id, $val]);

    $out->LEVEL = 2;

    return $out;
}

?>