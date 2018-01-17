<?php
    include('includes/config.php');
    include('structure/database.php');
    include('structure/user.php');
    include('structure/base.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    $base = new base();
    $user = new user($db);
    
    if($user->rights($user->id) < 1) $base->redirect('index.php');
    
    $users = $db->processQuery("SELECT `username`,`donations` FROM `users` WHERE `flag` = 1 AND `disabled` = 0", array(), true);
    foreach($users as $user){
        if($user['donations'] > 0)
            echo '<span style="color:red;">'.$user['username'].' ('. $user['donations'] .')</span><br/>';
        else
            echo $user['username'].'<br/>';
    }
?>