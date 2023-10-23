<!DOCTYPE HTML>

<html>

<head>
    <title>SholomProMax</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="style.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
</head>

<body>

    <script>

    function printInfo($in, $hide = 1) {
        $.ajax({
            url: 'blok/print_to_work.php',
            method: 'GET',
            dataType: 'html',
            data: 'ID=' + $in + '&hideForWorker=' + $hide,
            success: function(data) {
                $('#info').html(data);
            }
        });
    };

    </script>

    <?php

    if (!isset($_GET['n'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . "/class/universal.php";
        require_once("blok/conn_local.php");

        $query = 'SELECT `ID`,`phone`,`sholom_num` FROM `client_info` where `date_out` IS NULL AND `sholom_num` <> 0 ORDER BY `date_max` ASC';

        $result = mysqli_query($link, $query);

        if (mysqli_num_rows($result) != 0) {
            session_start();

            if (isset($_SESSION['logged'])) {
                echo '<a href="work" class="no-print" > <<АДМІНКА </a>';
            }
            echo '<label for="numberToInfo" class="no-print" style=font-size:120%;font-weight:bold;padding:10px;">Незавершені задачі:</label>';
            echo '<div id="numberToInfo" class="no-print">';

            foreach ($result as $row) {
                echo '<button class="but_info" id="'.$row["ID"].'" />' . $row["sholom_num"] . '#тел.: ...' . substr($row['phone'], -4);
            }

            echo '</div>';
        }

        echo '<div id="info" />';
    }


    ?>

    <script>
            $('.but_info').on('click', function () {
            let v = $(this).attr('id');
            printInfo(v);
            });
    </script>

</body>

</html>