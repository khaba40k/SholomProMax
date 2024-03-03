<script>
    function showJurnal() {

                $.ajax({
                url: 'blok/exp/jurnal_period.php',
                dataType: 'html',
                    success: function (response) {
                        $('#zvitResult').html(response);
                }
                });

        return true;
    }

    function showForm() {
                $.ajax({
                url: 'blok/exp/expenses.php',
                dataType: 'html',
                success: function(response) {
                    $('#zvitResult').html(response);
                }
                });
    }

    $('.expenses_ch').on('click', function () {

        $('.expenses_ch').not(this).removeClass('clicked_but')
        $(this).toggleClass('clicked_but');

    });
</script>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$but_text = ['ЖУРНАЛ', 'РЕДАГУВАННЯ'];

echo new HTEL('div .=expensesMenu+no-print', new HTEL(
    'form !=expensesMenu onsubmit=return+false',
    [
        $but_text,
            new HTEL('button !=add .=expenses_ch+clicked_but onClick=showForm();/[1]'),
            new HTEL('button !=jurnal .=expenses_ch onClick=showJurnal();/[0]')
        ]
)
);

echo new HTEL('div !=zvitResult');

if (isset($_GET['page']) && $_GET['page'] == 'jurnal'){
     echo new HTEL('script/showJurnal();');
}
else{
     echo new HTEL('script/showForm();');
}

?>