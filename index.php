<!DOCTYPE HTML>

<html>

<head>
    <title>SholomProMax</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />

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

        $div(new HTEL('label/Опис послуг та про нас...'));

        $div([
            new HTEL("button !=createDef onclick=location.href=='index?page==newZdef'/Оформити переобладнання шолому"),
            new HTEL("button !=createSold onclick=location.href=='index?page==newZsold'/Покупка комплектуючих"),
            new HTEL("button !=createSoldS onclick=location.href=='index?page==newZsoldHem'/Покупка шолому")
        ]);

        echo $div;
    }
    else{
        echo new HTEL('div !=workfield &=padding:1%+1%;');
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
            case 'newZsoldHem':
                echo new HTEL('script/newZ("sold1");');
                break;
        }
    }

    ?>

</body>

</html>