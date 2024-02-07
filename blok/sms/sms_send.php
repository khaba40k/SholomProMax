<?php

$TEXT = trim($_POST['mes']);

$NUMBER = array();

if (isset($_POST['tellist'])){
    $input_tel = json_decode($_POST['tellist']);

    if (count($input_tel) == 0) {
        echo 'Номерів не вказано!';
        exit;
    }

    foreach ($input_tel as $t) {
        $NUMBER[] = $t->value;
    }
}else{
    $NUMBER[] = $_POST['tel'] ?? '';
}

//echo SEND_ONE_SMS($NUMBER, $TEXT);

//$NUMBER[] = '+380631546860';

echo SEND_MORE_SMS($NUMBER, $TEXT);

function SEND_ONE_SMS($number, $mes)
{
    $marker = GET_MARKER();

    if (substr($marker, 0, 5) == 'Error') {
        return $marker;
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://a2p.vodafone.ua/communication-event/api/communicationManagement/v2/communicationMessage/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{ \"content\": \"" . $mes .
    "\", \"type\": \"SMS\", \"receiver\": [ { \"id\": 0, \"phoneNumber\": " . $number .
    " } ], \"sender\": { \"id\": \"HelmetUA\" }, \"characteristic\": [ { \"name\": \"DISTRIBUTION.ID\", \"value\": 5486092 }, { \"name\": \"VALIDITY.PERIOD\", \"value\": \"000001000000000R\" } ] }");

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: */*';
    $headers[] = 'Authorization: bearer ' . $marker;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = json_decode(curl_exec($ch));

    if (curl_errno($ch)) {
        return 'ПОМИЛКА:' . curl_error($ch);
    }

    curl_close($ch);

    $ans = 'СТАТУС:';

    foreach ($response as $a){
        $ans .= "<br>" . explode('-',$a->id)[2] . ' => ' . answerInfo($a->status);
    }

    return $ans;
}

function SEND_MORE_SMS(array $NUMBERS, $mes){

    $marker = GET_MARKER();

    if (substr($marker, 0, 5) == 'Error') {
        return $marker;
    }

    $ch = curl_init();

    $AB = "";

    $counter = 0;

    foreach ($NUMBERS as $N){
        $p = getCorrectPhone($N);
        if (!empty($p)){
            $AB .= "{\"id\": " . $counter++ . ",\"phoneNumber\": ".$p."},";
        }
    }

    if ($counter == 0) return 'НОМЕР НЕ КОРЕКТНИЙ!';

    $AB =  substr($AB, 0, strlen($AB) - 1);

    curl_setopt($ch, CURLOPT_URL, 'https://a2p.vodafone.ua/communication-event/api/communicationManagement/v2/communicationMessage/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
    "{\"content\": \"". $mes ."\",\"type\": \"SMS\",\"receiver\": [". $AB .
    "],\"sender\": {\"id\": \"HelmetUA\"},\"characteristic\": [{\"name\": \"DISTRIBUTION.ID\",\"value\": 5486092},
    {\"name\": \"VALIDITY.PERIOD\",\"value\": \"000001000000000R\"}]}");

    $headers = array();
    $headers[] = 'Accept: */*';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: bearer ' . $marker;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = json_decode(curl_exec($ch));

    if (curl_errno($ch)) {
        return 'ПОМИЛКА:' . curl_error($ch);
    }

    //var_dump($response);
    //echo '<br><br>';

    curl_close($ch);

    $ans = 'СТАТУС:';

    $arr_ans = array();

    foreach ($response as $a){
        $arr_ans[] = $a->id;

        $ans .= "<br>" . explode('-', $a->id)[2] . ' => ' . answerInfo($a->status);
    }

    RecOnBase($arr_ans, $mes);

    return $ans;
}

function RecOnBase(array $arr, string $mes){

    //ЗАПИС В БАЗУ ДАНИХ

}

function GET_MARKER():string{
    $url = 'https://a2p.vodafone.ua/uaa/oauth/token?grant_type=password';

    $ch = curl_init($url);

    $data = [
        'username' => '380953410218',
        'password' => 'RgRhh7L%Ff'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'authorization: Basic aW50ZXJuYWw6aW50ZXJuYWw='
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Error:' . curl_error($ch);
    }

    curl_close($ch);

    $ans = json_decode($response);

    var_dump($response);

    if (!is_null($ans->access_token)){
        return $ans->access_token;
    }else{
        return RefreshMarker($ans->refresh_token);
    }
}

function RefreshMarker($refr_tok):string{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://a2p.vodafone.ua/uaa/oauth/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=refresh_token&refresh_token=" . $refr_tok);

    $headers = array();
    $headers[] = 'Authorization: Basic aW50ZXJuYWw6aW50ZXJuYWw=';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Error:' . curl_error($ch);
    }

    curl_close($ch);

    $ans = json_decode($result);

    return $ans->access_token ?? 'Error: Помилка при відправленні, зверніться до оператора! (Перевірте стан рахунку)';
}

function getCorrectPhone(string $in, $kodKr = true){
    $out = '';

    $split = str_split($in);

    foreach($split as $s){
        if (is_numeric($s)){
            $out .= $s;
        }
    }

    if (strlen($out) < 9)
        return '';

    $out = mb_substr($out, -9);

    if ($kodKr)
        $out = '380' . $out;

    return $out;
}

function answerInfo($word):string{
    $ARR = [
         'UNACCEPTED'=>'Платформа не прийняла повідомлення',
         'ACCEPTED'=>'Повідомлення прийнято платформою',
         'UNDELIVERABLE'=>'Доставка на вказаний номер неможлива',
         'PENDING'=>'Повідомлення було доставлено в мережу одержувача, але не одержувачу. Ймовірно, - вимкнено телефон.',
         'DELIVERED'=>'Доставлено одержувачу',
         'EXPIRED'=>'Термін дії повідомлення минув',
         'REJECTED'=>'Повідомлення було відхилено мережею одержувача'
    ];

    return $ARR[$word] ?? 'НЕВІДОМО';
}
?>
