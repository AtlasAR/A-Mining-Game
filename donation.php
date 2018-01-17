<?php
    include('includes/config.php');
    include('structure/database.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);

    if($_GET['get']=='goal'){
        $data = $db->processQuery("SELECT `donations` FROM `data`", array(), true);
        echo $data[0][0].'-60';
    }
?>