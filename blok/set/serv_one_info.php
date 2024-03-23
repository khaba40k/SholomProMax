<?php
    $SERV_ID = $_GET['ID'] ?? null;

    if ($SERV_ID === null || $SERV_ID == "") exit;

    require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    $service_types = [
        1=>'замовлення',
        2=>'замовлення (з адмінки)',
        4=>'продаж',
        8=>'продаж (з адмінки)',
        16=>'витрати'
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

    $divAttr = new HTEL('div !=attr_info .=attr_info');

    $result = $conn('SELECT atr FROM service_ids WHERE ID = '. $SERV_ID);

    $atr = $result[0]['atr'] ?? -1;

    foreach ($service_types as $a=>$an){
        $stat = inclAttr($a, $atr) ? 'checked':'';
        $divAttr(new HTEL('input *=checkbox #=[0] ?=atr[] [2]/[1]', [$a, $an, $stat]));
    }

    $only_exp = $atr == 16;

    $formOut($divAttr);

    if ($only_exp){
        echo $formOut;
        exit;
    }

    //Список типів сервісу/кольорів/зображення

    $fsTypes = new HTEL('fieldset .=types_info', new HTEL('legend/Підтипи'));

    $result = $conn('SELECT type_ID, name FROM type_ids WHERE service_ID = '. $SERV_ID);

    $has_types = true;
    $counter = 1;

    if (count($result) == 0){
        $result[0]['type_ID'] = 1;
        $result[0]['name'] = '';
        $has_types = false;
    }

    foreach($result as $row){
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

        $tmp = new HTEL('div .=pid_type', [
             $row['type_ID'],
             new HTEL('label/[1].', [1=>$counter++]),
             new HTEL('input *=text ?=type_names[[0]] #=[1]', [1=>$row['name']]),
             new HTEL('button *=button .=deltype #=[0]/X'),
             $img_col
        ]);

        $fsTypes($tmp);
    }

    if ($has_types){
        $tmp = new HTEL('div .=pid_type', [
             $counter++,
             new HTEL('label/[0].'),
             new HTEL('button *=button !=addtype/+')
        ]);

        $fsTypes($tmp);
    }

    $conn->close();

    $formOut([
        $fsTypes,
        new HTEL('button *=submit !=save_but/ЗБЕРЕГТИ'),
        new HTEL('div !=temp_ans')
    ]);

    echo $formOut;


?>

<script>

    $(document).ready(function () {


         $('#set_info').submit(function (evt) {
             evt.preventDefault();
        
             var form = $(this).closest("form");;
             var formData = new FormData(form[0]);
             var serviceId = $('#serv').val();
             formData.append('ID', serviceId);
        
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

    });

</script>

<style>

.types_info{
   padding: 20px;
}

.pid_type{

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

</style>