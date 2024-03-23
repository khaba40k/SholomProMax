<?php

//var_dump($_GET);
//exit;
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$conn = new SQLconn();

#region Отримання списку кольорів
$_COLORS = array();

$result = $conn('SELECT * FROM colors');

$map = $conn('SELECT * FROM color_map');

foreach ($result as $row) {
    $_COLORS[$row['ID']] = new MyColor($row['ID'], $row['color'], $map, $row['css_name'], $row['is_def']);
}
#endregion

#region Отримання масиву послуг
$_service_name = array();
$_service_type = array();

$result = $conn('SELECT * FROM `service_ids` ORDER BY `order` ASC');

foreach ($result as $row) {
    $_service_name[$row['ID']] = $row['NAME'];
    $_service_type[$row['ID']][1] = '';
}
#endregion

#region Отримання типів
$result = $conn('SELECT * FROM `type_ids`');

foreach ($result as $row) {
    $_service_type[$row['service_ID']][$row['type_ID']] = $row['name'];
}
#endregion

$conn->close();

$in = '';
$ind = array();
$typ = array();
$emp = array();
$tmp = array();
$cnt = array();
$counter = 0;
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
            $ind[$counter] = substr($k, 5);
            $typ[$counter] = $_GET['type_' . $ind[$counter]];

            $tmp[$counter] = $v;
            $emp[$counter] = $_GET['color_' . $ind[$counter]] ?? null;
            $cnt[$counter] = !is_null($emp[$counter]) ? 1:'';
            $sum += $v;
            $counter++;
        }
    }
}else{
    if (isset($_GET['cost_21'])) {
        $ind[$counter] = 21;
        $tmp[$counter] = $_GET['cost_21'];
        $sum += $_GET['cost_21'];
        $counter++;
    }

    for ($i=1; $i < 50; $i++){
        $count = $_GET['count_' . $i] ?? 0;

        if ($count > 0){
            $ind[$counter] = $_GET['s_' . $i] ?? null;
            $typ[$counter] = $_GET['type_' . $i] ?? 1;

            $tmp[$counter] = $_GET['price_'.$i];
            $emp[$counter] = $_GET['color_' . $i] ?? null;
            $cnt[$counter] = !is_null($emp[$counter]) ? $count : '';
            $sum += $tmp[$counter];
            $counter++;
        }
    }

    foreach ($_GET as $k => $v) {
        $in .= '&' . $k . '=' . $v;
    }
}

$out = new HTEL('tbody');

foreach($ind as $c=>$v){
    $col = isset($emp[$c]) ? (' (' . $_COLORS[$emp[$c]] . ')') : '';
    $type = $typ[$c];

    $out(new HTEL('tr', [
        $_service_name[$v],
        $_service_type[$v][$type],
        $col,
        $cnt[$c],
        CostOut($tmp[$c]),
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
        url: 'blok/z_create/record_new_z.php',
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