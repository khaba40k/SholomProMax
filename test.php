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

    $conn = new SQLconn();

    $_COLORS = array();

    $result = $conn('SELECT * FROM colors');
    $map = $conn('SELECT * FROM color_map');

    foreach ($result as $row) {
        $_COLORS[$row['ID']] = new MyColor2($row['ID'], $row['color'], $map, $row['css_name'], $row['is_def']);
    }

    $conn->close();

    foreach ($_COLORS as $c){
        echo $c->ID .' => '. $c->AppleTo(6,2) . "\n<br>";
        //echo $c->ID .' => '. $c->ANS(9) . "\n<br>";
    }

    var_dump($_COLORS);

    ?>

        <style>

        </style>
    <script></script>
</body>

</html>

