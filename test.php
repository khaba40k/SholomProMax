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
    //session_start();
    //if (!isset($_SESSION['logged']) || $_SESSION['logged'] != 'Administrator')
    //    exit;

    //require("blok/conn_local.php");

    require "blok/header.php";

    require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    use LisDev\Delivery\NovaPoshtaApi2;

    $np = new NovaPoshtaApi2('90bb2c77ec2e2fba67348f82547f2f0a', 'ua', true, 'file_get_content');

    $cities = $np->getCities();

    var_dump($cities);

    ?>

</body>

</html>
