<?php
require("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

if (!isset($_GET['search']) || $_GET['search'] == ''){
    switch ($_GET['type']) {
        case 'new':
            $query = 'SELECT * FROM `client_info` where `date_out` IS NULL AND `TTN_IN` IS NULL AND `sholom_num` = 0 ORDER BY `date_max` ASC';
            break;
        case 'inwork':
            $query = 'SELECT * FROM `client_info` where `date_out` IS NULL AND (`TTN_IN` IS NOT NULL OR `sold_number` IS NOT NULL) ORDER BY `date_max` ASC';
            break;
        default:
            $query = 'SELECT * FROM `client_info` where `date_out` IS NOT NULL ORDER BY `date_out` DESC';
            break;
    }

}else{
    $srch = '"%' . $_GET['search'] . '%" ';

    echo new HTEL('label &=margin-bottom:10px;padding:5px+10px;width:100%;font-size:120%;text-weight:bold;/РЕЗУЛЬТАТИ ПОШУКУ: "[0]"',
    $_GET['search']);

    $query = 'SELECT * FROM `client_info` where
    `phone` LIKE ' . $srch . '
    OR `client_name` LIKE ' . $srch . '
    OR `TTN_IN` LIKE ' . $srch . '
    OR `TTN_OUT` LIKE ' . $srch . '
    OR `sold_number` LIKE ' . $srch . '
    OR `sholom_num` LIKE ' . $srch . '
    OR `comm` LIKE ' . $srch . '
    OR `reqv` LIKE ' . $srch;
}

$result = mysqli_query($link, $query);

session_start();

//var_dump($_SESSION);

foreach ($result as $row) {

    $variant = 'def';
    $num = $row['sholom_num'];
    $ID = $row['ID'];

    if (!is_null($row['sold_number'])){
        $num = $row['sold_number'];
        $variant = 'sold';
    }

    if ($num == 0)
        $num = '';

    if (isset($_GET['search']) && $_GET['search'] != ''){
        if (is_null($row['date_out']) && (!is_null($row['TTN_IN']) || $row['sold_number'] !== null)){
            $_GET['type'] = 'inwork';
        }else if(is_null($row['date_out']) && is_null($row['TTN_IN'])){
            $_GET['type'] = 'new';
        }
        else{
            $_GET['type'] = 'archiv';
        }
    }

    $style = 'border-left: 25px solid ';

    switch($_GET['type']){
        case  'new':
            $style .= 'red;';
            break;
        case 'inwork':
            $style .= 'yellow;';
            break;
        default:
            $style .= 'green;';
            break;
    }

    $pip = explode(' ', $row['client_name']);

    $pip_out = $pip[0];

    for ($i = 1; $i < count($pip); $i++){
        $pip_out .= ' ' . mb_substr($pip[$i], 0, 1) . '.';
    }

    $div = new HTEL(
        'div !=[7] .=activeZ &=[8]',
        [
            $num,
            $pip_out,
            mb_substr(str_replace(' ' , '', $row['phone']), -10),
            dateToNorm($row['date_max'], true),
            dateToNorm($row['date_out'], true),
            $_GET['type'],
            $variant,
            $ID,
            $style,
            new HTEL('p/[0]', $row['redaktor'])
        ]
    );

    $vid = $variant == 'def' ? "#" : "$";

    $div([
        new HTEL('label/[1] [0]', [1=>$vid]),
        new HTEL('label/[1]'),
        new HTEL('label/[2]')
    ]);

    if ($_GET['type'] != 'archiv') {
        $div(new HTEL('label/[3]'));
    } else {
        $div(new HTEL('label/[4]'));
    }

    if ($_SESSION[$_SESSION['logged']] <= $_SESSION[$row['redaktor']]){
        $div([
            new HTEL('button *=button .=but_prt onclick=printInfo([7],0,`[5]`,`[6]`)'),
            new HTEL('button *=button .=but_cng onclick=changeInfo([7],`[6]`)'),
            new HTEL('button *=button .=but_del onclick=removeInfo([7],[0])')
        ]);
    }else{
        $div([
            new HTEL('button *=button .=but_prt+dis '),
            new HTEL('button *=button .=but_cng+dis '),
            new HTEL('button *=button .=but_del+dis ')
        ]);
    }

    if ($row['discount'] !== null){
        $div(new HTEL('label .=percent/-[0]%', $row['discount']));
    }

    //$sum = 0;

    if ($row['date_out'] === null){
        $query = 'SELECT * FROM `service_out` where `ID` = ' . $ID . ' AND `service_ID` = 21 LIMIT 1';

        if (mysqli_num_rows(mysqli_query($link, $query)) == 1) {
            $div(new HTEL('label .=term/T'));
            //$sum = $row['costs'];
        }
    }

    //$query = 'SELECT costs, NAME, service_ID FROM service_out JOIN service_ids ON service_out.service_ID=service_ids.ID  where service_out.ID = ' . $ID . ' AND service_ID <> 21';

    //$res = mysqli_query($link, $query);

    //$tr = new HTEL('tr');

    //$arr_kompl_no_duble = array();

    //foreach ($res as $row){
    //    $sum += $row['costs'];
    //    $arr_kompl_no_duble[$row['service_ID']] = $row['NAME'];
    //}

    //foreach ($arr_kompl_no_duble as $in_kompl){
    //    $kompl_words = explode(' ', $in_kompl);
    //    $out_frase = '';

    //    foreach ($kompl_words as $word){
    //        $out_frase .= mb_substr($word, 0, 5) . ' ';
    //    }

    //    $tr(new HTEL('td nowrap/[0]', trim($out_frase)));
    //}

    //$div(new HTEL('label .=costs/[0]', CostOut($sum)));

    //$div(new HTEL('table .=kompl', new HTEL('tbody', $tr)));

    echo $div;
}

$link->close();

?>

<script>
    function printInfo($in, $hide = 0, $type = 'activ', $var = 'def') {
            $.ajax({
                url: 'blok/print_to_work.php',
                method: 'GET',
                dataType: 'html',
                data: 'ID=' + $in + '&hideForWorker=' + $hide + '&type=' + $type + '&variant=' + $var,
                success: function(data) {
                      $('#workfield').html(data);
                }
                });
    };

     //Редагувати заявку

     function changeInfo($in, $var = 'def') {

     $.ajax({
         url: 'blok/new_Z.php',
         method: 'GET',
         dataType: 'html',
         data: 'ID=' + $in+ '&type=' + $var,
         success: function (data) {
             $('#workfield').html(data);
         }
     });
     }

    function removeInfo($in, $num) {
         if ($num == null) $num = '';

         if (confirm('Підтвердіть видалення даних по шолому '  + $num)) {
             $.ajax({
                 url: 'blok/remowe_info.php',
                 method: 'GET',
                 dataType: 'html',
                 data: 'ID=' + $in,
                 success: function (data) {
                     $('#workfield').html(data);
                 }
             });
         };
     };
</script>

