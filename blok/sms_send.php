<?php

$TEXT = trim($_GET['mes']);

if (empty($TEXT)){
    echo 'Повідомлення пусте!';
    exit;
}

$NUMBER = explode(',', $_GET['tel']);

//echo SEND_ONE_SMS($NUMBER, $TEXT);

echo SEND_MORE_SMS($NUMBER, $TEXT);

function SEND_ONE_SMS($number, $mes)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://a2p.vodafone.ua/communication-event/api/communicationManagement/v2/communicationMessage/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{ \"content\": \"" . $mes .
    "\", \"type\": \"SMS\", \"receiver\": [ { \"id\": 0, \"phoneNumber\": " . $number .
    " } ], \"sender\": { \"id\": \"SholomPro\" }, \"characteristic\": [ { \"name\": \"DISTRIBUTION.ID\", \"value\": 5434146 }, { \"name\": \"VALIDITY.PERIOD\", \"value\": \"000000000100000R\" } ] }");

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: */*';
    $headers[] = 'Authorization: bearer ' . GET_MARKER();
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
    "],\"sender\": {\"id\": \"SholomPro\"},\"characteristic\": [{\"name\": \"DISTRIBUTION.ID\",\"value\": 5434146},
    {\"name\": \"VALIDITY.PERIOD\",\"value\": \"000000000100000R\"}]}");

    $headers = array();
    $headers[] = 'Accept: */*';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: bearer ' . GET_MARKER();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = json_decode(curl_exec($ch));

    if (curl_errno($ch)) {
        return 'ПОМИЛКА:' . curl_error($ch);
    }

    curl_close($ch);

    $ans = 'СТАТУС:';

    foreach ($response as $a){
        $ans .= "<br>" . explode('-', $a->id)[2] . ' => ' . answerInfo($a->status);
    }

    return $ans;
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

    curl_close($ch);

    $ans = json_decode($response);

    return $ans->access_token;
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
