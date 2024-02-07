<?php
require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//отримання списку адміністраторів/редакторів

$query = 'SELECT login FROM users';

$result = mysqli_query($link, $query);

$users = array();

foreach($result as $row){
    $users[] = $row['login'];
}

$_GET['type'] = $_GET['type'] ?? 'archiv';

if (!isset($_GET['search']) || trim($_GET['search']) == ''){

    switch ($_GET['type']) {
        case 'new':
            $query = 'SELECT * FROM `client_info` where `date_out` IS NULL AND `TTN_IN` IS NULL AND `sholom_num` = 0 ORDER BY `date_max` DESC';
            break;
        case 'inwork':
            $query = 'SELECT * FROM `client_info` where `date_out` IS NULL AND (`TTN_IN` IS NOT NULL OR `sold_number` IS NOT NULL) ORDER BY `date_max` ASC';
            break;
        default:
            $get_per = $_GET['period'] ?? date('m.Y');

            $done_period = _getPeriod($get_per); //12.2023

            echo new HTEL('div .=period_done', [
                new HTEL("button !=per_plus onclick=location.href=='work?page==archiv&period==[0]'/<", _perNext($get_per)),
                new HTEL('input *=text !=period_txt #=[0] [ro]', _ukrPeriod($get_per)),
                new HTEL("button !=per_minus onclick=location.href=='work?page==archiv&period==[0]'/>", _perPrev($get_per))
            ]);

            $query = 'SELECT * FROM client_info where
            date_out >= '. $done_period[0] . ' AND date_out < '. $done_period[1] . '
            ORDER BY `date_out` DESC';
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

function _getPeriod($in = null): array //in = ~ 10.2023 or null
{
    $out = array();

    if (!is_null($in)) {
        $spl = explode('.', $in);

        $month = $spl[0];
        $year = $spl[1];

        $out[0] = '"' . $year . '-' . $month . '-1"';

        if ($month < 12) {
            $month++;
        } else {
            $month = 1;
            $year++;
        }

        $out[1] = '"' . $year . '-' . $month . '-1"';
    } else {
        $now = date("Y-m-1");

        $out[0] = '"' . $now . '"';

        $month = date('m');

        $year = date('Y');

        if ($month < 12) {
            $month++;
        } else {
            $month = 1;
            $year++;
        }

        $out[1] = '"' . $year . '-' . $month . '-1"';
    }

    return $out;
}

function _perNext($in):string{
    $spl = explode('.', $in);

    $month = $spl[0];
    $year = $spl[1];

    if ($month < 12) {
        $month++;
    } else {
        $month = 1;
        $year++;
    }

    return $month . '.' . $year;
}

function _perPrev($in): string
{
    $spl = explode('.', $in);

    $month = $spl[0];
    $year = $spl[1];

    if ($month > 1) {
        $month--;
    } else {
        $month = 12;
        $year--;
    }

    return $month . '.' . $year;
}

function _ukrPeriod($in):string{
    $mounts = [
        1 => 'січень',
        'лютий',
        'березень',
        'квітень',
        'травень',
        'червень',
        'липень',
        'серпень',
        'вересень',
        'жовтень',
        'листопад',
        'грудень'
    ];

    $spl = explode('.', $in);

    $month = $mounts[$spl[0]];
    $year = $spl[1];

    return $month . ' ' . $year;
}

$result = mysqli_query($link, $query);

session_start();

$counter = mysqli_num_rows($result);

if ($_GET['type'] == 'archiv' && $counter > 0){
    echo 'ЗАПИСІВ: [ ' . $counter . ' ]<br>';
}

if ($counter > 0){
    foreach ($result as $row) {
        $counter++;

        $variant = 'def';
        $num = $row['sholom_num'];
        $ID = $row['ID'];

        if (!is_null($row['sold_number'])) {
            $num = $row['sold_number'];
            $variant = 'sold';
        }

        if ($num == 0)
            $num = '';

        if (isset($_GET['search']) && $_GET['search'] != '') {
            if (is_null($row['date_out']) && (!is_null($row['TTN_IN']) || $row['sold_number'] !== null)) {
                $_GET['type'] = 'inwork';
            } else if (is_null($row['date_out']) && is_null($row['TTN_IN'])) {
                $_GET['type'] = 'new';
            } else {
                $_GET['type'] = 'archiv';
            }
        }

        $style = 'border-left: 25px solid ';

        switch ($_GET['type']) {
            case 'new':
                $style .= 'red;';
                break;
            case 'inwork':
                $style .= 'yellow;';
                break;
            default:
                $style .= 'green;';
                break;
        }

        $style .= 'background:' . ($variant == 'def' ?
        "linear-gradient(to left, yellow, rgba(255, 255, 255, 0.50));" :
        "linear-gradient(to left, lightgray, rgba(255, 255, 255, 0.50));");

        $pip = explode(' ', trim($row['client_name']));

        $pip_out = $pip[0];

        for ($i = 1; $i < count($pip); $i++) {
            $pip_out .= ' ' . mb_substr($pip[$i], 0, 1) . '.';
        }

        $div = new HTEL(
            'div !=[7] .=activeZ+[9] &=[8]',
            [
                $num,
                $pip_out,
                getCorrectPhone($row['phone']),
                dateToNorm($row['date_max'], true),
                dateToNorm($row['date_out'], true),
                $_GET['type'],
                $variant,
                $ID,
                $style,
                classFromCreator($row['redaktor']),
                new HTEL('p/[0]', $row['redaktor'])
            ]
        );

        $div([
            new HTEL('label/[0]'),
            new HTEL('label/[1]')
        ]);

        if ($row['callback'] == 1 && $row['date_out'] === null) {
            $div(
                new HTEL(
                    'label &=color:yellow;border:2px+solid+blue;border-radius:3px;text-decoration:unset;background-color:red;/[2]'
                )
            );
        } else {
            $div(
                new HTEL('label/[2]')
            );
        }

        if ($_GET['type'] != 'archiv') {
            $div(new HTEL('label/[3]'));
        } else {
            $div(new HTEL('label/[4]'));
        }

        $div_buttons = new HTEL('div .=buttons');

        if ((!isset($_SESSION[$row['redaktor']]) && $_SESSION[$_SESSION['logged']] <= 2) || $_SESSION[$_SESSION['logged']] <= $_SESSION[$row['redaktor']]) {
            $div_buttons([
                new HTEL('button *=button .=but_prt onclick=printInfo([7],0,`[5]`,`[6]`)'),
                new HTEL('button *=button .=but_cng onclick=changeInfo([7],`[6]`)'),
                new HTEL('button *=button .=but_del onclick=removeInfo([7],[0])')
            ]);
        } else {
            $div_buttons([
                new HTEL('button *=button .=but_prt+dis '),
                new HTEL('button *=button .=but_cng+dis '),
                new HTEL('button *=button .=but_del+dis ')
            ]);
        }

        $div($div_buttons);

        if ($row['discount'] !== null) {
            $div(new HTEL('label .=percent/-[0]%', $row['discount']));
        }

        if ($row['date_out'] === null) {
            $query = 'SELECT * FROM `service_out` where `ID` = ' . $ID . ' AND `service_ID` = 21 LIMIT 1';

            if (mysqli_num_rows(mysqli_query($link, $query)) == 1) {
                $div(new HTEL('label .=term/T'));
            }
        }

        echo $div;
    }
}
else {
    echo 'ЗАПИСІВ НЕ ЗНАЙДЕНО !';
}

$link->close();


function classFromCreator($creator):string{
    $us = $GLOBALS['users'];

    if (!in_array($creator, $us)){
        return 'customcreator';
    }

    return '';
}
function getCorrectPhone(string $in, $kodKr = false):string{
    $out = '';

    $split = str_split($in);

    foreach($split as $s){
        if (is_numeric($s)){
            $out .= $s;
        }
    }

    if (strlen($out) < 10)
        return '?';

    $out = mb_substr($out, -10);

    if ($kodKr)
        $out = '+38' . $out;

    return $out;
}

?>

<script>
    function printInfo($in, $hide = 0, $type = 'activ', $var = 'def') {
            $.ajax({
                url: 'blok/z_list/print_to_work.php',
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
         url: 'blok/z_create/new_Z.php',
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
                 url: 'blok/z_list/remowe_info.php',
                 method: 'GET',
                 dataType: 'html',
                 data: 'ID=' + $in,
                 success: function (data) {
                     $('#workfield').html(data);
                 }
             });
         };
    };

    $('.activeZ').hover(function () {
        var $this = $(this).find('.buttons');
        $('.buttons').not($this).removeClass('show_podmenu');
        $this.toggleClass("show_podmenu");
    });

</script>

