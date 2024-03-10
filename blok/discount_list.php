<?php

session_start();

if ($_SESSION[$_SESSION['logged']] > 1)
    exit;

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$conn = new SQLconn();

if (isset($_GET['disc_cnt'])) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $length = 5;
    $charactersLength = strlen($characters) - 1;

    $count = $_GET['disc_cnt'];
    $perc = $_GET['disc_prc'];
    $out = array();

    for ($ii = 0; $ii < $count; $ii++){
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength)];
        }

        $out[] = $randomString;
    }

    foreach($out as $code){
        $conn('INSERT INTO `discount_list` SET `code`= "'.$code.'", `percent`='.$perc);
    }

    $conn->close();
    header('location: ../work?page=discount_list');
    exit;
}

#region Отримання існуючих дисконтів
$discounts = array();

$result = $conn('SELECT * FROM `discount_list` WHERE `from_ID` IS NULL ORDER BY `date_gen` DESC, `percent` DESC');

foreach ($result as $row) {
    $discounts[$row['date_gen']][$row['percent']][] = $row['code'];
}
#endregion

$conn->close();

$coll_count = 10;

$tbody = new HTEL('tbody');
foreach ($discounts as $date => $row) {
    $tbody(new HTEL('tr', new HTEL('th colspan=[0]/[1]', [$coll_count, 'Дата генерації: '.dateToNorm($date) ])));
    foreach ($row as $perc=>$row1) {
        $tbody(new HTEL('tr', new HTEL('td &=font-weight:bold;font-size:100%;text-align:left;padding:5px;background-color:yellow; colspan=[0]/[1]', [$coll_count, count($row1) . ' => ' . $perc . '%'])));
        $counter = -1;
        $tr1 = new HTEL('tr');
        foreach ($row1 as $code) {
            $counter++;

            if ($counter >= $coll_count) {
                $counter = 0;
                $tbody($tr1);
                $tr1 = new HTEL('tr');
            }

            $tr1(new HTEL('td/[0]', $code));
        }
        $tbody($tr1);
    }
}

$table = new HTEL('table !=tbl_disc', [
    new HTEL('caption/Перелік невикористаних дисконтних кодів'),
    $tbody
]);

$generator = new HTEL('form !=generator .=no-print onSubmit=return+generate()');

$generator(new HTEL('input *=number ?=disc_cnt min=1 max=50 $=кількість [r]/x'));
$generator(new HTEL('input *=number ?=disc_prc min=1 max=99 $=відсоток [r]/%'));
$generator(new HTEL('button *=submit /ЗГЕНЕРУВАТИ'));

echo $table;
echo $generator;

?>

<script>

    function generate() {

        if (!confirm('Підтвердіть генерування кодів...')) return false;

         $.ajax({
             url: 'blok/discount_list.php',
             method: 'GET',
             dataType: 'html',
             data: $('#generator').serialize(),
             success: function (data) {
                 $('#workfield').html(data);
             }
         });

        return false;
    }

</script>