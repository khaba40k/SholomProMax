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

    require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    $cont = new HTEL('aside .=menu-cont+no-print', new HTEL('h2 .=menu-capt/МЕНЮ'));

    $ul = new HTEL('ul');

    $ul(new HTEL('li &=--clr:#2483ff',
       new HTEL('a @=work?page==newZdef', [
           new HTEL('i .=fa-solid+fa-createz'),
           new HTEL('span/Створити')
       ]))
    );

    $ul(new HTEL('li &=--clr:#fff200',[
    new HTEL('p !=count_z/89'),
    new HTEL('a @=#work', [
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
    //prices

    $ul(new HTEL('li &=--clr:#cccccc',
   new HTEL('a @=#', [
   new HTEL('i .=fa-solid+fa-prices'),
   new HTEL('span/Ціни')
   ]))
);
    //discounts

    $ul(new HTEL('li &=--clr:#00ff2a',
   new HTEL('a @=work?page==discount_list', [
   new HTEL('i .=fa-solid+fa-discounts'),
   new HTEL('span/Знижки')
   ]))
);

    $ul(new HTEL('li &=--clr:#ff58f1',
   new HTEL('a @=#', [
   new HTEL('i .=fa-solid+fa-sms'),
   new HTEL('span/Розсилка')
   ]))
);

    $ul(new HTEL('li &=--clr:#ff0e7a',
   new HTEL('a @=#', [
   new HTEL('i .=fa-solid+fa-zal'),
   new HTEL('span/Залишки')
   ]))
);

    $ul(new HTEL('li &=--clr:#6e6d2f',
   new HTEL('a @=#', [
   new HTEL('i .=fa-solid+fa-sumzvit'),
   new HTEL('span/Доходи')
   ]))
);

    $ul(new HTEL('li &=--clr:#46485b',
   new HTEL('a @=info', [
   new HTEL('i .=fa-solid+fa-print'),
   new HTEL('span/Робітнику')
   ]))
);

    $cont($ul);

    echo $cont;

    ?>

<style>

:root{
   --bg: #222;
   --clr: #fff;
}

ul li{
position: relative;
list-style: none;
width: 80px;
height: 80px;
display: flex;
justify-content: center;
align-items: center;
cursor: pointer;
transition: 0.5s;
}

ul li::before{
    content:'';
position: absolute;
inset: 30px;
box-shadow: 0 0 0 10px var(--clr),
0 0 0 20px var(--bg),
0 0 0 22px var(--clr);
transition: 0.5s;
}

ul li:hover::before{
inset: 15px;
}

ul li::after{
content: '';
position: absolute;
inset: 0px;
background: var(--bg);
transform: rotate(45deg);
transition: 0.5s;
}

ul li:hover::after{
inset: 0px;
transform: rotate(0deg);
}

ul li a {
position: relative;
text-decoration:none;
z-index: 10;
display: flex;
justify-content: center;
align-items: center;
}

ul li a i{
font-size: 2em;
transition: 0.5s;
color: var(--clr);
opacity: 1;
}

ul li p{
display: flex;
justify-content:center;
align-items: center;
position:absolute;
right: -7px;
top: -10px;
font-size: 0.7em;
color: var(--clr);
opacity: 1;
background-color: red;
border-radius: 50%;
width: 30px;
height: 30px;
z-index: 11;
font-weight: bold;
}

ul li:hover p{
   background: none;
}

ul li:hover a i{
    color: var(--clr);
    transform: translateY(-40%);
}

ul li a span{
font-size: 0.7em;
position: absolute;
font-family: sans-serif;
    color: var(--clr);
    opacity: 0;
    transition: 0.5s;
    transform: scale(0) translateY(200%);
}

ul li:hover a span{
opacity: 1;
transform: scale(1) translateY(150%);
}

ul li:hover a i,
ul li a span{
filter: drop-shadow(0 0 20px var(--clr)) drop-shadow(0 0 40px var(--clr)) drop-shadow(0 0 60px var(--clr));
}

.fa-solid{
    width:40px;
    height:40px;
    background-repeat: no-repeat;
    background-size:contain;
}

.fa-createz{
    background-image: url(/img/createz.png);
}

.fa-zlist{
    background-image: url(/img/zlist.png);
}

.fa-expens{
    background-image: url(/img/expens.png);
}

.fa-prices{
    background-image: url(/img/prices.png);
}

.fa-discounts{
    background-image: url(/img/discounts.png);
}

.fa-sms{
    background-image: url(/img/sms.png);
}

.fa-zal{
    background-image: url(/img/zal.png);
}

.fa-sumzvit{
    background-image: url(/img/sumzvit.png);
}

.fa-print{
    background-image: url(/img/print.png);
}

.menu-cont{
    position: relative;
    border-top: outset 45px green;
    border-radius: 25px 0 25px 0;
    background-color: var(--bg);
    padding-left: 25px;
    padding-top: 25px;
    display: inline-block;
    margin-top: 10px;
    margin-left: 10px;
}

.menu-capt{
      position: absolute;
      top: -40px;
      left: 0;
      padding: 0 15px;
      color: var(--clr);
}

    .menu-cont ul{
        display: grid;
        grid-template-columns: 33% 33% 33%;
    }

   .menu-cont ul > *{
        margin-bottom: 25px;
        margin-right: 25px;
   }

</style>

</body>

</html>

<script>

</script>