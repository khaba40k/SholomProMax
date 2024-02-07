<script>

    function goToPrint(id, type) {
        $.ajax({
        url: 'blok/z_list/print_to_work.php',
        method: 'GET',
        dataType: 'html',
        data: 'ID=' + id + '&hideForWorker=0&type=archiv&variant=' + type,
        success: function (data) {
            $('#workfield').html(data);
        }
    });
    }

</script>


<?php

//["sum_fact"]=> string(7) "8082.50" ["kompl_0"]=> string(2) "19" ["cost_0"]=> string(3) "150" ["kompl_1"]=> string(2) "19" ["cost_1"]=> string(3) "250" ["kompl_2"]=> string(1) "8" ["cost_2"]=> string(5) "337.5" ["kompl_3"]=> string(2) "19" ["cost_3"]=> string(3) "350" ["kompl_4"]=> string(2) "15" ["cost_4"]=> string(6) "1247.5" ["kompl_5"]=> string(1) "7" ["cost_5"]=> string(6) "5747.5"

//var_dump($_GET);
//exit;

require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$err = '';

$query = 'UPDATE `client_info` SET `date_out`= "' . date('Y-m-d') .
     '", `TTN_OUT`= "' . $_GET['ttn_done'] .
     '", `worker`="' . $_GET['worker'] .
     '" WHERE `ID`=' . $_GET['ID'];

if ($link->query($query) !== TRUE) {
    $err = "Помилка запису в базу даних: " . $query . "\n" . $link->error . "\n";
}

$kompl = array();

$query = 'SELECT * FROM `service_out` where `ID` = ' . $_GET['ID']  . ' ORDER  BY `costs` DESC' ;

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $kompl[$row['service_ID']][$row['type_ID']] = $row['costs'];
}

$sum_mastbe = sumArray($kompl);

$korrect = $kompl;

if (isset($kompl[21][1])){
    unset($korrect[21]);

    $korrect = korektArr($korrect, $_GET['sum_fact'] - $kompl[21][1]);

    $korrect[21][1] = $_GET['sum_fact'] >= ($kompl[21][1] + sumArray($korrect)) ? $kompl[21][1] : ($_GET['sum_fact'] - sumArray($korrect));
}
else{
    $korrect = korektArr($korrect, $_GET['sum_fact']);
}

foreach ($korrect as $id => $kk) {
    foreach ($kk as $t => $cost) {
        $query = 'UPDATE `service_out` SET `costs`=' . round($cost, 2) .
            ' WHERE `ID`=' . $_GET['ID'] .
            ' AND `service_ID`=' . $id .
            ' AND `type_ID` =' . $t;

        if ($link->query($query) !== TRUE) {
            $err .= "Помилка запису в базу даних: " . $query . "\n" . $link->error;
            break;
        }
    }
}

$link->close();

if ($err == ''){
    phpAlert("Замовлення успішно виконано.");
    echo new HTEL('script/goToPrint(`[0]`, `[1]`);', [$_GET['ID'], $_GET['type']]);
}else{
    phpAlert($err);
}

function korektArr($arr, $korrectSum): array
{
    $out = $arr;

    if ($korrectSum <= 0){
        foreach ($out as $id => $type) {
            foreach ($type as $t => $cost) {
                 $out[$id][$t] = 0;
            }
        }

        return $out;
    }


    $step = 0.1;
    $sum = sumArray($out);
    $korKeys = array('sum' => 0);

    if ($sum > $korrectSum) {
        while ($sum > $korrectSum) {
            foreach ($out as $id => $type) {
                foreach ($type as $t => $cost) {
                    if ($cost > 0) {
                        $out[$id][$t] = round($cost - $step, 2);

                        if ($out[$id][$t] > $korKeys['sum']) {
                            $korKeys['sum'] = $out[$id][$t];
                            $korKeys[0] = $id;
                            $korKeys[1] = $t;
                        }
                    }
                }
            }

            $sum = sumArray($out);
        }
    } else if ($sum < $korrectSum) {
        while ($sum < $korrectSum) {
            foreach ($out as $id => $type) {
                foreach ($type as $t => $cost) {
                    $out[$id][$t] = round($cost + $step, 2);

                    if ($out[$id][$t] > $korKeys['sum']) {
                        $korKeys['sum'] = $out[$id][$t];
                        $korKeys[0] = $id;
                        $korKeys[1] = $t;
                    }
                }
            }

            $sum = sumArray($out);
        }
    }

    if ($sum != $korrectSum) {
        $out[$korKeys[0]][$korKeys[1]] += $korrectSum - $sum;
    }

    return $out;
}
?>
