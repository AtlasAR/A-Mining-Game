<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');
    include('../structure/base.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    $id = $_POST['id'];
    $group = $user->ownsGroup();
    if($user->isLoggedIn && $group && $id != $user->id){
        $db->processQuery("UPDATE `users` SET `group` = 0 WHERE `id` = ? AND `group` = ? LIMIT 1", array($id, $group));
        
        if($db->getRowCount() > 0)
            echo 'success';
    }
?>