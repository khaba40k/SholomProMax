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
require 'blok/header.php';
?>

<div class="contacts">

    <span>
        🏠Наша адреса: м. Вишгород, Київської області.<br />
        Якщо бажаєте відвідати нашу майстерню - зателефонуйте нам і ми домовимось про зустріч ☎<br />
    </span>
    <span>
        📞Наш телефон: <span class="phone_span">+38 (095) 341 02 18</span><br />
        Доступні месенджери для зв'язку з адміністратором за вище вказаним номером телефону: 
        <img src="img/s_seti/telegram.png"/>, 
        <img src="img/s_seti/whatsapp.png"/>, 
        <img src="img/s_seti/viber.png"/>, 
        <img src="img/s_seti/signal.png"/>
        📲<br />
    </span>
    <span class="grafik">
        📆Графік роботи:<br />
        Понеділок - пʼятниця:<br />
        з 10:00 - 19:00 год.<br />
        Субота:<br />
        з 10:00 - 17:00 год.<br />
        Неділя - вихідний.<br />
    </span><br />
    <p>ШоломProMax в інших соціальних мережах:</p>
    <span class="soc_m">
        <a href="https://tiktok.com/@sholompromax">
            <img src="img/s_seti/tiktok.png" />
        </a>
        <a href="https://instagram.com/sholompromax">
            <img src="img/s_seti/instagram.png" />
        </a>
        <a href="mailto:sholompromax@gmail.com">
            <img src="img/s_seti/email.png" />
        </a>
        <a href="https://facebook.com/profile.php?id=100093650869055">
            <img src="img/s_seti/facebook.png" />
        </a>
    </span>

</div>


<style>
a > img{
    width: 48px;
    height: 48px;
}   

img{
    width: 16px;
    height: 16px;
}

a{
    margin: 0 5px;
}

.contacts{
    position: relative;
    top: 10%;
    width: 80%;
    margin: 0 10%;
    padding: 5%;
    background-color: olive;
    border-radius: 20px;
}

.soc_m{
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

    .phone_span{
         font-weight: bold;
         font-size: 120%;
         color: yellow;
    }

    .grafik{
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 110%;
    }
</style>

</body>

</html>