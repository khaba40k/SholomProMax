<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

if (isset($_GET['new_f'])) {
    echo NewFilter($_GET['new_f']);
    exit;
}

$KOMPL_LIST = array();

$result = new SQLconn('SELECT ID, NAME FROM service_ids WHERE atr >= 0 AND atr <> 16 ORDER BY `order`');

$KOMPL_LIST[] = new HTEL('option # [d] [s]/обери...');

foreach ($result() as $row) {
    $KOMPL_LIST[] = new HTEL('option #=[0]/[1]', [$row['ID'], $row['NAME']]);
}

if (isset($_GET['new_f_ans'])) {
    echo NewAnswerFromFilter($_GET['new_f_ans']);
    exit;
}

$filers = new HTEL('fieldset !=filters .=filters &=padding:1%;',
[new HTEL('legend/Фільтри'),
new HTEL('button !=clear_filter/X')]);

$filers(NewFilter());

$message = new HTEL('fieldset !=message &=padding:1%;', [
    new HTEL('legend/Повідомлення'),
    new HTEL('textarea !=mes_text $=ТЕКСТ+ПОВІДОМЛЕННЯ &=width:100%;min-height:100px;resize:vertical;'),
    new HTEL('button !=start_button/НАДІСЛАТИ')
]);

$client_list = new HTEL('fieldset &=padding:1%;', [new HTEL('legend/ОТРИМУВАЧІ'), new HTEL('form !=clientlist')]);

function NewFilter($id = 0): HTEL
{
    $filter_list = [
        'Тип заявки',
        'Статус заявки',
        'Період створення',
        'Період відправки',
        'Включає...',
        'Не включає...'
    ];

    $select = new HTEL('select !=f_[0] .=filter_select atr=[0]',  [$id, new HTEL('option #')]);

    foreach ($filter_list as $k=>$f){
        $select(new HTEL('option #=[0]/[1]', [$k, $f]));
    }

    return new HTEL('div !=fdiv_[0] .=line_filter #=[0]', [$id, $select, new HTEL('div !=ans_[0] .=filter_ans')]);
}

function NewAnswerFromFilter($id):HTEL{

    $kompl = $GLOBALS['KOMPL_LIST'];

    $rand = rand(1000, 9999);

    $ARR_OUT = [
        0 => new HTEL('select ?=[0]_filter_0', [
            $rand, new HTEL('option # [d] [s]/обери...'),
        new HTEL('option #=0/Переобладнання'), new HTEL('option #=1/Продаж')]),
        1 => new HTEL('select ?=[0]_filter_1', [
            $rand,
            new HTEL('option # [d] [s]/обери...'), new HTEL('option #=0/Нові'), new HTEL('option #=1/В роботі'),
        new HTEL('option #=2/Архів')]),
        2 => new HTEL('div', [$rand, new HTEL('input ?=[0]_filter_2_ot *=date'), new HTEL('input ?=[0]_filter_2_do *=date')]),
        3 => new HTEL('div', [$rand, new HTEL('input ?=[0]_filter_3_ot *=date'), new HTEL('input ?=[0]_filter_3_do *=date')]),
        4 => new HTEL('select ?=' . $rand . '_filter_4', $kompl),
        5 => new HTEL('select ?=' . $rand . '_filter_5', $kompl)
    ];

    return $ARR_OUT[$id] ?? new HTEL();
}

echo $filers;
echo $message;
echo $client_list;

?>

<script>

    var LASTID = 0;
    var COUNT = 1;

    AppendFilters();

    function ADD_ROW_FILTER(_id, _val) {
        if (_val != '' && LASTID == _id) {
            LASTID++;

            $.get(
                'blok/sms/sms_menu.php',
                'new_f=' + LASTID,
                function (result) {
                    $('#filters').append(result);
                    
                    COUNT++;
                }
            );

        } else if (_val == '' && COUNT > 1) {
            COUNT--;
            $('#fdiv_' + _id).remove();
        }
    }

    function SET_ANS(_id, _val) {
        $.get(
          'blok/sms/sms_menu.php',
          'new_f_ans=' + _val,
            function (result) {
                $('#ans_' + _id).empty();
                $('#ans_' + _id).append(result);
        }
    );
    }

    $("#filters").on("change", ".filter_select", function () {
        var id = $(this).attr('atr');
        var val = $(this).val();

        ADD_ROW_FILTER(id, val);
        SET_ANS(id, val);
    });
    
    $("#filters").on("change", ".filter_ans > *, .filter_select", function () {
        //ТУТ ФОРМУЮ ЗАПРОС

        AppendFilters();
    });

    $('#clear_filter').on('click', function () {
        $.get(
        'blok/sms/sms_menu.php',
        'new_f=' + 0,
        function (result) {

            $('#filters > div').remove();
            $('#filters').append(result);

            LASTID = 0;
            COUNT=1;
        }
        );

        window.setTimeout(AppendFilters, 300);
    });

    function AppendFilters() {
        var data = $('#filters').serializeArray();

         $.post('blok/sms/get_client_list_query.php', data, function (result) {
             $('#clientlist').empty();
             $('#clientlist').append(result);
         });
    }

    $('#start_button').on('click', function () {

        var data = JSON.stringify( $('#clientlist').serializeArray());
        var text = $('#mes_text').val();

        $.post('blok/sms/sms_send.php', {mes: text, tellist: data}, function (result) {
            $('#clientlist').empty();
            $('#clientlist').append(result);
        });

    });

</script>
