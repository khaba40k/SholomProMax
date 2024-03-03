<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$now = date('m.Y');

$period_board = new HTEL(
    'div .=period_jurnal',
    [
        new HTEL('button !=prev/<'),
        new HTEL('label !=per/[0]', $now),
        new HTEL('button !=next/>')
    ]
);

echo $period_board;
echo new HTEL('div !=jurnal_content');
?>

<script>

    var CURRENT_PERIOD = ShowJurnal();

    function ShowJurnal(period = null) {
        var month; var year;

        if (period == null) {
            period = new Date();

            month = period.getUTCMonth() + 1;
            year = period.getUTCFullYear();
        }
        else {
            var spl = period.split('.', 2);
            month = spl[0];
            year = spl[1];
        }
        
        $.ajax({
            url: 'blok/exp/jurnal.php',
            dataType: 'html',
            data: { month: month, year: year },
            success: function (data) {
                $('#jurnal_content').html(data);
            }
        });

        return ('0' + month).slice(-2) + '.' + year;
    }

    $('#prev').on('click', function () {
        var spl = CURRENT_PERIOD.split('.', 2);
        var m = spl[0];
        var y = spl[1];

        if (m > 1) {
            m--;
        } else {
            m = 12;
            y--;
        }

        CURRENT_PERIOD = ShowJurnal(m + '.' + y);

        $('#per').empty();
        $('#per').append(CURRENT_PERIOD);
    });

    $('#next').on('click', function () {
        var spl = CURRENT_PERIOD.split('.', 2);
        var m = spl[0];
        var y = spl[1];

        if (m < 12) {
            m++;
        } else {
            m = 1;
            y++;
        }

        CURRENT_PERIOD = ShowJurnal(m + '.' + y);

        $('#per').empty();
        $('#per').append(CURRENT_PERIOD);
    });

</script>