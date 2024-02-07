<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$fs = new HTEL('fieldset .=fs_byhand_sms', [
    new HTEL('legend/Повідомлення'),
    new HTEL('input ?=tel *=tel $=номер+телефону maxlength=13 minlength=10 [r]'),
    new HTEL('textarea ?=mes $=ТЕКСТ+ПОВІДОМЛЕННЯ minlength=1 [r]'),
    new HTEL('button *=submit #=click/НАДІСЛАТИ'),
    new HTEL('label !=ans/...')
]);

$form = new HTEL('form !=sms_byhand_form onsubmit=return+false', $fs);

echo $form;

?>

<script>

    $('#sms_byhand_form').submit(function () {
        var data = $(this).serialize();

        $.post('blok/sms/sms_send.php', data, function (result) {
               $('#ans').empty();
               $('#ans').append(result);
        });
    });

</script>