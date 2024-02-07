<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$board = new HTEL('div', [
    new HTEL('div .=sms_content', [
        new HTEL('button !=byfilters .=clicked_but+sms_board_but/ЗА ФІЛЬТРАМИ'),
        new HTEL('button !=byhand .=sms_board_but/ВРУЧНУ')
    ]),
    new HTEL('div !=sms_content .=sms_content')
]);

echo $board;

?>

<script>

    $('#byfilters').on('click', function () { setByFilters(); });

    setByFilters();

    function setByFilters() {
        $.ajax({
           url: 'blok/sms/sms_menu.php',
           dataType: 'html',
           success: function (response) {
               $('#sms_content').html(response);
           }
        });
    }

    $('#byhand').on('click', function () {
        $.ajax({
            url: 'blok/sms/sms_byhand.php',
            dataType: 'html',
            success: function (response) {
                $('#sms_content').html(response);
            }
        });
    });

    $('.sms_board_but').on('click', function () {
        $('.sms_board_but').not(this).removeClass('clicked_but')
        $(this).toggleClass('clicked_but');
    });

</script>