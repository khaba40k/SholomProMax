<!DOCTYPE HTML>

<html>

<head>
    <title>SholomProMax</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
</head>

<body>

    <?php

    require "blok/header.php";

    require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    if (!isset($_GET['debug']) || $_GET['debug'] != 1) {
        HIDE();
    }else{
        session_start();
        $_SESSION['logged'] = 'Administrator';
    }

    if (!isset($_GET['page'])){
        $div = new HTEL('div .=firstpage');

        $div(new HTEL('div .=opis/[0]', file_get_contents('about.php')));

        $div([
            new HTEL("button !=createDef onclick=location.href=='index?page==newZdef'/Замовити переобладнання шолому"),
            new HTEL("button !=createSold onclick=location.href=='index?page==newZsold'/Покупка шолому/комплектуючих")
        ]);

        echo $div;
    }
    else{
        echo new HTEL('div !=workfield &=padding:1%+1%;');
        echo new HTEL('div !=dialog');
    }

    ?>

    <script>

        function newZ(type = 'def0') {

        $.ajax({
            url: 'blok/new_Z.php',
            method:'get',
            dataType: 'html',
            data: 'type=' + type,
            success: function (responce) {
                $('#workfield').html(responce);
            }
        });
    };

    </script>

    <?php

    if (isset($_GET['page'])) {
        switch ($_GET['page']) {
            case 'newZdef':
                echo new HTEL('script/newZ("def0");');
                break;
            case 'newZsold':
                echo new HTEL('script/newZ("sold0");');
                break;
        }
    }

    ?>

</body>

</html>