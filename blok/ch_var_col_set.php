<?php

require "conn_local.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//Вхідні дані
//["sposob"]=> string(0) "" ["color_0_1"]=> string(1) "2" ["count_0_1"]=> string(1) "1" ["cost_0_1"]=> string(6) "1500.9" ["count_1_1"]=> string(1) "1" ["cost_1_1"]=> string(3) "190" ["color_6_2"]=> string(1) "1" ["count_6_2"]=> string(1) "1" ["cost_6_2"]=> string(3) "444" ["is_rewrite"]=> string(1) "1"

$attr = 2;//

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

#region Отримання списку цін
$_COASTS = array();

$query = 'SELECT * FROM `price_list`';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_COASTS[$row['service_id']][$row['type_id']] = $row['cost'];
    }
}
#endregion

#region Отримання масиву послуг
$_service_id = array();
$_service_name = array();
$_service_has_color = array();
$_dep_map = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
         if(inclAttr($attr, $row['atr'])){
                 $_service_name[$row['ID']] = $row['NAME'];

                 if ($row['dep'] !== null){
                     $_dep_map[$row['ID']] = explode('/', $row['dep']);
                 }

                 if ($row['color'] == '1') {
                     $_service_has_color[$row['ID']] = true;
                 } else {
                     $_service_has_color[$row['ID']] = false;
                 }
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

#region ВИБІРКА ЗАЛИШКІВ
$arr_cnt = array();

$query = 'SELECT service_ID, type_ID, color, count FROM `service_in` WHERE color IS NOT NULL';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    if (!isset($arr_cnt[$row['service_ID']][$row['type_ID']][$row['color']])) {
        $arr_cnt[$row['service_ID']][$row['type_ID']][$row['color']] = 0;
    }

    $arr_cnt[$row['service_ID']][$row['type_ID']][$row['color']] += $row['count'];
}

$query = 'SELECT service_ID, type_ID, color, count FROM `service_out`
JOIN client_info ON service_out.ID=client_info.ID
WHERE color IS NOT NULL AND (TTN_IN IS NOT NULL OR sold_number IS NOT NULL)';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    if (!isset($arr_cnt[$row['service_ID']][$row['type_ID']][$row['color']])) {
        $arr_cnt[$row['service_ID']][$row['type_ID']][$row['color']] = 0;
    }

    $arr_cnt[$row['service_ID']][$row['type_ID']][$row['color']] -= $row['count'];
}
#endregion

$link->close();

if (isset($_GET['set_select'])){
    $t = $_GET['set_type'] != '' ? $_GET['set_type'] : 1;
    $right = new HTEL('div', rightFor($_GET['set_select'], $t));
    echo '₴' . $right->GetChildren();
    exit;
}

echo new HTEL('script/var DEP_MAP = [0];', json_encode($_dep_map));

#region Логіка
foreach ($_service_name as $i=>$n) {

    $typesForId = $_service_type[$i] ?? null;

    echo print_serv(
        $i,
        $n,
        $typesForId
    );
}
#endregion

//Методи

function print_serv($serv_ID, $serv_name, $_types): HTEL
{
    $div = new HTEL('div .=opt_color', [$serv_ID, 2=>$serv_name]);

    $div->LEVEL = 2;

    $left = new  HTEL('div !=left_menu_[0] .=left_z_menu');

    $type_vars = null;

    $cur_type = 1;

    if (isset($_types)){

        foreach($_types as $t=>$nm){
            if  (isset($_GET['count_' . $serv_ID . '_' . $t])){
                $cur_type = $t;
                break;
            }
        }

        $type_vars = new HTEL('select !=type_[0] ?=type_[0] .=type_variant');

        if (count($_types) > 1){
            foreach($_types as $t=>$n){
                  $type_vars(new HTEL('option #=[0] [3]/[2] ([1])', [$t, $n, 3=>SelectTypeStatus($serv_ID, $cur_type)]));
            }
        }
        else{
            $type_vars = new HTEL('label for=color_[0]/[2] ([1])', [1=>$_types[1]]);
        }
    }
    else{
        $type_vars = new HTEL('label for=color_[0]/[2]');
    }

    $left($type_vars);

    $status_in = SelectStatus(19, $serv_ID);

    if ($GLOBALS['_service_has_color'][$serv_ID] == 1){

        $left(new HTEL('div .=convers',[

                new HTEL('label for=convers_[0] /мої компл.  (установка)'),
                new HTEL(
                    'input *=checkbox !=convers_[0] ?=convers_[0] .=convers #=[0] [1]',
                    [1 => $status_in]
                )

        ]));
    }

    if ($status_in != ''){
        $cur_type = 0;
    }

    $right = new HTEL('div !=right_menu_'.$serv_ID.' .=right_menu/₴', rightFor($serv_ID, $cur_type));

    $div([
        $left,
        $right
    ]);

    return $div;
}

function rightFor($serv_id, $serv_type = 1):array{
    $_colors = $GLOBALS['_COLORS'];
    $_has_col = $GLOBALS['_service_has_color'][$serv_id];
    $colorIN = $_has_col ? $_GET['sposob'] : "";

    $ustanovka = 19;

    $cost = isset($GLOBALS['_COASTS'][$serv_id][$serv_type]) ?
    $GLOBALS['_COASTS'][$serv_id][$serv_type] :
    $GLOBALS['_COASTS'][$ustanovka][1];

    $count = $GLOBALS['arr_cnt'][$serv_id][$serv_type];

    if (isset($_GET['cost_' . $serv_id . '_' . $serv_type])){
        $cost = $_GET['cost_' . $serv_id . '_' . $serv_type];
    } else if (isset($_GET['cost_' . $ustanovka . '_' . $serv_id])){
        $cost = $_GET['cost_' . $ustanovka . '_' . $serv_id];
    }

    $AnyCreator = $_GET['IS_ADMIN'] < 0 ? 'readonly': '';

    $coastInp = new HTEL('input *=number step=0.01 min=0 ?=cost_[0] #=[1] [2]', [$serv_id, $cost, $AnyCreator]);

    $curCol = null;

    if ($colorIN != ''){
        foreach ($_colors as $c) {
            if ($c->ID == $colorIN) {
                $curCol = $c;
                break;
            }
        }
    }

    $right = new HTEL();

    if ($serv_type > 0){
        if ($_has_col) {
            if (!is_null($curCol) && $curCol->AppleTo($_colors, $serv_id, $serv_type) && $count[$curCol->ID] > 0) { //колір підходить
                    $right = new HTEL('input *=checkbox !=color_[0] ?=cb[0] #=[1] [2]',
                [1 => $curCol->ID, SelectStatus($serv_id, $serv_type, $curCol->ID)]);
            } else { //не підходить

                $right = new HTEL('select !=color_[0] ?=color_[0] .=colorselector', $serv_id);

                $right(new HTEL('option #=- [0]/x', SelectStatus($serv_id, null, 'selected')));

                foreach ($_colors as $c) {
                    if ($c->AppleTo($_colors, $serv_id, $serv_type)) {
                        if ($_GET['is_rewrite'] == 1 || $count[$c->ID] > 0) {
                            $right(new HTEL('option #=[0] [1]/+[2]', [$c->ID, SelectStatus($serv_id, $serv_type, $c->ID, 'selected'), $c->NAME]));
                        }
                    }
                }
            }
        } else {
            $right = new HTEL('div .=chb_yesno', [$serv_id, SelectStatus($serv_id, $serv_type)]);

            $lableYes = new HTEL('label for=color_[0]/ТАК');
            $lableYes(new HTEL('input *=radio !=color_[0] ?=cb[0] # [1] [r]'));

            $lableNo = new HTEL('label for=color_[0]_n/НІ');
            $lableNo(
                new HTEL(
                    'input *=radio !=color_[0]_n ?=cb[0] #=- [2]',
                    [
                        1 => ($_GET['is_rewrite'] == 1 ? SelectStatus($serv_id, $serv_type, null, 'checked') : ''),
                        2 => (SelectStatus($serv_id, $serv_type, null, 'checked') == '' &&
                            $_GET['is_rewrite'] == 1) ? 'checked' : ''
                    ]
                ));

            $right([
                $lableYes,
                $lableNo
            ]);
        }
    }

    return [$coastInp, $right];
}

function SelectStatus($id, $type, $colid = null, $out = 'checked'):string{

    $id_type = $id . '_' . $type;

    if (isset($_GET['count_'.$id_type])){

        if (isset($_GET['color_' . $id_type]) && !is_null($colid)) {
            return $_GET['color_' . $id_type] == $colid ? $out : '';
        }
        else{
            return $out;
        }
    }

    return '';
}

function SelectTypeStatus($id, $type) :string{
    if  ($type > 1 && isset($_GET['count_'.$id.'_'.$type])) return 'selected';

    return '';
}
?>

<script>
    $('.convers, .colorselector').on('click', function () {
        console.log($(this).attr('name') + ' => [' + $(this).val() + ']');
    });

    $('.type_variant').change(function () {

        var serv_type = $(this).val();

        var serv_id = $(this).attr('id').split('_')[1];

        setRight(serv_id, serv_type);
    });

    $('.convers').change(function () {
        serv_id = $(this).val();

        if ($(this).is(":checked")) {
            setRight(serv_id, 0);
        }
        else {
            _type = $('#type_' + serv_id).length > 0 ? $('#type_' + serv_id).val() : 1;
            setRight(serv_id, _type);
        }
    });

    function setRight(id, type) {
        $.ajax({
            url: 'blok/ch_var_col_set.php',
            method: 'GET',
            dataType: 'html',
            data: '&set_select=' + id + '&set_type=' + type 
        <?php echo " + '&sposob=" . $_GET['sposob'] . "' + '&IS_ADMIN=" . $_GET['IS_ADMIN'] . "'" ?>,
            success: function (data) {
                $('#right_menu_' + id).html(data);
            }
        });
    };

    
</script>