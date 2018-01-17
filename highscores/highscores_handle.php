<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $hs = new highscores($db);
    $user = new user($db);
    
    if($user->isLoggedIn){
        if(isset($_GET['request'])){
            $rID = $_GET['request'];

            $query = $db->processQuery("SELECT `group`,`userid` FROM `group_requests` WHERE `id` = ? LIMIT 1", array($rID), true);

            //make sure they are the owner of the group in question
            if($hs->isOwner($user->id, $query[0]['group'])){
                if($_GET['action'] == 'accept')
                    $hs->addMember($query[0]['userid'], $query[0]['group']);
                    
                //now remove the request
                $db->processQuery("DELETE FROM `group_requests` WHERE `id` = ? LIMIT 1", array($rID));
            }
        }
    }
?>