<?php

$ID = $_GET['id'] ?? null;
$TYPE = $_GET['type'] ?? 1;

if ($ID === null) exit;

require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

$OUT_ARR = array();

$conn = new SQLconn();

$query = 'SELECT date_in date, service_ids.NAME name, type_ids.name type, colors.color, count, costs, comm, redaktor FROM service_in
LEFT JOIN colors ON service_in.color=colors.ID
JOIN service_ids ON service_in.service_ID=service_ids.ID
LEFT JOIN type_ids ON service_in.service_ID=type_ids.service_ID AND service_in.type_ID=type_ids.type_ID
WHERE service_in.service_ID = '. $ID . ' AND service_in.type_ID = '. $TYPE . '
ORDER BY date_in DESC';

$result = $conn($query);

$counter = 0;
$old_date = null;

foreach ($result as $row){

    if ($old_date !== $row['date']){
            $counter = 0;
            $old_date = $row['date'];
    }

    $OUT_ARR[$row['date']][$counter]['name'] = $row['name'] . ($row['type'] !== null ? ' (' . $row['type'] . ')' : '');
    $OUT_ARR[$row['date']][$counter]['color'] = $row['color'] ?? '- без кольору -';
    $OUT_ARR[$row['date']][$counter]['count'] = $row['count'];
    $OUT_ARR[$row['date']][$counter]['costs'] = $row['costs'] * -1;
    $OUT_ARR[$row['date']][$counter]['comm'] = $row['comm'] ?? '';
    $OUT_ARR[$row['date']][$counter]['redaktor'] = $row['redaktor'];

    $counter++;
}

$query = 'SELECT date_out date, service_ids.NAME name, type_ids.name type, colors.color, count, costs, sholom_num, sold_number, redaktor FROM service_out
JOIN client_info ON service_out.ID=client_info.ID
LEFT JOIN colors ON service_out.color=colors.ID
JOIN service_ids ON service_out.service_ID=service_ids.ID
LEFT JOIN type_ids ON service_out.service_ID=type_ids.service_ID AND service_out.type_ID=type_ids.type_ID
WHERE date_out IS NOT NULL AND service_out.service_ID = ' . $ID . ' AND service_out.type_ID = ' . $TYPE . '
ORDER BY date DESC';

$result = $conn($query);

foreach ($result as $row) {

    $OUT_ARR[$row['date']][] = [
    'name'=> $row['name'] . ($row['type'] !== null ? ' (' . $row['type'] . ')' : ''),
    'color'=> $row['color'] ?? '- без кольору -',
    'count'=> $row['count'] * -1,
    'costs'=> $row['costs'],
    'comm'=> '№ ' . $row['sholom_num'] ?? '' . $row['sold_number'] ?? '',
    'redaktor'=> $row['redaktor']
    ];
}

krsort($OUT_ARR);

//var_dump($OUT_ARR);

$conn->close();

$tbody = new HTEL('tbody &=text-align:center;');

$caption = null;

foreach ($OUT_ARR as $date=>$arr){

    if (is_null($caption)) $caption = new HTEL('caption/[0]', $arr[0]['name']);

    $th = new HTEL('tr', new HTEL('th &=text-align:left;padding-left:10%; colspan=5/[0]', dateToNorm($date)));
    $tbody($th);

    foreach ($arr as $v){
        $tr = new HTEL('tr',[
            new HTEL('td/[0]', $v['comm']),
            new HTEL('td/[0]', $v['color']),
            new HTEL('td/[0]', $v['count']),
            new HTEL('td/[0]', CostOut($v['costs'])),
            new HTEL('td/[0]', $v['redaktor'])
        ]);

        $tbody($tr);
    }
}

$table = new HTEL('table', [$caption, $tbody]);

echo $table;

?>