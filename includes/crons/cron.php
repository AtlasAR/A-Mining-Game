<?php
    include('../config.php');
    include('../database.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    
    $db->processQuery("UPDATE `data` SET `donations` = 0", array());
?>