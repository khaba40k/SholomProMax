<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
require "conn_local.php";

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

#region Отримання масиву послуг
$_service_name = array();
$_service_type = array();

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_service_name[$row['ID']] = $row['NAME'];
        $_service_type[$row['ID']][1] = '';
    }
}
#endregion

#region Отримання типів

$query = 'SELECT * FROM `type_ids`';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_service_type[$row['service_ID']][$row['type_ID']] = $row['name'];
    }
}

#endregion

$link->close();

$in = '';
$emp = array();
$tmp = array();
$cnt = array();
//&cost_21=300&s_1=6&type_1=1&color_1=2&count_1=1&price_1=2000.00&is_rewrite=0&typeZ=sold0&ID=69

$sum = 0;

if ($_GET['typeZ'] == 'def0'){
    foreach ($_GET as $k => $v) {

        $tmp = explode('_', $k, 2);

        if (count($tmp) == 2) {
            if ($tmp[0] == 'color' && ($v == '-' || $v == '')) {
                $emp['cost_' . $tmp[1]] = 0;
                $emp['type_' . $tmp[1]] = 0;
                $emp['color_' . $tmp[1]] = 0;
            }
        } else if (substr($k, 0, 2) == 'cb' && $v == '-') {
            $emp[$k] = 0;
            $emp['cost_' . substr($k, 2)] = 0;
        }
    }

    unset($tmp);

    foreach ($emp as $del => $nul) {
        unset($_GET[$del]);
    }

    unset($emp);

    foreach ($_GET as $k => $v) {
        $in .= '&' . $k . '=' . $v;
        if (substr($k, 0, 4) == 'cost') {
            $id = substr($k, 5);

            $tmp[$id] = $v;
            $emp[$id] = $_GET['color_' . $id] ?? null;
            $cnt[$id] = !is_null($emp[$id]) ? 1:'';
            $sum += $v;
        }
    }
}else{
    if (isset($_GET['cost_21'])) {
        $tmp[21] = $_GET['cost_21'];
        $sum += $_GET['cost_21'];
    }

    for ($i=1; $i < 20; $i++){
        $id = $_GET['s_' . $i] ?? null;

        if ($id !== null){
            $tmp[$id] = $_GET['price_'.$i];
            $emp[$id] = $_GET['color_' . $i] ?? null;
            $cnt[$id] = !is_null($emp[$id]) ? ($_GET['count_'.$i] ?? 0) : '';
            $_GET['type_' . $id] = $_GET['type_' . $i] ?? 1;
            $sum += $tmp[$id];
        }
    }
}

$out = new HTEL('tbody');

foreach($tmp as $k=>$v){
    $col = isset($emp[$k]) ? (' (' . $_COLORS[$emp[$k]] . ')') : '';
    $type = $_GET['type_' . $k] ?? 1;

    $out(new HTEL('tr', [
        $_service_name[$k],
        $_service_type[$k][$type],
        $col,
        $cnt[$k],
        CostOut($v),
        new HTEL('td &=padding:2px;/[0] [1] [2]'),
        new HTEL('td &=text-align:center;padding:2px;/[3]'),
        new HTEL('td &=text-align:right;padding:2px;/[4]')
    ]));
}

$out(new HTEL('tr', [
    CostOut($sum),
    new HTEL('th colspan=2/До сплати'),
    new HTEL('th /[0]')
]));

$dialog_window=new HTEL('form !=dialog_form .=dialog_w onSubmit=return+rec(`[0]`);', $in);

$dialog_window(new HTEL('label !=top_dialog_lbl/Послуги'));

$dialog_window(new HTEL('button !=but_close *=button #=click/X'));

$dialog_window(new HTEL('table &=width:100%;font-size:130%;', $out));

$dialog_window(new HTEL('button !=but_apply *=submit/ЗАМОВИТИ'));

echo $dialog_window;
?>

<script>

    $('#but_close').on('click', function () {
        $(this).parent().parent().html("");
    });

    function rec($get) {
        $.ajax({
        url: 'blok/record_new_z.php',
        method: 'GET',
        dataType: 'html',
        data: $get,
        success: function (data) {
            $('#workfield').html(data);
        }
        });

        return true;
    }

</script>