<?php

class QUERY_MASTER
{
    //    0    'Тип заявки',
    //    1    'Статус заявки',
    //    2    'Період створення',
    //    3    'Період відправки',
    //    4    'Включає...',
    //    5    'Не включає...'

    //  'filter_0' => string '0' (length=1) 0-переобл 1-продаж
    //  'filter_1' => string '1' (length=1) 0-нові 1-в роб 2-архів
    //  'filter_2_ot' => string '2024-01-01' (length=10)
    //  'filter_2_do' => string '2024-01-14' (length=10)
    //  'filter_3_ot' => string '2024-01-03' (length=10)
    //  'filter_3_do' => string '2024-01-21' (length=10)
    //  'filter_4' => string '15' (length=2)
    //  'filter_5' => string '5' (length=1)

    private $arr_in;

    function __construct(array $arr)
    {
         foreach($arr as $k=>$f){
            $ff = substr($k, 12);
            $this->arr_in[$ff][] = $f;
         }
    }

    function GET_ARR():array{
        return $this->arr_in;
    }

    function GET_POSITIVE(): string
    {
        $ans = 'SELECT DISTINCT client_info.ID FROM service_out JOIN client_info ON service_out.ID=client_info.ID WHERE #';

        $accepted = false;

        foreach ($this->arr_in as $k => $f) {

            switch ($k) {
                case '0':
                    $ans .= ' AND (#';
                    $temp = '';

                    foreach ($f as $ff) {
                        $temp .= ' OR (' . ($ff == 0 ? 'sholom_num' : 'sold_number') . ' IS NOT NULL)';
                    }

                    $ans = str_replace('# OR', '', $ans . $temp . ')');

                    $accepted = true;
                    break;
                case '1':
                    $ans .= ' AND (#';
                    $temp = '';
                    foreach ($f as $ff) {
                        switch ($ff) {
                            case 0:
                                $temp .= ' OR ((TTN_IN IS NULL AND sold_number IS NULL) AND date_out IS NULL)';
                                break;
                            case 1:
                                $temp .= ' OR (TTN_IN IS NOT NULL AND date_out IS NULL)';
                                break;
                            case 2:
                                $temp .= ' OR (TTN_OUT IS NOT NULL AND date_out IS NOT NULL)';
                                break;
                        }
                    }

                    $ans = str_replace('# OR', '', $ans . $temp . ')');

                    $accepted = true;
                    break;
                case '2_ot':
                    $t = $this->GET_DATE($f, false);
                    if (!empty($t)){
                        $ans .= ' AND date_in >= "' . $t . '"';
                    }
                    $accepted = true;
                    break;
                case '2_do':
                    $t = $this->GET_DATE($f);
                    if (!empty($t)) {
                        $ans .= ' AND date_in <= "' . $t . '"';
                    }
                    $accepted = true;
                    break;
                case '3_ot':
                    $t = $this->GET_DATE($f, false);
                    if (!empty($t)) {
                        $ans .= ' AND date_out >= "' . $t . '"';
                    }
                    $accepted = true;
                    break;
                case '3_do':
                    $t = $this->GET_DATE($f);
                    if (!empty($t)) {
                        $ans .= ' AND date_out <= "' . $t . '"';
                    }
                    $accepted = true;
                    break;
                case '4';
                    $ans .= ' AND service_ID IN (';
                    $temp = '';

                    foreach($f as $ff){
                        $temp .= $ff . ' ';
                    }

                    $ans .= str_replace(' ', ',', trim($temp)) . ')';
                    $accepted = true;
                    break;

            }
        }

        return $accepted ? str_replace('# AND', '', $ans) : str_replace('WHERE #', '', $ans);
    }

    function GET_NEGATIVE(): string{
        $ans = 'SELECT DISTINCT client_info.ID FROM service_out JOIN client_info ON service_out.ID=client_info.ID WHERE #';
        $accepted = false;

        foreach ($this->arr_in as $k => $f) {

            switch ($k) {
                case '5';
                    $ans .= ' AND service_ID IN (';
                    $temp = '';

                    foreach ($f as $ff) {
                        $temp .= $ff . ' ';
                    }

                    $ans .= str_replace(' ', ',', trim($temp)) . ')';
                    $accepted = true;
                    break;

            }
        }

        return $accepted ? str_replace('# AND', '', $ans) : '';
    }

    private function GET_DATE($in_arr, $min = true):string
    {
        $out = null;

        foreach($in_arr as $t){
            if (!empty($t)){
                $out = strtotime($t);
                break;
            }
        }

        if (empty($out))
            return '';

        foreach($in_arr as $str_date){
            if (!empty($str_date)){
                $d = strtotime($str_date);

                if ($min){
                    if ($d < $out)
                        $out = $d;
                }else{
                    if ($d > $out)
                        $out = $d;
                }
            }
        }

        return date('Y-m-d', $out);
    }

    function __toString()
    {
        return '';
    }

}

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$conn = new SQLconn();

$n = new QUERY_MASTER($_POST);

//negative arr

$n_ids = array();

$query = $n->GET_NEGATIVE();

if (!empty($query)){
    $result = $conn($query);

    foreach ($result as $row) {
        $n_ids[] = $row['ID'];
    }
}
//positive arr

$p_ids = array();

$query = $n->GET_POSITIVE();

if (!empty($query)) {

    $result = $conn($query);

    foreach ($result as $row) {
        if (!in_array($row['ID'], $n_ids))
            $p_ids[] = $row['ID'];
    }
}

if (count($p_ids) == 0) {
    echo 'НЕ ЗНАЙДЕНО!!!';
    $conn->close();
    exit;
}

$tbody = new HTEL('tbody');

$counter = 0;

$query = 'SELECT phone, client_name, sholom_num, sold_number FROM client_info WHERE ID IN (' . implode(',', array_map('intval', $p_ids)) . ')';

$result = $conn($query);

foreach ($result as $row) {

    $phone_number = getCorrectPhone($row['phone']);

    if (!empty($phone_number)){
        $tr = new HTEL('tr .=client_row #=[0]', $counter);

        $tr(new HTEL('td', new HTEL('input *=checkbox ?=[0] #=[1] [c]', [1=> $phone_number])));

        if (($row['sholom_num'] ?? $row['sold_number']) == 0) {
            $tr(new HTEL('td/-'));
        } else {
            $tr(new HTEL('td/№ [0]', $row['sholom_num'] ?? $row['sold_number']));
        }

        $tr(new HTEL('td/[0]',  $phone_number));
        $tr(new HTEL('td/[0]', $row['client_name']));

        $tbody($tr);
        $counter++;
    }
}

$conn->close();

$table = new HTEL('table', [new HTEL('caption /ЗАПИСIВ: [0]', count($p_ids)) ,$tbody]);

echo $table;

function getCorrectPhone(string $in, $kodKr = true)
{
    $out = '';

    $split = str_split($in);

    foreach ($split as $s) {
        if (is_numeric($s)) {
            $out .= $s;
        }
    }

    if (strlen($out) < 9)
        return '';

    $out = mb_substr($out, -9);

    if ($kodKr)
        $out = '+380' . $out;

    return $out;
}

?>