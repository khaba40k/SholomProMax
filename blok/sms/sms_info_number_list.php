<?php
require $_SERVER['DOCUMENT_ROOT'] . "/blok/conn_local.php";
require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

session_start();

$token = $_SESSION['token'] ?? GET_MARKER();

$ID = $_POST['id'];

$cont = new HTEL('div .=list');

$tbody = new HTEL('tbody');

$query = 'select * from message_info WHERE mes_id =' . $ID;

    $result = mysqli_query($link, $query);

    $counter = 1;

    foreach ($result as $row){
        $tbody(new HTEL('tr', [
            new HTEL('td width=5%/[0].', $counter++),
            new HTEL('td width=20%/[0]', $row['tel']),
            new HTEL('td/[0]', get_stat($token, $row['id'])),
            new HTEL('td/[0]', $row['receiver'])
        ]));
    }

$cont(new HTEL('table .=smsresult', $tbody));

echo $cont;

function get_stat($marker, $mes_id):string{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://a2p.vodafone.ua/communication-event/api/communicationManagement/v2/communicationMessage/status?messageId='.$mes_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'Authorization: bearer ' . $marker;
    $headers[] = 'Accept: */*';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = json_decode( curl_exec($ch));

    curl_close($ch);

    return answerInfo($result->status);
}

function GET_MARKER()
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://a2p.vodafone.ua/uaa/oauth/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=password&username=380985992689&password=Sho000lom_");

    $headers = array();
    $headers[] = 'Authorization: Basic aW50ZXJuYWw6aW50ZXJuYWw=';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    curl_close($ch);

    $ans = json_decode($result);

    $_SESSION['token'] = $ans->access_token;

    return $ans->access_token;
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

    return $ARR[$word] ?? $word;
}
?>
