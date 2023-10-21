<?php
require("conn_local.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//ВИБІРКА НАЙМЕНУВАНЬ/ забивка одиночних типів

$arr_serv_name = array();
$arr_types = array();

$query = 'SELECT * FROM `service_ids` where `atr` <> 16 ORDER BY `order` ASC';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_serv_name[$row["ID"]] = $row["NAME"];
    $arr_types[$row["ID"]][1] = '';
}

//ВИБІРКА ІСНУЮЧИХ ТИПІВ

$query = 'SELECT * FROM `type_ids`';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_types[$row["service_ID"]][$row["type_ID"]] = "(" . $row["name"] . ")";
}

//ВИБІРКА ЦІН

$arr_cst = array();

$query = 'SELECT * FROM `price_list`';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $arr_cst[$row["service_id"]][$row["type_id"]] = $row["cost"];
}

$link->close();

?>

<div class="tbl_price">
    <table id="tbl_price">
        <caption>Ціни на розходні матеріали</caption>
        <tbody>
            <tr>
                <th>НАЗВА</th>
                <th>ЦІНА (грн)</th>
            </tr>

            <?php

            $eho = '';

            foreach ($arr_serv_name as $id=>$name){
                foreach ($arr_types[$id] as $t=>$nt) {

                    $cst = isset($arr_cst[$id][$t]) ? CostOut($arr_cst[$id][$t]) : "0.00";

                    $el0 = new HTEL('tr', [ $id, $t, $cst ]);

                    $el0->LEVEL = 1;

                    $el = array();

                    $el[0] = new HTEL('td .=price_name_cell/[0] [1]', [$name, $nt]);

                    $el[1] = new HTEL('td');
                    $el[1](new HTEL('input *=number step=0.01 min=0 name=[0]_[1] #=[2] [r]'));

                    $el0($el);

                    echo $el0;

                }
            }

            //var_dump($arr_cst);

            ?>

        </tbody>
    </table>
    <button id="butSubm" type="button" name="butSubm" onclick="Record()">ЗБЕРЕГТИ</button>
</div>

<script>

    function Record() {
          let dataBase = $('#tbl_price :input').serialize()

           $.ajax({
               url: 'blok/record_price.php',
               method: 'GET',
               dataType: 'html',
               data: dataBase,
               success: function (data) {
               $('#workfield').html(data);
           }
       });
    }

   //$("#fPrice").on("submit", function () {
         
   //});

</script>