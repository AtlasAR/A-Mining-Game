<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
  
    if($_GET['action'] == 'leave' && $user->hasGroup($user->id) && $user->isLoggedIn){
        $db->processQuery("UPDATE `users` SET `group` = 0 WHERE `id` = ? LIMIT 1", array($user->id));
    }
    
    if($_GET['action'] == 'disband' && $user->isLoggedIn && $user->ownsGroup()){
        $group = $user->ownsGroup();
        $db->processQuery("UPDATE `users` SET `group` = 0 WHERE `group` = ?", array($group));
        $db->processQuery("DELETE FROM `groups` WHERE `id` = ? LIMIT 1", array($group));
    }
?>