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

    echo new HTEL('label &=margin-bottom:10px;padding:5px+10px;width:100%;font-size:24px;text-weight:bold;/РЕЗУЛЬТАТИ ПОШУКУ: "[0]"',
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

    $div = new HTEL(
        'div .=activeZ',
        [
            $num,
            $row['client_name'],
            $row['phone'],
            dateToNorm($row['date_max'], true),
            dateToNorm($row['date_out'], true),
            $_GET['type'],
            $variant,
            $ID
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

    $div([
        new HTEL('button *=button .=but_prt onclick=printInfo([7],0,`[5]`,`[6]`)'),
        new HTEL('button *=button .=but_cng onclick=changeInfo([7],`[6]`)'),
        new HTEL('button *=button .=but_del onclick=removeInfo([7])')
    ]);

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

     function removeInfo($in) {
         if (confirm('Підтвердіть видалення даних по шолому...')) {
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

