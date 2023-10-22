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
    require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    require "blok/header.php";

    echo '<label />В РОЗРОБЦІ<br />';
    echo '<a href="/work" style="font-size:25px;">Перейти в адмінку</a><br /><br />';

    $div = new HTEL('div .=firstpage');

    $div(new HTEL('label/Про нас...'));

    $div([
        new HTEL('button !=createDef/Оформити переобладнання шолому'),
        new HTEL('button !=createSold/Покупка комплектуючих'),
        new HTEL('button !=createSoldS/Покупка шолому')
    ]);

    echo $div;

    ?>


</body>

</html>