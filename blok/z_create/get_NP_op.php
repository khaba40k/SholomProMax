<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Delivery/NovaPoshtaApi2.php';
use LisDev\Delivery\NovaPoshtaApi2;
$np = new NovaPoshtaApi2('90bb2c77ec2e2fba67348f82547f2f0a', 'ua', true, 'file_get_content'); //'file_get_content'

$FIND = $_GET['find'] ?? null;
$REF = $_GET['ref'] ?? null;
$NUMBER = $_GET['number'] ?? 0;

if (!empty($FIND)){

    $FIND = str_replace(' .', '.', $FIND);
    $FIND = str_replace('.', '. ', $FIND);
    $FIND = str_replace('(', ' (', $FIND);
    $FIND = str_replace(')', ') ', $FIND);

    $spl = explode(' ', $FIND);
    $FIRST_FIND = $FIND;

    foreach ($spl as $f){
         if (!mb_stripos($f, '.') && !mb_stripos($f, '(') && !empty(trim($f)) && strlen($f) > 2){
            $FIND = $f;
            break;
         }
    }

    $cities = $np->getCities(0, $FIND);

    foreach ($cities['data'] as $c){
        echo '<option data-value="' . $c['Ref'] . '">' . STD_ABBR($c['SettlementTypeDescription']) .
            EXPLAIN($c['Description'], $c['AreaDescription']) . '</option>';
    }

} else if(!empty($REF)){
    $vidd = $np->getWarehouses($REF);

    echo '<option value="" disabled selected>оберіть відділення...</option>';

    foreach ($vidd['data'] as $v) {
        if ($v['CategoryOfWarehouse'] !== 'Postomat'){
            echo '<option value="' . $v['Number'] . '" '.selectedW($v['Number'], $NUMBER) . '>' . $v['Description'] . '</option>';
        }
    }

}

function selectedW($in, $need):string{
    if ($in == $need)
        return 'selected';

    return '';
}

function STD_ABBR($in) : string{
    $out = '';

    $split = explode(' ', $in);

    foreach ($split as $s){
        $out .= mb_substr( $s, 0, 1) . '.';
    }

    return $out . ' ';
}

function EXPLAIN($if, $set) : string{
    $out = $if;

    if (strpos($if, '(') === false){
        $out .= ' (' . $set . ' обл.)';
    }
    else if (strpos($if, 'обл.') === false){
        $out = str_replace('(', '(' . $set . ' обл., ', $if);
    }

    return $out;
}

?>