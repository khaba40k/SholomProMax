<?php

//var_dump($_GET);
//exit;

//["convers_0"]=> string(1) "1" ["cost_0"]=> string(6) "1500.9" ["color_0"]=> string(1) "-"

$termin_ID = 21;

$typeZ = $_GET['typeZ'] ?? 'def';//def / sold

require_once $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";

if (!isset($_GET['sol_num'])) {
    $_GET['sol_num'] = 0;
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

#region Видалення старих даних
if ($_GET['is_rewrite'] == 1) {

    $query = 'DELETE FROM `service_out` WHERE ID=' . $_GET['ID'];
    mysqli_query($link, $query);

}
#endregion

session_start();

$creator = $_SESSION['logged'] ?? $_GET['phone_out'];

if (isset($_GET['creator'])){
    $creator = $_GET['creator'];
}

//Отримання масиву послуг

$_service_id = array();

$attr = 2;//

$query = 'SELECT * FROM `service_ids` ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        if (inclAttr($attr, $row['atr'])) {
            $_service_id[] = $row['ID'];
        }
    }
}

$_service_id[] = $termin_ID;

//---------------------------------

$err = '';

if (!isset($_GET['date_in']))
    $_GET['date_in'] = date('Y-m-d');

if (!isset($_GET['date_max']))
    $_GET['date_max'] = strftime("%Y-%m-%d", strtotime($_GET['date_in'] . " +3 day"));

if (!isset($_GET['date_out']))
    $_GET['date_out'] = null;

if (!isset($_GET['ttn_out']))
    $_GET['ttn_out'] = null;

$num_cell = ($typeZ=='def' || $typeZ == 'def0') ? 'sholom_num': 'sold_number';

$ifNull = ['ttn_in', 'ttn_out', 'date_out', 'comm', 'worker', 'mess'];

foreach($ifNull as $var){
    if (!isset($_GET[$var]) || empty(trim($_GET[$var])))
        $_GET[$var] = '';
}

#region Отримання наступного номера заявки/скидання на 0

if((!empty($_GET['ttn_in']) || ($typeZ != 'def' && $typeZ != 'def0')) && ($_GET['sol_num'] === 0)){
    $query = 'SELECT `' . $num_cell . '` FROM `client_info` order by `' . $num_cell . '` DESC LIMIT 1';

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) == 1) {
        foreach ($result as $row) {
            $_GET['sol_num'] = $row[$num_cell] + 1;
        }
    }

}else if (empty($_GET['ttn_in']) && ($typeZ == 'def' || $typeZ == 'def0')){
    $_GET['sol_num'] = 0;
}

//видалення дати виходу за відсутності ттн вихідної

if (empty($_GET['ttn_out']) && !empty($_GET['date_out'])) {
    $_GET['date_out'] = null;
}

#endregion

$query = "INSERT INTO `client_info` (ID, " . $num_cell . ", date_in, date_max, date_out, phone, client_name, reqv, TTN_IN, TTN_OUT, comm, worker, redaktor)
VALUES ("
    . outVal($_GET['ID'])
    . outVal($_GET['sol_num'])
    . outVal($_GET['date_in'])
    . outVal($_GET['date_max'])
    . outVal($_GET['date_out'])
    . outVal($_GET['phone_out'])
    . outVal($_GET['pip'])
    . outVal($_GET['rek_out'])
    . outVal($_GET['ttn_in'])
    . outVal($_GET['ttn_out'])
    . outVal($_GET['mess']. ' ' . trim($_GET['comm']))
    . outVal($_GET['worker'])
    . outVal($creator, true) . ")
    ON DUPLICATE KEY UPDATE "
    . $num_cell . "=" . outVal($_GET['sol_num']) .
    "date_in=" . outVal($_GET['date_in']) .
    "date_max=" . outVal($_GET['date_max']) .
    "date_out=" . outVal($_GET['date_out']) .
    "phone=" . outVal($_GET['phone_out']) .
    "client_name=" . outVal($_GET['pip']) .
    "reqv=" . outVal($_GET['rek_out']) .
    "TTN_IN=" . outVal($_GET['ttn_in']) .
    "TTN_OUT=" . outVal($_GET['ttn_out']) .
    "comm=" . outVal($_GET['mess'] . ' ' . trim($_GET['comm'])) .
    "worker=" . outVal($_GET['worker']) .
    "redaktor=" . outVal($creator, true);

if ($link->query($query) !== TRUE) {
    $err = "Помилка запису в базу даних:\n" . $query . "\n" . $link->error;
}

if (!isset($_GET['cost_' . $termin_ID]))
    $_GET['cost_' . $termin_ID] = 0;

if ($err == ''){
     if ($typeZ == 'def' || $typeZ == 'def0'){
          foreach ($_service_id as $i) {
               $_color_id = GetColor($i);

               $price = CostOut($_GET['cost_' . $i]);

               $count = 1;

               $type = 1;

               $serv_id = $i;

               if (isset($_GET['convers_' . $i])) {
                   $serv_id = 19;
                   $type = $i;
                   $_color_id = '';
               } else if (isset($_GET['type_' . $i])) {
                   $type = $_GET['type_' . $i];
               }else if ($i == $termin_ID && $price != 0){
                   $_color_id = '';
               }

              if ($_color_id != '-') { //Вибір на ІД зроблено

                  $query = "INSERT INTO `service_out` (ID, service_ID, type_ID, color, count, costs)
                   VALUES (" .
                      outVal($_GET['ID']) .
                      outVal($serv_id) .
                      outVal($type) .
                      outVal($_color_id) .
                      outVal($count) .
                      outVal($price, true) .
                      ")";

                  if ($link->query($query) !== TRUE) {
                      $err = "Помилка запису в базу даних:\n" . $query . "\n" . $link->error;
                      break;
                  }
              }
          }
     }
     else{
        for ($ii = 0; $ii < 30; $ii++) {
            if (isset($_GET['s_' . $ii])) {
                $serv_id = $_GET['s_' . $ii];
                $type = isset($_GET['type_' . $ii]) ? $_GET['type_' . $ii] : 1;
                $color = isset($_GET['color_' . $ii]) ? $_GET['color_' . $ii] : null;
                $count = isset($_GET['count_' . $ii]) ? $_GET['count_' . $ii] : 1;
                $price = $_GET['price_' . $ii];

                $query = "INSERT INTO `service_out` (ID, service_ID, type_ID, color, count, costs)
                   VALUES (" .
                        outVal($_GET['ID']) .
                        outVal($serv_id) .
                        outVal($type) .
                        outVal($color) .
                        outVal($count) .
                        outVal(CostOut($price), true) .
                     ")";

                if ($link->query($query) !== TRUE) {
                    $err = "Помилка запису в базу даних:\n" . $query . "\n" . $link->error;
                    break;
                }
            }
        }

        if ($_GET['cost_' . $termin_ID] > 0){
            $query = "INSERT INTO `service_out` (ID, service_ID, type_ID, color, count, costs)
                   VALUES (" .
            outVal($_GET['ID']) .
            outVal($termin_ID) .
            outVal(1) .
            outVal('') .
            outVal(1) .
            outVal(CostOut($_GET['cost_' . $termin_ID])) .
                 ")";

            if ($link->query($query) !== TRUE) {
                $err = "Помилка запису в базу даних:\n" . $query . "\n" . $link->error;
            }
        }
    }
}

$link->close();

if ($err == ''){
    phpAlert("Запис успішно створено.", 'work');
}
else{
    phpAlert($err);
}

function GetColor($serv_id):string{

//checkbox/radio
   if (isset($_GET['cb'. $serv_id])) {
        return $_GET['cb' . $serv_id];
    }
//select
    if (isset($_GET['color_' . $serv_id]) && $_GET['color_' . $serv_id] != "") {
        return $_GET['color_' . $serv_id];
    }

    return '-';
}

function outVal($val, $last = false): string
{
    $out = $val;

    if (!is_numeric($val)){
        if (empty($val)) {
            $out = 'NULL';
        } else {
            $out = "'" . $out . "'";
        }
    }
    else  if (is_numeric($val) && substr($val, 0, 1) == '0'){
        $out = "'" . $out . "'";
    }

    if (!$last) {
        $out .= ", ";
    }

    return $out;
}
?>