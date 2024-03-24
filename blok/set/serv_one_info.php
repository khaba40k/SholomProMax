<?php
    $SERV_ID = $_GET['ID'] ?? null;
    $SERV_NAME = $_GET['NAME'] ?? '';

    if ($SERV_ID === null || $SERV_ID == "") exit;

    require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    $service_types = [
        1=>'Замовлення',
        2=>'Замовлення (з адмінки)',
        4=>'Продаж',
        8=>'Продаж (з адмінки)',
        16=>'Витрати'
    ];

    $conn = new SQLconn();

    $formOut = new HTEL('form !=set_info onsubmit=return+false');

    #region Отримання списку кольорів
    $_COLORS = array();

    $result = $conn('SELECT * FROM colors');

    $map = $conn('SELECT * FROM color_map');

    foreach ($result as $row) {
        $_COLORS[$row['ID']] = new MyColor($row['ID'], $row['color'], $map, $row['css_name'], $row['is_def']);
    }
    #endregion

    //Список атрибутів сервісу

    $fsAttr = new HTEL('fieldset !=attr_info .=attr_info', new HTEL('legend/Атрибути'));

    $result = $conn('SELECT atr, color FROM service_ids WHERE ID = '. $SERV_ID);

    $atr = $result[0]['atr'] ?? -1;
    $has_color = $result[0]['color'] == 1 ? 'checked' : '';
    $not_has_color = $result[0]['color'] != 1 ? 'checked' : '';

    $usingColor = array();
    $usingColor['in'] = $conn('SELECT * FROM service_in WHERE service_ID = '
            . $SERV_ID . ' AND color IS NOT NULL LIMIT 1');
    $usingColor['out'] = $conn('SELECT * FROM service_out WHERE service_ID = '
            . $SERV_ID . ' AND color IS NOT NULL LIMIT 1');
    $usingColor['ans'] = (count($usingColor['in']) + count($usingColor['out'])) > 0 ? 'hidden':'';

    $counter = 0;

    foreach ($service_types as $a=>$an){
        $stat_yes = inclAttr($a, $atr) ? 'checked':'';
        $stat_no = inclAttr($a, $atr) ? '':'checked';

        $rnd_id_yes = rand(100000, 999999);
        $rnd_id_no = rand(100000, 999999);

        $using = '';

        switch($a){
            case 16://Чи були витрати по ІД
                $ans = count($conn('SELECT * FROM service_in WHERE service_ID='.$SERV_ID.' LIMIT 1')) == 1;

                if ($ans){
                    $using = 'hidden';
                    $stat_yes = 'checked';
                    $stat_no = '';
                }
                break;
        }

        $fsAttr(new HTEL('fieldset .=radio_fs',[
              1=>$rnd_id_yes,
              $rnd_id_no,
              $a,
              $counter++,
              new HTEL('legend/[0]', $an),
              new HTEL('label for=[1]/так', new HTEL('input *=radio !=[1] ?=atr[[4]] .=attrradio #=[3] [0]', $stat_yes)),
              new HTEL('label for=[2] [0]/ні',[$using, new HTEL('input *=radio !=[2] ?=atr[[4]] .=attrradio #=0 [0]', $stat_no)])
        ]));
    }

    $fsAttr(new HTEL('fieldset .=radio_fs',[
        $usingColor['ans'],
        new HTEL('legend/Колір'),
        new HTEL('label for=color_1/так', new HTEL('input *=radio !=color_1 ?=has_color .=colorradio #=1 [0]', $has_color)),
        new HTEL('label for=color_0 [0]/ні', new HTEL('input *=radio !=color_0 ?=has_color .=colorradio #=0 [0]', $not_has_color))
    ]));

    $only_exp = $atr == 16;

    $formOut($fsAttr);

    if (!$only_exp){
        //Список типів сервісу/кольорів/зображення

        $fsTypes = new HTEL('fieldset .=types_info', new HTEL('legend/Підтипи'));

        $result = $conn('SELECT type_ID, name FROM type_ids WHERE service_ID = '. $SERV_ID . ' ORDER BY type_ID ASC');

        $has_types = true;
        $counter = 1;

        $next_type_id = 2;

        if (count($result) == 0){
            $result[0]['type_ID'] = 1;
            $result[0]['placeholder'] = 'основний тип: ' . $SERV_NAME;
            $has_types = false;
        }
        else{
            foreach ($result as $r){
                if ($next_type_id <= $r['type_ID']) $next_type_id = $r['type_ID'] + 1;
            }

            $next_ind = count($result);

            $result[$next_ind]['type_ID'] = $next_type_id;
            $result[$next_ind]['placeholder'] = 'додати новий тип...';
        }

        $usingTypes = array();

        foreach($result as $row){
            if ($has_color != ''){
                $img_col = new HTEL('div .=img_col', $row['type_ID']);

                foreach($_COLORS as $c){
                    $is_apply = $c->AppleTo($SERV_ID, $row['type_ID']) ? 'checked': '';

                    $rnd_id = rand(100000, 999999);

                    $img_col([
                       new HTEL('label for=[2]/[1]', [1=>$c->NAME, $rnd_id]),
                       new HTEL('input *=checkbox !=[3] ?=type_colors[[0]][] #=[1] [2]', [1=>$c->ID, $is_apply, $rnd_id]),
                       new HTEL("img ?=img_[0] src=[2][3].[0].[1].png onerror=this.src=='[4]' alt",
                       [1=>$c->ID, '/img/kompl/', $SERV_ID, '/img/createz.png']),
                       new HTEL('input *=file .=img_upl ?=[1]-[0]-[2] accept=[3] #',
                       [1=>$SERV_ID, $c->ID, 'image/png'])
                    ]);
                }
            }

            $rnd_id = rand(10000, 99999);
            $canDelete = '';

            $usingTypes['in'] = $conn('SELECT * FROM service_in WHERE service_ID = '
            . $SERV_ID . ' AND type_ID = ' . $row['type_ID'] . ' LIMIT 1');

            $usingTypes['out'] = $conn('SELECT * FROM service_out WHERE service_ID = '
           . $SERV_ID . ' AND type_ID = ' . $row['type_ID'] . ' LIMIT 1');

            $is_using = (count($usingTypes['in']) + count($usingTypes['out']) == 0);

            if ($is_using || !isset($row['name']) || $row['name'] == '' || $row['type_ID'] == 1){
                $canDelete = 'hidden';
            }

            $delete_chb = new HTEL('label for=[0] .=del_type [1]/Видалити', [
                $rnd_id,
                $canDelete,
                new HTEL('input *=checkbox !=[0] ?=remowe_types[] #=[2] [1]', [2=>$row['type_ID']])
            ]);

            $tmp = new HTEL('div .=pid_type', [
                 $row['type_ID'],
                 new HTEL('label/[1].', [1=>$counter++]),
                 new HTEL('input *=text ?=type_names[[0]] #=[1] $=[2]',
                 [1=>$row['name'] ?? '', $row['placeholder'] ?? '']),
                 $delete_chb,
                 $img_col
            ]);

            $fsTypes($tmp);

        }

        $formOut($fsTypes);
    }

    $conn->close();

    $formOut([
            new HTEL('button *=submit !=save_but/ЗБЕРЕГТИ'),
            new HTEL('div !=temp_ans')
    ]);

    echo $formOut;

?>

<script>

    //$(document).ready(function () {

    $('#set_info').submit(function (evt) {
        evt.preventDefault();
   
        var form = $(this).closest("form");;
        var formData = new FormData(form[0]);
        var serviceId = $('#serv').val();
        var new_name = $('#new_name').val();

        formData.append('service_id', serviceId);
        formData.append('rename', new_name);

        $.ajax({
            url: "blok/set/save_set.php",
            type: "POST",
            processData: false,
            contentType: false,
            dataType: 'html',
            data:  formData,
            success: function(data) {
                   $('#temp_ans').html(data);
            }
        });
    });

    $('.colorradio').on('change', function () {
        var has_col = $(this).val();

        if (has_col == 0) {
            $('.img_col').toggleClass('hide_color');
        } else {
            $('.img_col').removeClass('hide_color');
        }
    });
</script>

<style>

.hide_color{
      display: none;
}

.types_info{
   padding: 20px;
}

.pid_type input[type=text]{
   min-width: 400px;
}

.img_col{
   margin-left: 50px;
   padding: 10px 0;
   display: grid;
   justify-items: start;
   align-items: center;
   grid-template-columns: 100px 20px 100px auto;
   grid-gap: 10px;
}

    .img_col img{
       border: 5px double blue;
       padding: 4px;
       border-radius: 4px;
       height: 64px;
       width: 64px;
    }

.pid_type button{
     height: 100%;
     width: 40px;
     margin: 0;
     padding: 0;
     background: none;
     border: 2px double green;
}

#set_info{
   margin-top: 20px;
   padding-top: 20px;
   border-top: 5px dotted red; 
}

.del_type{
    color: red;
    font-weight: bold;
    border: 2px dotted red;
}

.radio_fs{
   display: inline-flex;
   justify-content: center;
   align-items:center;
   width: auto;
   margin: 5px 10px;
   border: 4px solid green;
   border-radius: 15px;
   background-color: white;
   color: blue;
   padding: 4px 8px;
}

    .radio_fs legend{
        background-color: green;
        padding: 2px 5px;
        border-radius: 10px;
        color: yellow;
    }

    .radio_fs label{
        padding: 3px;
        color: darkgrey;
        border-top: 5px solid green;
        padding: 2px;
        min-width: 35px;
        text-align: center;
        font-style: italic;
    }

    .radio_fs label:last-child{
        border-top: 5px solid red;
    }

    .radio_fs label:has(>input:checked){
        background-color: green;
        color: white;
        font-style: normal;
    }

    .radio_fs label:last-child:has(>input:checked){
        background-color: red;
    }

    .radio_fs input[type=radio]{
        display: none;
    }

</style>