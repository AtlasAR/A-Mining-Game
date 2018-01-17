<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');
    include('../structure/base.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    if($user->isLoggedIn)
        $db->processQuery("DELETE FROM `group_requests` WHERE `userid` = ? LIMIT 1", array($user->id));
    
?>