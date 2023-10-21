<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$but_text = ['Тиждень', 'Поточний місяць', 'Минулий місяць', 'Рік', 'Весь час'];

echo new HTEL('div .=zvitMenu+no-print', new HTEL(
    'form !=zvitMenu onsubmit=return+false',
    [
        $but_text,
        new HTEL('button !=week .=perChng/[0]'),
        new HTEL('button !=mountNow .=perChng+clicked_but/[1]'),
        new HTEL('button !=mountPrev .=perChng/[2]'),
        new HTEL('button !=year .=perChng/[3]'),
        new HTEL('button !=allTime .=perChng/[4]'),
        new HTEL('div !=periodVariant', [
                $_GET['ot'],
                $_GET['do'],
            new HTEL('label for=ot/ПЕРІОД'),
            new HTEL('input !=ot *=date ?=ot #=[0]'),
            new HTEL('input !=do *=date ?=do #=[1]')
        ])
    ]
)
);

echo new HTEL('div !=zvitResult');

?>

<script>
    $( "#periodVariant" ).change(showTable).change();

    function showTable() {
                $.ajax({
                url: 'blok/table_sum.php',
                method: 'GET',
                dataType: 'html',
                data: $("#zvitMenu").serialize(),
                success: function(data) {
                    $('#zvitResult').html(data);
                }
                });
    }

    $('.perChng').on('click', function () {

        $('.perChng').not(this).removeClass('clicked_but')
        $(this).toggleClass('clicked_but');

        let currentdate = new Date();

        let dateOT;
        let dateDO = currentdate.getFullYear() + "-"
                + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-"
                    + currentdate.getDate().toString().padStart(2, '0');

        switch ($(this).attr("id")) {
            case 'week':
                currentdate.setDate(currentdate.getDate() - 7);

                    dateOT = currentdate.getFullYear() + "-"
                + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-"
                    + currentdate.getDate().toString().padStart(2, '0');
                break;
            case 'mountNow':
                    dateOT = currentdate.getFullYear() + "-"
                + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-01";
                break;
            case 'mountPrev':
                dateOT = currentdate.getFullYear() + "-"
                    + (currentdate.getMonth()).toString().padStart(2, '0') + "-01";

                currentdate = new Date(currentdate.getFullYear(), currentdate.getMonth(), 0);

                dateDO = currentdate.getFullYear() + "-"
                    + (currentdate.getMonth() + 1).toString().padStart(2, '0') + "-" +
                        + currentdate.getDate().toString().padStart(2, '0');
                break;
            case 'year':
                    dateOT = currentdate.getFullYear() + "-01-01";
                break;
            case 'allTime':
                dateOT = "2000-01-01";
                break;
        }

        ChangePeriod(dateOT, dateDO);
    })

    function ChangePeriod(_ot, _do) {

          $("#ot").attr("value", _ot);
          $("#do").attr("value", _do);

         showTable();
    }
</script>