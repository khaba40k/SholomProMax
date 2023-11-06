<script>
        //Додавання таблиці продаж
    var Invalide_numbers;
    var NUMBER_VALID = true;

    function InvalidNumbers($in) {
        Invalide_numbers = $in;
    }

    function insertTable($in = '') {

              $.ajax({
                    url: 'blok/expenses.php',
                    method: 'GET',
                  dataType: 'html',
                  data: 'for=out' + $in,
                        success: function (data) {
                        $('#table_work').html(data);
                    }
              });
    }

    function shopList() {

        $.ajax({
            url: 'blok/shop.php',
            dataType: 'html',
            success: function (response) {
                $('#table_work').html(response);
            }
        });
    }
</script>

<?php

$TYPE_Z = $_GET['type'] ?? 'def';

require "conn_local.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

//HIDE();

#region Отримання списку кольорів
$_COLORS = array();

$query = 'SELECT * FROM `colors`';

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) != 0) {
    foreach ($result as $row) {
        $_COLORS[] = new MyColor($row['ID'], $row['color'], $row['serv_ids'], $row['css_name']);
    }
}
#endregion

$Z_DATA = new ZDATA();

//ВХІДНІ ДАНІ
$header_form = 'ДАНІ ЗАМОВЛЕННЯ';

$Z_DATA->DATE_IN = date("Y-m-d");
$Z_DATA->DATE_MAX = strftime("%Y-%m-%d", strtotime($Z_DATA->DATE_IN ." +3 day"));

$IS_CHANGE = isset($_GET['ID']) ? 1: 0;

$cell_num = ($TYPE_Z == 'def' || $TYPE_Z == 'def0') ? 'sholom_num' : 'sold_number';

if ($IS_CHANGE == 1){//РЕДАГУВАННЯ
    $header_form = 'РЕДАГУВАННЯ ЗАМОВЛЕННЯ';

    #region Вибірка з бази клієнта/комплектуючих

    $query = 'SELECT * FROM `client_info` WHERE `ID` = '. $_GET['ID']. ' LIMIT 1';

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) == 1) {
        foreach ($result as $row) {
            $Z_DATA->SET($row);
        }
    }

    $query = 'SELECT * FROM `service_out` WHERE `ID` = ' . $_GET['ID'];

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {
        $in_arr = array();

        foreach ($result as $row) {
            $in_arr['serv'][$row['service_ID']][$row['type_ID']] =
                [
                    'color' => $row['color'],
                    'count' => $row['count'],
                    'cost' => $row['costs']
                ];
        }

        if (count($in_arr) > 0) {
            $Z_DATA->SET($in_arr);
        }
    }

    #endregion

    #region Обробка переміщень
    //наступний номер за відсутності
    if (!empty($Z_DATA->TTN_IN) && empty($Z_DATA->SHOLOM_NUM) && empty($Z_DATA->SOLD_NUM)) {

        $query = 'SELECT `' . $cell_num . '` FROM `client_info` order by `' . $cell_num . '` DESC LIMIT 1';

        $result = mysqli_query($link, $query);

        if (mysqli_num_rows($result) == 1) {
            foreach ($result as $row) {
                $Z_DATA->SHOLOM_NUM = $row[$cell_num] + 1;
                $Z_DATA->SOLD_NUM = $row[$cell_num] + 1;
            }
        }
    }

    #endregion

}else{//НОВИЙ
    if ($TYPE_Z == 'def' || $TYPE_Z == 'def0') {
        $Z_DATA->SHOLOM_NUM = 0;
    } else {
        $Z_DATA->SOLD_NUM = 0;
    }

    #region Отримання наступного номера ID

        $query = 'SELECT `ID` FROM `client_info` order by `ID` DESC LIMIT 1';

        $result = mysqli_query($link, $query);

        if (mysqli_num_rows($result) == 1) {
            foreach ($result as $row) {
                $Z_DATA->ID = $row['ID'] + 1;
            }
        }

     #endregion
}

#region Масив існуючих номерів
if ($TYPE_Z == 'def' || $TYPE_Z == 'sold')
{
    $numbers_in_base = array();

    $query = 'SELECT `' . $cell_num . '` FROM `client_info` WHERE `' . $cell_num . '`<>' . ($TYPE_Z == 'def' ? $Z_DATA->SHOLOM_NUM : $Z_DATA->SOLD_NUM);

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0) {
        foreach ($result as $row) {
            if ($row[$cell_num] != null)
                $numbers_in_base[] = $row[$cell_num];
        }
    }

    echo new HTEL('script/InvalidNumbers([0]);', json_encode($numbers_in_base));
}
#endregion



$mes = array('Телеграм', 'Вотсап', 'Інстаграм', 'Вайбер', 'Телефон', 'Наручно', 'Сигнал', 'ТікТок');

$comm = $Z_DATA->COMM != null ? trim(str_replace($mes, '', $Z_DATA->COMM)):'';

$FORM = new HTEL(
    'form onsubmit=return+false !=form_create[12] method=post .=create_z',
    [
        $header_form,//0
        $Z_DATA->SHOLOM_NUM,
        $Z_DATA->SOLD_NUM,
        $Z_DATA->DATE_IN,//3
        $Z_DATA->DATE_MAX,
        $Z_DATA->DATE_OUT,//5
        $Z_DATA->PHONE_OUT,
        $Z_DATA->PIP,
        $Z_DATA->REQ_OUT,//8
        $Z_DATA->TTN_IN,
        $Z_DATA->TTN_OUT,
        trim($comm),//11
        ($TYPE_Z == 'def0' || $TYPE_Z == 'sold0') ? '_0':''
    ]
);

$field1 = new HTEL('fieldset !=fs1');

if($TYPE_Z == 'def' || $TYPE_Z == 'def0'){
    $div = array();

    if($IS_CHANGE == 1 && !empty($Z_DATA->TTN_IN)){
        $div[] = new HTEL('div', [
            new HTEL('label for=num/Номер шолома'),
            new HTEL('input !=num *=number min=1 ?=sol_num #=[1] [r]')
        ]);
    }

    if($TYPE_Z != 'def0'){
        $div[] = new HTEL('div', [
            new HTEL('label for=datin/Створено'),
            new HTEL('input !=datin *=date ?=date_in #=[3] [r]')
        ]);

        $div[] = new HTEL('div', [
            new HTEL('label for=datmax/Термін'),
            new HTEL('input  !=datmax *=date ?=date_max #=[4] [r]')
        ]);
    }

    $field1(new HTEL('legend/[0]:'));
    $field1($div);
}else{
    $div = array();

    if ($IS_CHANGE == 1) {
        $div[] = new HTEL('div', [
            new HTEL('label for=num/Номер замовл.'),
            new HTEL('input !=num *=number min=1 ?=sol_num #=[2] [r]')
        ]);
    }

    if ($TYPE_Z == 'sold'){
        $div[] = new HTEL('div', [
            new HTEL('label for=datin/Створено'),
            new HTEL('input !=datin *=date ?=date_in #=[3] [r]')
        ]);

        $div[] = new HTEL('div', [
            new HTEL('label for=datmax/Термін'),
            new HTEL('input !=datmax *=date ?=date_max #=[4] [r]')
        ]);
    }

    $field1(new HTEL('legend/[0]:'));
    $field1($div);
}

if (!is_null($Z_DATA->DATE_OUT)) {

    $field1(new HTEL('div', [
        new HTEL('label for=datout/Відправлено'),
        new HTEL('input !=datout *=date ?=date_out #=[5]')
    ]));
}

$div = array();

$div[] = new HTEL('div', [
    new HTEL('label for=telnum/Телефон'),
    new HTEL('input !=telnum *=tel ?=phone_out #=[6] [r]')
]);

$div[] = new HTEL('div', [
    new HTEL('label for=client/Ім`я'),
    new HTEL('input !=client ?=pip #=[7] [r]')
]);

$div[] = new HTEL('div', [
    new HTEL('label for=reqv/Реквізити НП'),
    new HTEL('input !=reqv ?=rek_out #=[8] [r]')
]);

$field1($div);

if ($TYPE_Z == 'def'){
    $field1(new HTEL('div', [
        new HTEL('label for=ttnin/ТТН (вхідна)'),
        new HTEL('input !=ttnin ?=ttn_in #=[9] $=якщо+відомо [0]', [(!empty($Z_DATA->DATE_OUT) ? 'readonly':'')])
    ]));
}

if ($IS_CHANGE == 1 && $Z_DATA->TTN_OUT != ''){
    $field1(new HTEL('div', [
        new HTEL('label for=ttnout/ТТН (вихідна)'),
        new HTEL('input !=ttnout ?=ttn_out #=[10]')
    ]));
}

if (!is_null($Z_DATA->DISCOUNT)){
    $field1(new HTEL('div', [
        new HTEL('label for=discount/Врахована знижка'),
        new HTEL('input ?=discount &=color:red; #=[0]% [ro]', $Z_DATA->DISCOUNT)
    ]));
}
else{
    $field1(new HTEL('div', [
        new HTEL('label for=discount/ДИСКОНТ'),
        new HTEL('input !=discount ?=discount minlength=5 maxlength=5 &=color:red; $=код+на+знижку #')
    ]));
}

if ($TYPE_Z == 'def' || $TYPE_Z == 'sold')
$field1(new HTEL('div', [
    new HTEL('label for=mess/Зв`язок'),
    new HTEL('select !=mess ?=mess', [
        new HTEL('option #/-'),
        new HTEL('option #=[0] [1]/[0]', [$mes[0], selectStatus(strpos($Z_DATA->COMM, $mes[0]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[1], selectStatus(strpos($Z_DATA->COMM, $mes[1]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[2], selectStatus(strpos($Z_DATA->COMM, $mes[2]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[3], selectStatus(strpos($Z_DATA->COMM, $mes[3]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[4], selectStatus(strpos($Z_DATA->COMM, $mes[4]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[5], selectStatus(strpos($Z_DATA->COMM, $mes[5]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[6], selectStatus(strpos($Z_DATA->COMM, $mes[6]) === 0)]),
        new HTEL('option #=[0] [1]/[0]', [$mes[7], selectStatus(strpos($Z_DATA->COMM, $mes[7]) === 0)])
    ])
]));

$field1(new HTEL('div &=display:flex;height:150px;', [
    new HTEL('label &=display:inline-flex;height:auto; for=com/Коментар'),
    new HTEL('textarea &=display:inline-flex;height:90%;resize:none; !=com ?=comm /[11]')
]));

//Заповнення графи терміново

$query = 'SELECT `cost` FROM `price_list` WHERE `service_id` = 21 LIMIT 1';

$result = mysqli_query($link, $query);

foreach ($result as $row) {
    $term_cost = $Z_DATA->GET_KOMPLECT(21) != '' ? $Z_DATA->GET_KOMPLECT(21) : $row['cost'];
}

$statusTerm = $Z_DATA->GET_KOMPLECT(21) != '' ? 'checked':'';

$field1(new HTEL('div', [
    new HTEL('label for=term/Терміново (+[0]грн.)', CostOut($term_cost)),
    new HTEL('input *=checkbox !=term ?=cost_21 #=[0] [1]', [$term_cost, $statusTerm])
]));

$field2 = new HTEL('fieldset !=fs2');

if ($TYPE_Z == 'def' || $TYPE_Z == 'def0'){
    $div = new HTEL('div !=color_variant');

    $label = new HTEL('label for=c_v/Колір на вибір...');
    $label(new HTEL('input *=radio ?=col !=c_v [c]'));

    $div($label);

    if ($IS_CHANGE == 0) {
        for ($i = 0; $i < count($_COLORS); $i++) {
            if ($_COLORS[$i]->Universal()) {
                $label = new HTEL(
                    'label for=c_v_[0] &=background:linear-gradient(to+top,white,[1]);/[2]',
                    [$_COLORS[$i]->ID, $_COLORS[$i]->CSS_ANALOG, $_COLORS[$i]->NAME]
                );
                $label(new HTEL('input *=radio ?=col !=c_v_[0]'));
                $div($label);
            }
        }
    }

    $field2([
        new HTEL('legend/КОМПЛЕКТУЮЧІ / ПОСЛУГИ:'),
        $div,
        new HTEL('div !=grid_color')
    ]);
}
else {
    $field2([
        new HTEL('legend/КОМПЛЕКТУЮЧІ / ПОСЛУГИ:'),
        new HTEL('div !=table_work')
    ]);

    if ($IS_CHANGE == 0) {
        if ($TYPE_Z != 'sold0') {
            $field2(new HTEL('script/insertTable(`&typeZ=[0]`);', $TYPE_Z));
        }
        else {
            $field2(new HTEL('script/shopList();'));
        }
    }
    else{
        $field2(new HTEL('script/insertTable(`[0]&typeZ=[1]`);', [$Z_DATA->GET_KOMPLECT(), $TYPE_Z]));
    }
}

if ($TYPE_Z == 'def' || $TYPE_Z == 'sold'){
    session_start();
    $div = new HTEL('div .=doneApply', ['worker', $Z_DATA->WORKER]);

    $idaccess = $_SESSION[$_SESSION['logged']] ?? 0;

    $query = 'SELECT `login` FROM `users` WHERE `login` <> "Administrator" AND `ID` > ' . $idaccess;

    $result = mysqli_query($link, $query);

    if (mysqli_num_rows($result) > 0){
        $select = new HTEL('select !=creator ?=creator', new HTEL('option #=[0]/-', $_SESSION['logged']));

        foreach ($result as $row) {
            $sel = $row['login'] == $Z_DATA->REDAKTOR ? 'selected' : '';
            $select(new HTEL('option #=[0] [1]/[0]', [$row['login'], $sel]));
        }

        $div(new HTEL('label for=creator/Переглядає [0], доступ -> ', $_SESSION['logged']));
        $div($select);
    }

    $div([
        new HTEL('label  for=[0]/Працівник: '),
        new HTEL('input !=[0] ?=[0] &=width:20%; $=якщо+відомо #=[1]'),
        new HTEL('button !=butSubm *=submit #=click /ЗБЕРЕГТИ')
    ]);
}else{
    $div = new HTEL('div .=doneApply',[
        new HTEL('button !=butSubm *=submit #=click /ОФОРМИТИ')
    ]);
}

$FORM([
    $field1,
    $field2,
    $div
]);

echo $FORM;

function selectStatus($bool = false):string{
    if ($bool)
        return 'selected';
    return '';
}

?>

<script>
    $("document").ready(function () {

    //Функція при перемиканні верхнього поля кольорів
       $("#color_variant").change(cb_color_ch).change();

       function cb_color_ch() {

           var radio_value = "";

           $(":radio").each(function () {
               var ischecked = $(this).is(":checked");
               if (ischecked) {
                   radio_value = $(this).attr("id").substring(4);
                   return false;
               }
           });

            $.ajax({
            url: 'blok/ch_var_col_set.php',
            method: 'GET',
            dataType: 'html',
                data: 'sposob='+radio_value+'<?php echo $Z_DATA->GET_KOMPLECT() . '&is_rewrite=' . $IS_CHANGE . '&ID=' . $Z_DATA->ID;  ?>',
                success: function (data) {
                $('#grid_color').html(data);
            }
            });
        };

       //ВНЕСЕННЯ ДАНИХ ПО ЗАЯВКАМ /вручну
       $("#form_create").submit(function () {

           if (!NUMBER_VALID) {
               alert('Такий номер заявки вже існує!');
               return false;
           }

           let dataForm = $("#form_create").serialize();
           let _sendGet = '<?php echo '&is_rewrite=' . $IS_CHANGE . '&typeZ=' . $TYPE_Z . '&ID=' . $Z_DATA->ID; ?>';

           $.ajax({
               url: 'blok/record_new_z.php',
               method: 'GET',
               dataType: 'html',
               data: dataForm + _sendGet + '&TO_PRINT=1',
                   success: function (data) {
                       $('#workfield').html(data);
                   }
               });
       });

        //ВНЕСЕННЯ ДАНИХ ПО ЗАЯВКАМ /абон
    $("#form_create_0").submit(function () {

        let dataForm = $("#form_create_0").serialize();
        let _sendGet = '<?php echo '&is_rewrite=0&typeZ=' . $TYPE_Z . '&ID=' . $Z_DATA->ID; ?>';

        $.ajax({
            url: 'blok/accept_dialog.php',
            method: 'GET',
            dataType: 'html',
            data: dataForm + _sendGet,
            success: function (data) {
                $('#dialog').html(data);
            }
        });
    });

    });

        var INPUT_NUM = document.getElementById('num');

    if (INPUT_NUM != null)
        INPUT_NUM.addEventListener("change", validate);

    function validate() {
        if (INPUT_NUM == null) NUMBER_VALID = true;

          if(Invalide_numbers.indexOf(INPUT_NUM.value) !== -1){
              INPUT_NUM.classList.add("invalid"); 
              NUMBER_VALID = false;
          } else {
              INPUT_NUM.classList.remove("invalid"); 
              NUMBER_VALID = true;
          }
        }
</script>

<?php $link->close();?>