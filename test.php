<!DOCTYPE HTML>

<html>

<head>
    <title>SholomProMax</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
</head>

<body>

    <?php
    //require("blok/conn_local.php");

    require "blok/header.php";

    require $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";

    //$in = 'м. Миколаївка (Донецька обл., Слов’янська міська рада), № 1';

    //echo new HTEL('div .=NP_SELECTOR', [
    //    new HTEL('div', [
    //        new HTEL('input *=text !=np_input list=list_np ?=rek_out $=населений+пункт [r] #=[0]', $in),
    //        new HTEL('select !=np_sel_cit'),
    //        new HTEL('select !=np_sel_vid'),
    //        new HTEL('datalist !=list_np')
    //    ])
    //]);

    $per = _getPeriod();

    echo $per[0] . '<br>';
    echo $per[1];

    function _getPeriod($in = null): array //in = ~ 10.2023 or null
    {
        $out = array();

        if (!is_null($in)) {
            $spl = explode('.', $in);

            $month = $spl[0];
            $year = $spl[1];

            $out[0] = '"' . $year . '-' . $month . '-1"';

            if ($month < 12) {
                $month++;
            } else {
                $month = 1;
                $year++;
            }

            $out[1] = '"' . $year . '-' . $month . '-1"';
        } else {
            $now = date("Y-m-1");

            $out[0] = '"' . $now . '"';

            $month = date('m');

            $year = date('Y');

            if ($month < 12) {
                $month++;
            } else {
                $month = 1;
                $year++;
            }

            $out[1] = '"' . $year . '-' . $month . '-1"';
        }

        return $out;
    }


    ?>

</body>

</html>

<script>

    //$(document).ready(function () {

    //    //NOVA POSHTA

    //var NP_INP = document.getElementById('np_input');
    //var LIST = document.getElementById('list_np');
    //var SEL_C = document.getElementById('np_sel_cit');
    //var SEL_V = document.getElementById('np_sel_vid');

    //var WriteDone = true;

    //NP_INP.addEventListener('input',
    //    function () {
    //        WriteDone = false;
    //        window.setTimeout(function () { WriteDone = true; }, 300);
    //        initInput($(this).val());
    //    });

    //inputHundler(NP_INP.value);

    //function initInput($val) {
    //    if (WriteDone) {
    //        inputHundler($val);
    //    }
    //    else {
    //        window.setTimeout(function () { initInput($val); }, 200);
    //    }
    //};

    //SEL_C.addEventListener('change', function () {
    //    setViddily($(this).val());
    //});

    //SEL_V.addEventListener('change', function () {
    //    NP_INP.value = $('#np_sel_cit option:selected').html() + ', № ' + $(this).val();
    //});

    //function inputHundler(val) {

    //    if (!WriteDone) return false;

    //    $.ajax({
    //        url: 'blok/get_NP_op.php',
    //        method: 'GET',
    //        dataType: 'html',
    //        data: 'find=' + val,
    //        success: function (data) {
    //            LIST.innerHTML = data;
    //            window.setTimeout(changeHundler, 200);
    //        }
    //    });
    //};

    //function changeHundler() {
    //    $('#np_sel_cit').html('');
    //    $('#np_sel_vid').html('');

    //    var indexLen = 0;
    //    var lastLen = 0;
    //    var setVidVal = null;

    //    $('#list_np option').each(function (i, a) {
    //        indexLen = SearchSmart(a.innerText, NP_INP.value);

    //        if (indexLen > 0) {

    //            if (lastLen < indexLen) {
    //                $('#np_sel_cit').append('<option value="' + a.attributes[0].value + '" selected>' + a.innerText + '</option>');

    //                setVidVal = a.attributes[0].value;
    //            }
    //            else {
    //                $('#np_sel_cit').append('<option value="' + a.attributes[0].value + '">' + a.innerText + '</option>');
    //            }

    //            lastLen = indexLen;
    //        }
    //    });

    //    if (setVidVal !== null)
    //        setViddily(setVidVal, getNum(NP_INP.value));
    //}

    //function getNum(str) {
    //    var split = str.split(' ');
    //    var OUT = 0;

    //    for (var ii = split.length - 1; ii >= 0; ii--) {
    //        OUT = getNumFromStr(split[ii]);
    //        if (OUT != '') break;
    //    }

    //    return OUT;
    //}

    //    function getNumFromStr(str) {
    //        var out = '';

    //        for (var i = str.length - 1; i >= 0; i--) {
    //            if ($.isNumeric(str.charAt(i))) {
    //                out = str.charAt(i).toString() + out.toString();
    //            }
    //            else if (out != '') {
    //                break;
    //            }
    //        }

    //        return out;
    //    }

    //function setViddily(ref, prior = 0) {
    //    $.ajax({
    //        url: 'blok/get_NP_op.php',
    //        method: 'GET',
    //        dataType: 'html',
    //        data: 'ref=' + ref + '&number=' + prior,
    //        success: function (data) {
    //            $('#np_sel_vid').html(data);
    //        }
    //    });
    //}

    //function SearchSmart(instr, find) {
    //    find = find.toLowerCase();
    //    instr = instr.toLowerCase();
    //    var OUT = 0;

    //    var arr = find.split(')');

    //    if (instr.indexOf(arr[0]) > -1) {
    //        OUT = arr[0].length;
    //    }

    //    if (OUT == 0) {
    //        arr = find.split(' ');
    //        arr.forEach(function callback(currentValue) {
    //            if (currentValue.length > 4 && currentValue.indexOf('.') == -1 &&
    //                instr.indexOf(currentValue) == 0) {
    //                if (OUT < currentValue.length) OUT = currentValue.length;
    //            }
    //        });
    //    }

    //    return OUT;
    //}

    //});

</script>


<!--getAreas() array (size=4)
          'Ref' => string '71508129-9b87-11de-822f-000c2965ae0e' (length=36)
          'AreasCenter' => string 'db5c88de-391c-11dd-90d9-001a92567626' (length=36)
          'DescriptionRu' => string 'Винницкая' (length=18)
          'Description' => string 'Вінницька' (length=18)-->

<!-- getCities() 16 => 
        array (size=20)
          'Description' => string 'Агрономічне' (length=22)
          'DescriptionRu' => string 'Агрономичное' (length=24)
          'Ref' => string 'ebc0eda9-93ec-11e3-b441-0050568002cf' (length=36)
          'Delivery1' => string '1' (length=1)
          'Delivery2' => string '1' (length=1)
          'Delivery3' => string '1' (length=1)
          'Delivery4' => string '1' (length=1)
          'Delivery5' => string '1' (length=1)
          'Delivery6' => string '1' (length=1)
          'Delivery7' => string '1' (length=1)
          'Area' => string '71508129-9b87-11de-822f-000c2965ae0e' (length=36)
          'SettlementType' => string '563ced13-f210-11e3-8c4a-0050568002cf' (length=36)
          'IsBranch' => string '0' (length=1)
          'PreventEntryNewStreetsUser' => string '0' (length=1)
          'CityID' => string '890' (length=3)
          'SettlementTypeDescription' => string 'село' (length=8)
          'SettlementTypeDescriptionRu' => string 'село' (length=8)
          'SpecialCashCheck' => int 1
          'AreaDescription' => string 'Вінницька' (length=18)
          'AreaDescriptionRu' => string 'Винницкая ' (length=19)-->

<!-- getWarehouses() 0 =>
        array (size=53)
          'SiteKey' => string '10300' (length=5)
          'Description' => string 'Відділення №1: Миколаївське шосе, 5-й км' (length=71)
          'DescriptionRu' => string 'Отделение №1: Николаевское шоссе, 5-й км' (length=71)
          'ShortAddress' => string 'Херсон, Миколаївське шосе, 5-й км' (length=58)
          'ShortAddressRu' => string 'Херсон, Николаевское шоссе, 5-й км' (length=60)
          'Phone' => string '380800500609' (length=12)
          'TypeOfWarehouse' => string '9a68df70-0267-42a8-bb5c-37f427e36ee4' (length=36)
          'Ref' => string '0d545ed6-e1c2-11e3-8c4a-0050568002cf' (length=36)
          'Number' => string '1' (length=1)
          'CityRef' => string 'db5c88cc-391c-11dd-90d9-001a92567626' (length=36)
          'CityDescription' => string 'Херсон' (length=12)
          'CityDescriptionRu' => string 'Херсон' (length=12)
          'SettlementRef' => string 'e71f8b5f-4b33-11e4-ab6d-005056801329' (length=36)
          'SettlementDescription' => string 'Херсон' (length=12)
          'SettlementAreaDescription' => string 'Херсонська область' (length=35)
          'SettlementRegionsDescription' => string '' (length=0)
          'SettlementTypeDescription' => string 'місто' (length=10)
          'SettlementTypeDescriptionRu' => string 'город' (length=10)
          'Longitude' => string '32.578317610200000' (length=18)
          'Latitude' => string '46.676342077200000' (length=18)
          'PostFinance' => string '1' (length=1)
          'BicycleParking' => string '0' (length=1)
          'PaymentAccess' => string '0' (length=1)
          'POSTerminal' => string '1' (length=1)
          'InternationalShipping' => string '1' (length=1)
          'SelfServiceWorkplacesCount' => string '1' (length=1)
          'TotalMaxWeightAllowed' => string '0' (length=1)
          'PlaceMaxWeightAllowed' => string '1100' (length=4)
          'SendingLimitationsOnDimensions' =>
            array (size=3)
              ...
          'ReceivingLimitationsOnDimensions' =>
            array (size=3)
              ...
          'Reception' =>
            array (size=7)
              ...
          'Delivery' =>
            array (size=7)
              ...
          'Schedule' =>
            array (size=7)
              ...
          'DistrictCode' => string 'В1' (length=3)
          'WarehouseStatus' => string 'Working' (length=7)
          'WarehouseStatusDate' => string '2023-03-20 00:00:00' (length=19)
          'WarehouseIllusha' => string '0' (length=1)
          'CategoryOfWarehouse' => string 'Branch' (length=6)
          'Direct' => string '' (length=0)
          'RegionCity' => string 'ХЕРСОН' (length=12)
          'WarehouseForAgent' => string '0' (length=1)
          'GeneratorEnabled' => string '0' (length=1)
          'MaxDeclaredCost' => string '0' (length=1)
          'WorkInMobileAwis' => string '0' (length=1)
          'DenyToSelect' => string '0' (length=1)
          'CanGetMoneyTransfer' => string '1' (length=1)
          'HasMirror' => string '0' (length=1)
          'HasFittingRoom' => string '0' (length=1)
          'OnlyReceivingParcel' => string '0' (length=1)
          'PostMachineType' => string '' (length=0)
          'PostalCodeUA' => string '73034' (length=5)
          'WarehouseIndex' => string '53/1' (length=4)
          'BeaconCode' => string '' (length=0)-->