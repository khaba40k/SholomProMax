<?php
     session_start();
     if (isset($_SESSION['logged'])) {
         header('Location: work');
         exit;
     }
?>

<!DOCTYPE HTML>

<html>

<head>

    <title>SholomProMax</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />

</head>

<body>

    <?php
    require "blok/header.php";
    ?>

    <!--форма авторизации-->

    <div class="autorize">
        <form action="work" method="post">

            <label>Логін</label>
            <input type="text" name="log" placeholder="Вкажи логін" />
            <label>Пароль</label>
            <input type="password" name="pas" placeholder="Підтверди паролем" />
            <button>Авторизуватись</button>

    <?php
        if (isset($_SESSION['msg'])){
            echo '<p class="msg">' . $_SESSION['msg'] . '</p>';
            unset($_SESSION['msg']);
        }
    ?>
        </form>
    </div>

</body>

</html>