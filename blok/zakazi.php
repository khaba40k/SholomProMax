<script>

    $('.zakazi_but').on('click', function () {
        $('.zakazi_but').not(this).removeClass('clicked_but')
        $(this).toggleClass('clicked_but');
    });

    //$('#activ_z_but').on('click', function () { list_Z('new'); });
    //$('#inwork_z_but').on('click', function () { list_Z('inwork'); });
    //$('#archiv_z_but').on('click', function () { list_Z('archiv'); });
    //$("#create_z_but").on("click", newZ);
    //$("#sold_z_but").on("click", newZsold);

    function newZ() {

        $.ajax({
            url: 'blok/new_Z.php',
            dataType: 'html',
            success: function (responce) {
                $('#zakaz_workplace').html(responce);
            }
        });
    };

    function newZsold() {

        $.ajax({
            url: 'blok/new_Z.php',
            method: 'GET',
            dataType: 'html',
            data: 'type=sold',
            success: function (data) {
                $('#zakaz_workplace').html(data);
            }
        });
    };

    function list_Z($status = 'new') {
          $.ajax({
                url: 'blok/active_z.php',
                method: 'GET',
              dataType: 'html',
              data: 'type=' + $status,
                success: function (data) {
                    $('#zakaz_workplace').html(data);
                }
          });
    };

</script>

<?php
$_MENU = $_GET['menu_type'] ?? 'list';

//var_dump($_GET);
//exit;

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$div = new HTEL('div .=zakaz_menu');

switch ($_MENU){
    case 'list':
        $style = _style(3);
        $div([
            new HTEL("button *=button !=activ_z_but .=zakazi_but[0] #=click onclick=location.href=='work?page==[2]' [1]/ЗАРЕЄСТРОВАНІ",
            [$_GET['page'] == 'new' ? ' clicked_but':'', $style, 'new']),
            new HTEL("button *=button !=inwork_z_but .=zakazi_but[0] #=click onclick=location.href=='work?page==[2]' [1]/В РОБОТІ",
            [$_GET['page'] == 'inwork' ? ' clicked_but' : '', $style, 'inwork']),
            new HTEL("button *=button !=archiv_z_but .=zakazi_but[0] #=click onclick=location.href=='work?page==[2]' [1]/ВИКОНАНІ (АРХІВ)",
            [$_GET['page'] == 'archiv' ? ' clicked_but' : '', $style, 'archiv'])
        ]);
        echo new HTEL('script/list_Z(`[0]`);', $_GET['page']);
        break;
    case 'create':
        $style = _style(2);
        $div([
            new HTEL("button *=button !=create_z_but .=zakazi_but[0] #=click onclick=location.href=='work?page==[2]' [1]/ПЕРЕОБЛАДНАННЯ",
            [$_GET['page'] == 'def' ? ' clicked_but' : '', $style, 'newZdef']),
            new HTEL("button *=button !=sold_z_but .=zakazi_but[0] #=click onclick=location.href=='work?page==[2]' [1]/ПРОДАЖ",
            [$_GET['page'] == 'sold' ? ' clicked_but' : '', $style, 'newZsold'])
        ]);

        if (isset($_GET['page']) && $_GET['page'] != 'def'){
            echo new HTEL('script/newZsold();');
        }
        else{
            echo new HTEL('script/newZ();');
        }
        break;
}

echo $div(new HTEL('div  !=zakaz_workplace'));

function _style($countBut = 2):string{
    $out = 'style="';
    //40% - 8%

    $width = intdiv(80, $countBut);
    $marg_left = intdiv($width, 5);

    $out .= 'width:' . $width . '%;';
    $out .= 'margin-left:' . $marg_left . '%;';

    return $out . '"';
}

?>
