<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $hs = new highscores($db);
    $user = new user($db);
    
    $groupID = $user->ownsGroup();
    if($user->isLoggedIn && $groupID){
        if(isset($_POST['action'])){
            if($_POST['action'] == 'setcolor'){
                $colors = array('red','green','blue','orange','yellow','purple','pink','brown');
                
                if($hs->groupTotalMoneyEarned($groupID) >= 100000000000000 && in_array($_POST['color'], $colors))
                    $db->processQuery("UPDATE `groups` SET `color` = ? WHERE `id` = ? LIMIT 1", array($_POST['color'], $groupID));
                else
                    echo 'fail';
            }
        }
    }
?>