<script>
    function showJurnal() {
        $out = true;

                $.ajax({
                url: 'blok/jurnal.php',
                dataType: 'html',
                    success: function (response) {
                        if (response != null) {
                                $('#zvitResult').html(response);
                        }
                        else {
                                $out = false;
                        }
                }
                });

        return $out;
    }

    function showForm() {
                $.ajax({
                url: 'blok/expenses.php',
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
        new HTEL('button !=jurnal .=expenses_ch+clicked_but onClick=showJurnal();/[0]'),
        new HTEL('button !=add .=expenses_ch onClick=showForm();/[1]')
    ]
)
);

echo new HTEL('div !=zvitResult');

if (isset($_GET['page']) && $_GET['page'] != 'jurnal'){
     echo new HTEL('script/showForm();');
}
else{
     echo new HTEL('script/showJurnal();');
}

?>