<header class="no-print">
    <div class="wrap-logo">
        <a href="#logo" class="logo">Логотип сайта</a>
    </div>
    <nav>
        <a class="active" href="../index">Головна</a>
        
        <?php

        $log = '';

        if (isset($_SESSION['logged'])){
            $log = $_SESSION['logged'];
        }

        if (isset($_GET['header']) && $_GET['header'] == 'admin') {
            echo '<a href="javascript:DoPost()">'. $log .':вийти</a>';
        }
        else{
            echo '<a href="../contacts">Контакти</a>';
            echo '<a href="../about">Про нас</a>';
        }
        ?>

    </nav>

    <script>

           function DoPost(){
              $.ajax({
                type: 'GET',
                url: 'blok/logout.php',
                success: function(msg) {
                      window.location.href = 'admin';
                }
              });
           }

    </script>

</header>