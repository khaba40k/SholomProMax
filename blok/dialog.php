<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$in = '';

$sum = 0;

$out = $_GET['dialog_body'] ?? new HTEL();

//$buttons = json_decode($_GET['dialog_buttons']) ?? array('OK' => true);

$buttons = $_GET['dialog_buttons'] ?? array('OK' => true);

$dialog_window=new HTEL('form !=dialog_form .=dialog_w');

$dialog_window(new HTEL('label !=top_dialog_lbl/[0]', $_GET['dialog_lable']));

$dialog_window(new HTEL('button !=but_close *=button #=click/X'));

$dialog_window(new HTEL('label/[0]', $out));

$butt_div = new HTEL('div .=bott_buts');

foreach ($buttons as $txt=>$val){
    $butt_div(new HTEL('button .=butt_dialog *=button #=click/[0]', [$txt, $val]));
}

$dialog_window($butt_div);

echo $dialog_window;
?>

<script>

    $('#but_close').on('click', function () {
        $(this).parent().parent().html("");
    });

    $('.butt_dialog').on('click', function () {


        $(this).parent().parent().html("");
    });

</script>