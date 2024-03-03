<header class="no-print">
    <div class="wrap-logo">
        <a href="../work" class="logo">ШоломProMax</a>
    </div>
    <nav>
       
        <?php

        $log = '';

        if (isset($_SESSION['logged'])){
            $log = $_SESSION['logged'];

            if ($log == 'Administrator'){
                echo '<a href="../test">ТЕСТ</a>';
            }
        }

        if (isset($_GET['header']) && $_GET['header'] == 'admin') {

            echo '<form id="search" onSubmit="return SearchStart();">';
            echo '<input  id="request" placeholder="пошук" onClick="this.setSelectionRange(0, this.value.length)" />';
            echo '<button id="searcbut" type="submit" value="click">>></button></form>';

            echo '<a href="javascript:DoPost()">'. $log .':вийти</a>';
        }
        else{
            echo '<a href="../contacts">Контакти</a>';
        }
        ?>

    </nav>

    <script>

        var request;
        var changed = false;

        $(document).ready(function () {
            request = document.getElementById('request');
            if (request != null)
            request.addEventListener('input', inputHandler);
        });

    function DoPost() {
        $.ajax({
            type: 'GET',
            url: 'blok/logout.php',
            success: function (msg) {
                window.location.href = 'admin';
            }
        });
    }

        function SearchStart() {

            findtext = $(request).val().trim();

            if (findtext == '') { window.location.href = 'work'; return false; }

            window.location.href = 'work?search=' + findtext;
            return false;
        }

        function inputHandler() {
            if ($(request).val() == '' && changed)
                window.location.href = 'work';

            if ($(request).val().trim() != '') {
                changed = true;
            } else {
                changed = false;
            }
        }
    </script>

</header>