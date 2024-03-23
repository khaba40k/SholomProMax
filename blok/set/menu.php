<?php
$head_buttons = new HTEL('div .=set_buttons', [
   new HTEL('button !=set_services/СЕРВІСИ'),
   new HTEL('button !=set_colors/КОЛЬОРИ')
]);

echo $head_buttons;
echo new HTEL('div !=set_workfield');
?>

<script>

    $('#set_services').on('click', SHOW_SERVICES);

    SHOW_SERVICES();

    function SHOW_SERVICES() {
        $.ajax({
            url: 'blok/set/services.php',
            success: function (response) {
                $('#set_workfield').html(response);
            }
        });
    }

</script>