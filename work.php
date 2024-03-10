<!DOCTYPE HTML>

<html>

<head>

    <title>Консоль</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

</head>

<body>
    <?php

    session_start();

    #region Авторизація тимчасова
    $tempAuth = $_GET['ds;gzjfmsds;fds'] ?? null;

    if ($tempAuth == 'shrtdgzf') {
        $_SESSION['logged'] = 'Administrator';
        $_SESSION['Administrator'] = 0;
    }
    #endregion

    require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    $conn = new SQLconn();

    if (!isset($_SESSION['logged'])){

        if(isset($_POST['log']) && isset($_POST['pas'])){

            $query = 'SELECT * FROM users
              where login = "' . $_POST['log'] . '"
              and password = "' . $_POST['pas'] . '"';

            $result = $conn($query);

            if (count($result) == 0 ||
                $_POST['log'] != trim($_POST['log']) ||
                $_POST['pas'] != trim($_POST['pas'])) {

                $_SESSION['msg'] = "Пароль або логін не підійшов!";
                header('Location: admin');
            }
            else {
                $result = $conn('SELECT login, ID FROM users');

                foreach ($result as $row){
                    $_SESSION[$row['login']] = $row['ID'];
                }

                $_SESSION['logged'] = $_POST['log'];
            }

            unset($_POST);
        }
        else{
            header('Location: admin');
        }
    }

    #region Підрахунок заявок
    $query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NULL AND `TTN_IN` IS NULL';

    $_SESSION['count_new'] = count($conn($query));

    $query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NULL AND (`TTN_IN` IS NOT NULL OR `sold_number` IS NOT NULL)';

    $_SESSION['count_inwork'] = count($conn($query));

    $query = 'SELECT `sholom_num` FROM `client_info` WHERE `date_out` IS NOT NULL';

    $_SESSION['count_archiv'] = count($conn($query));

    $conn->close();
    #endregion

    $activ_count = $_SESSION['count_new'] + $_SESSION['count_inwork'];

    $_GET['header'] = 'admin';
    require "blok/header.php";

    HIDE();

    $wrapper = new HTEL('div .=wrapper');

    $aside = new HTEL('aside .=menu-cont+no-print', new HTEL('h2 .=menu-capt/МЕНЮ'));

    $ul = new HTEL('ul');

    $ul(new HTEL('li &=--clr:#2483ff',
       new HTEL('a @=work?page==newZdef', [
           new HTEL('i .=fa-solid+fa-createz'),
           new HTEL('span/Створити')
       ]))
    );

    $ul(new HTEL('li &=--clr:#fff200',[
    new HTEL('p !=count_z/[0]', ($activ_count > 0 ? $activ_count : '-')),
    new HTEL('a @=work', [
       new HTEL('i .=fa-solid+fa-zlist'),
       new HTEL('span/Замовлення')
    ])])
);

    $ul(new HTEL('li &=--clr:#ff0000',
   new HTEL('a @=work?page==expens', [
       new HTEL('i .=fa-solid+fa-expens'),
       new HTEL('span/Витрати')
   ]))
);

    if (!isset($_SESSION[$_SESSION['logged']]) || $_SESSION[$_SESSION['logged']] <= 1){
               $ul(new HTEL('li &=--clr:#cccccc',
          new HTEL('a @=work?page==price_list', [
          new HTEL('i .=fa-solid+fa-prices'),
          new HTEL('span/Ціни')
          ]))
       );

        $ul(new HTEL('li &=--clr:#00ff2a',
       new HTEL('a @=work?page==discount_list', [
       new HTEL('i .=fa-solid+fa-discounts'),
       new HTEL('span/Знижки')
       ]))
    );

        $ul(new HTEL('li &=--clr:#ff58f1',
       new HTEL('a @=work?page==sms_sender', [
       new HTEL('i .=fa-solid+fa-sms'),
       new HTEL('span/Розсилка')
       ]))
    );

        $ul(new HTEL('li &=--clr:#ff0e7a',
   new HTEL('a @=work?page==zal', [
   new HTEL('i .=fa-solid+fa-zal'),
   new HTEL('span/Залишки')
   ]))
);

        $ul(new HTEL('li &=--clr:#6e6d2f',
       new HTEL('a @=work?page==sfp', [
       new HTEL('i .=fa-solid+fa-sumzvit'),
       new HTEL('span/Доходи')
       ]))
    );

    }

    $ul(new HTEL('li &=--clr:#46485b',
   new HTEL('a @=info', [
   new HTEL('i .=fa-solid+fa-print'),
   new HTEL('span/Робітнику')
   ]))
);

    $aside($ul);

    $wrapper([
        $aside,
        new HTEL('main !=workfield')
    ]);

    echo $wrapper;

    require "blok/footer.php";

    ?>

    <script>

    function newZ($page = 'def') {

        $.ajax({
            url: 'blok/z_list/zakazi.php',
            method: 'GET',
            dataType: 'html',
            data: 'menu_type=create&page=' + $page,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    };

    //Кнопка Активні замовлення

    function activeZedit($page = 'new', send = '') {

        $.ajax({
            url: 'blok/z_list/zakazi.php',
            method: 'get',
            dataType: 'html',
            data: 'menu_type=list&page=' + $page + send,
            success: function (response) {
                $('#workfield').html(response);
            }
        });
    };

    //Кнопка ЦІНИ
    //$("#formPrice").on("click", priceEdit);

    function priceEdit() {

        $.ajax({
            url: 'blok/price_list.php',
            dataType: 'html',
            success: function (response) {
                $('#workfield').html(response);
            }
        });
    };

    //Кнопка Знижки

    function discountEdit() {

        $.ajax({
            url: 'blok/discount_list.php',
            dataType: 'html',
            success: function (response) {
                $('#workfield').html(response);
            }
        });
    };

    //Кнопка звіту за період
    function SumFromPeriod() {

        let currentdate = new Date();

        let dateNow = currentdate.getFullYear() + "-"
            + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-"
            + currentdate.getDate().toString().padStart(2, '0');
        let date1 = currentdate.getFullYear() + "-"
            + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-01";

        let dataForm = 'ot=' + date1 + '&do=' + dateNow;

        $.ajax({
            url: 'blok/zvit/zvit_menu.php',
            method: 'GET',
            dataType: 'html',
            data: dataForm,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    };
    //Кнопка внесення витрат на товар, списання, продаж
    //$("#expenses").on("click", function () { newPurchase('jurnal'); });

    function newPurchase($page = "jurnal") {

        $.ajax({
            url: 'blok/exp/expenses_menu.php',
            method: 'GET',
            dataType: 'html',
            data: 'page=' + $page,
            success: function (data) {
                $('#workfield').html(data);
            }
        });
    }

    //Кнопка показу залишків

     function ZALForm() {

        $.ajax({
            url: 'blok/zvit/table_count.php',
            dataType: 'html',
            success: function (response) {
                $('#workfield').html(response);
            }
        });

    };

        function SMSSend() {

            $.ajax({
                url: 'blok/sms/sms_board.php',
                dataType: 'html',
                success: function (response) {
                    $('#workfield').html(response);
                }
            });

        };

    function Search($request) {
        $('#request').val($request);

        if ($request.trim() == '') return false;

        $.ajax({
            url: 'blok/z_list/active_z.php',
            method: 'get',
            dataType: 'html',
            data: 'search=' + $request,
            success: function (data) {
                $('#workfield').html(data);
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
                echo new HTEL('script/activeZedit("archiv", "[0]");', _requestSend($_GET));
                break;
            case 'discount_list':
                echo new HTEL('script/discountEdit();');
                break;
            case 'price_list':
                echo new HTEL('script/priceEdit();');
                break;
            case 'sms_sender':
                echo new HTEL('script/SMSSend();');
                break;
            case 'zal':
                echo new HTEL('script/ZALForm();');
                break;
            case 'sfp':
                echo new HTEL('script/SumFromPeriod();');
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