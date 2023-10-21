<?php
require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

session_start();

$lastId = $_GET['lastind'];

$arr_in = array();
$temp_arr = array();

$err = '';

for($i = 0; $i <= $lastId; $i++){
    if (isset($_GET['s_' . $i])){

        $temp_arr['id'] = $_GET['s_' . $i];
        $temp_arr['color'] = isset($_GET['color_' . $i]) ? $_GET['color_' . $i] : null;
        $temp_arr['count'] = isset($_GET['count_' . $i]) ? $_GET['count_' . $i] : 1;
        $temp_arr['price'] = isset($_GET['price_' . $i]) ? $_GET['price_' . $i] : 10;
        $temp_arr['comm'] = $_GET['comm_' . $i] != '' ? $_GET['comm_' . $i] : null;
        $temp_arr['type'] = isset($_GET['type_' . $i]) ? $_GET['type_' . $i] : 1;

        $arr_in[] = $temp_arr;
    }
}

//Запис списання/надходження компл

for ($i = 0; $i < count($arr_in); $i++){

    $query = "INSERT INTO `service_in` (date_in, service_ID, type_ID, color, count, costs, comm, redaktor)
      VALUES (" . outVal($_GET['date_in']) . outVal($arr_in[$i]['id']) . outVal($arr_in[$i]['type']) . outVal($arr_in[$i]['color']) .
      outVal($arr_in[$i]['count']) .  outVal($arr_in[$i]['price']) .  outVal($arr_in[$i]['comm']) .
      outVal($_SESSION['logged'], true) . ")";

    if ($link->query($query) !== TRUE) {
        $err = "Помилка запису в базу даних: " . $query . "<br>" . $link->error;
    }
}

$link->close();

if ($err == ''){
    phpAlert("Записи успішно створено.", 'work?page=jurnal');
}
else{
    phpAlert($err);
}

function outVal($val, $last = false):string{
    $out = $val;

    if (is_null($val)){
        $out = 'NULL';
    }
    else If (!is_numeric($val)){
        $out = "'" . $out . "'";
    }

    if (!$last) {
        $out .= ", ";
    }

    return $out;
}

?>