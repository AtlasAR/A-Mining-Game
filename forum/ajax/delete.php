<?php
    include('../../includes/config.php');
    include('../../structure/database.php');
    include('../../structure/user.php');
    include('../../structure/forum.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    $f = new forum($db);
    
    $id = (int)$_POST['id'];
    
    if($user->isLoggedIn && $user->rights($user->id) > 1 && $f->threadExists($id)){
        $f->deleteThread($id);
    }
?>