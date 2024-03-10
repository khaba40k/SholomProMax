<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    $container = new HTEL('div .=sms-info');

    $conn = new SQLconn();

    $result = $conn('select * from message_text order by date_time desc');

    foreach ($result as $row){

        $count = $conn('select COUNT(*) as count from message_info WHERE mes_id = ' . $row['id']);

        foreach ($count as $c){
            $count = $c['count'];
        }

        $phpdate = strtotime( $row['date_time'] );
        $mysqldate = date( 'd.m.y H:i:s', $phpdate );

        $container(new HTEL('div .=one-mes', [
           new HTEL('button !=[0] .=podm_sms_list *=button', $row['id']),
           new HTEL('label/[0]', $mysqldate),
           new HTEL('textarea .=mes-txt-area [ro]/[0]', $row['text']),
           new HTEL('div .=receiverPar', [
                new HTEL('div .=receiver'),
                new HTEL('label /[0]', $count)
           ]),
           new HTEL('label/[0]', $row['sender'])
        ]));
    }

    $conn->close();

    echo $container;
?>

<style>
.sms-info{
padding: 20px;
display: grid;
width: 100%;
font-size: 80%;
}

.one-mes{
background-color: #4c8f3f;
border: outset 5px green;
border-radius: 10px;
width: 100%;
display: inline-grid;
grid-template-columns: 5% 15% auto 15% 15%;
margin-bottom: 10px;
}
    .one-mes > * {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 5px;
    }

    .one-mes > label{
        text-align: center;
    }

    .one-mes > textarea{
    resize: none;
    min-height: 50px;
    padding: 5px;
    border-radius: 10px;
    }

    .one-mes > button{
         text-align: center;
         background: none;
         border: none;
         background-image: url(/img/podmenu.png);
         background-repeat: no-repeat;
         background-size: contain;
    }

    div[class=list] {
        grid-column: span 5/6;
        padding: 10px;
    }

        div[class=list] > *{
          width:  100%;
        }

            div[class=list] > tr{
             padding: 5px 0;
            }

            div[class=list] > td{
             padding: 0 5px;
            }

    .receiverPar{
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border: solid 2px yellow;
        border-radius: 5px;
        padding: 0 5%;
    }

        .receiverPar > label{
        text-align: right;
        width: 60%;
        }

    .receiver{
        width: 40%;
        height: 40%;
        background-image: url(/img/human.png);
        background-repeat: no-repeat;
        background-size: contain;
    }

</style>

<script>

    $(document).ready(function () {//Розтягування текстареї по висоті повідомлення
        var _H;
        $('.mes-txt-area').each(function () {
            _H = ($(this).prop('scrollHeight') + 4) + 'px';
            $(this).css('min-height', _H);
        });

    });

    $('.podm_sms_list').on('click', function () {
        var _id = $(this).attr('id');
        var par = $(this).parent();

        $('.list').remove();

        $.post('blok/sms/sms_info_number_list.php', { id: _id }, function (result) {
            par.append(result);
        });
    });

</script>