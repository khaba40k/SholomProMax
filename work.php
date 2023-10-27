<!DOCTYPE HTML>

<html>

<head>

    <title>Консоль</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

</head>

<body>
    <?php

    session_start();

    if (!isset($_SESSION['logged'])){

        if(isset($_POST['log']) && isset($_POST['pas'])){
            require "blok/conn_local.php";

            $query = 'SELECT * FROM `users`
              where `login` = "' . $_POST['log'] . '"
              and `password` = "' . $_POST['pas'] . '"';

            $result = mysqli_query($link, $query);

            if (mysqli_num_rows($result) == 0) {
                $_SESSION['msg'] = "Пароль не підійшов!";
                $link->close();
                header('Location: admin');
            }
            else {

                $query = 'SELECT `login`,`ID` FROM `users`';

                $result = mysqli_query($link, $query);

                foreach ($result as $row){
                    $_SESSION[$row['login']] = $row['ID'];
                }

                $link->close();

                $_SESSION['logged'] = $_POST['log'];
            }

            unset($_POST);
        }
        else{
            header('Location: admin');
        }
    }

    require "blok/conn_local.php";
    require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    //$query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NULL';

    //$result = mysqli_query($link, $query);

    $query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NULL AND `TTN_IN` IS NULL';

    $result = mysqli_query($link, $query);

    $_SESSION['count_new'] = mysqli_num_rows($result);

    $query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NULL AND `TTN_IN` IS NOT NULL';

    $result = mysqli_query($link, $query);

    $_SESSION['count_inwork'] = mysqli_num_rows($result);

    $query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NOT NULL';

    $result = mysqli_query($link, $query);

    $_SESSION['count_archiv'] = mysqli_num_rows($result);

    $link->close();

    $activ_count = $_SESSION['count_new'] + $_SESSION['count_inwork'];

    $_GET['header'] = 'admin';
    require "blok/header.php";

    $wrapper = new HTEL('div .=wrapper');
    $aside = new HTEL('aside .=no-print');
    $form = new HTEL('form !=feedBack method=post onsubmit=return+false');
    $form(new HTEL('label/РЕДАГУВАННЯ'));

    //    if ($_SESSION[$_SESSION['logged']] <= 1)

    $div = new HTEL('div !=fb1', [
        new HTEL("button !=create_Z onclick=location.href=='work?page==newZdef'/НОВЕ ЗАМОВЛЕННЯ"),
        new HTEL("button !=active_Z onclick=location.href=='work'/ЗАМОВЛЕННЯ[0]", ($activ_count > 0 ? ' (' . $activ_count . ')' : '')),
        new HTEL("button !=expenses onclick=location.href=='work?page==expens'/ВИТРАТИ")
    ]);

    if ($_SESSION[$_SESSION['logged']] <= 1)
    $div([
        new HTEL("button !=formPrice/ЦІНИ")
    ]);

    $form($div);

    $form(new HTEL('label/ЗВІТИ'));

    $div = new HTEL('div !=fb2');

    if ($_SESSION[$_SESSION['logged']] <= 1)
    $div([
        new HTEL("button !=zal_show /ЗАЛИШКИ"),
        new HTEL("button !=period_show /РУХ ЗА ПЕРІОД...")
    ]);

    $div(new HTEL("button !=toInfo onclick=location.href=='info'/Друк для робітника"));

    $form($div);

    $aside($form);

    $wrapper([
        $aside,
        new HTEL('main !=workfield')
    ]);

    echo $wrapper;

    ?>

    <!--<div class="wrapper">
        <aside class="no-print">
            <form id="feedBack" method="post" onsubmit="return false">

                <label>РЕДАГУВАННЯ</label>
                <div id="fb1">
                    <button id="create_Z" onclick="location.href='work?page=newZdef'">НОВЕ ЗАМОВЛЕННЯ</button>
                    <button id="active_Z" onclick="location.href='work'">ЗАМОВЛЕННЯ<php echo $activ_count > 0 ? ' ('.$activ_count . ')': '' ?>
                    </button>
                    <button id="expenses" onclick="location.href='work?page=expens'">ВИТРАТИ</button>
                    <button id="formPrice">ЦІНИ</button>
                </div>

                <label>ЗВІТИ</label>
                <div id="fb2">
                    <button id="zal_show">ЗАЛИШКИ</button>
                    <button id="period_show">РУХ ЗА ПЕРІОД...</button>
                    <button id="toInfo" onclick="location.href='info'">Друк для робітника</button>
                </div>
            </form>
        </aside>

        <main id="workfield"></main>
    </div>-->

    <script>

    function newZ($page = 'def') {

        $.ajax({
            url: 'blok/zakazi.php',
            method: 'GET',
            dataType: 'html',
            data: 'menu_type=create&page=' + $page,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    };

    //Кнопка Активні замовлення
    //$("#active_Z").on("click", function () { activeZedit(); });

    function activeZedit($page = 'new') {

        $.ajax({
            url: 'blok/zakazi.php',
            method: 'get',
            dataType: 'html',
            data: 'menu_type=list&page=' + $page,
            success: function (response) {
                $('#workfield').html(response);
            }
        });
    };

    //Кнопка ЦІНИ
    $("#formPrice").on("click", priceEdit);

    function priceEdit() {

        let dataForm = $(this).serialize()

        $.ajax({
            url: 'blok/price_list.php',
            method: 'POST',
            dataType: 'html',
            data: dataForm,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    };

    //Кнопка звіту за період
    $("#period_show").on("click", function () {

        let currentdate = new Date();

        let dateNow = currentdate.getFullYear() + "-"
            + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-"
            + currentdate.getDate().toString().padStart(2, '0');
        let date1 = currentdate.getFullYear() + "-"
            + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-01";

        let dataForm = 'ot=' + date1 + '&do=' + dateNow;

        $.ajax({
            url: 'blok/zvit_menu.php',
            method: 'GET',
            dataType: 'html',
            data: dataForm,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    });
    //Кнопка внесення витрат на товар, списання, продаж
    //$("#expenses").on("click", function () { newPurchase('jurnal'); });

    function newPurchase($page = "jurnal") {

        $.ajax({
            url: 'blok/expenses_menu.php',
            method: 'GET',
            dataType: 'html',
            data: 'page=' + $page,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    }

    //Кнопка показу залишків

    $("#zal_show").on("click", function () {

        $.ajax({
            url: 'blok/table_count.php',
            dataType: 'html',
            success: function (response) {
                $('#workfield').html(response);
            }
        });

    });

    function Search($request) {
        $('#request').val($request);

        if ($request.trim() == '') return false;

        $.ajax({
            url: 'blok/active_z.php',
            method: 'get',
            dataType: 'html',
            data: 'search=' + $request,
            success: function (response) {
                $('#workfield').html(response);
            }
        });

    }

    $("#request").focus();

    $(document).ready(function () {
        document.body.setScaledFont = function (f) {
            var s = this.offsetWidth, fs = s * f;
            this.style.fontSize = fs + '%';
            return this
        };
        document.body.setScaledFont(0.1);
        window.onresize = function () {
            document.body.setScaledFont(0.1);
        }
    });
    </script>

    <?php
    if (isset($_GET['page'])) {
        switch ($_GET['page']) {
            case 'newZdef':
                echo new HTEL('script/newZ("def");');
                break;
            case 'newZsold':
                echo new HTEL('script/newZ("sold");');
                break;
            case 'expens':
                echo new HTEL('script/newPurchase("expens");');
                break;
            case 'jurnal':
                echo new HTEL('script/newPurchase("jurnal");');
                break;
            case 'inwork':
                echo new HTEL('script/activeZedit("inwork");');
                break;
            case 'archiv':
                echo new HTEL('script/activeZedit("archiv");');
                break;
            default:
                echo new HTEL('script/activeZedit();');
                break;
        }
    } else if (isset($_GET['search'])){
        echo new HTEL('script/Search(`[0]`);', $_GET['search']);
    }
    else {
        echo new HTEL('script/activeZedit();'); //Сторінка за замовчуванням
    }
    ?>

</body>

</html>