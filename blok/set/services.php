<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    $conn = new SQLconn();

    $service_list = $conn('select ID, NAME from service_ids WHERE atr >= 0 ORDER BY `order`');

    $container = new HTEL('div .=set_serv_cont');

    $select = new HTEL('select !=serv .=serv', new HTEL('option # [s] [d]/послуга/компл...'));

    foreach($service_list as $serv){
        $select(new HTEL('option #=[0]/[1]', [$serv['ID'],$serv['NAME']]));
    }

    $container([$select, new HTEL('div !=workset')]);

    $conn->close();

    echo $container;
?>

<script>

    $('.set_serv_cont').on('change', '#serv', function () {
        var _id = $(this).val();

        $.ajax({
            url: 'blok/set/serv_one_info.php',
            method: 'GET',
            dataType: 'html',
            data: 'ID=' + _id,
            success: function (data) {
                $('#workset').html(data);
            }
        });

    });

</script>

<style>

.set_serv_cont{
margin: 10px;
padding: 20px;
border: 3px solid blue;
border-radius: 40px 0 0 0;
background-color: rgb(255, 244, 0 , 0.55);
}

    .set_serv_cont > select{
        background-color: blue;
        color: yellow;
        font-weight: bold;
        font-size: 120%;
    }

</style>