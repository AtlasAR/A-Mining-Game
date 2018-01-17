<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $hs = new highscores($db);
    $user = new user($db);
    
    if($user->isLoggedIn){
        //let's make sure they don't already have a request going, yeah?
        $db->processQuery("SELECT * FROM `group_requests` WHERE `userid` = ? LIMIT 1", array($user->id));

        if($db->getRowCount() > 0){
            $title = 'Error';
            $content = '<p>You already have a pending request to a group.</p>';
        }else if($user->ownsGroup() || $user->groupID){
            $title = 'Error';
            $content = 'You\'re already in a group.';
        }else if(!isset($_POST['groupname']) && !isset($_REQUEST['group'])){
            $title = 'Request to Join Group';
            $content = '
                <form action="highscores_joingroup.php" method="POST">
                    <center>
                        Please enter in a group name<br/>
                        <input type="text" name="groupname" maxlength="20"> <input type="submit" value="Find Group">
                    </center>
                </form>
            ';
        }else if(isset($_REQUEST['group'])){
            $id = $_REQUEST['group'];

            if($hs->groupExistsByID($id)){
                if(isset($_POST['comment'])){
                    if(strlen($_POST['comment']) > 200){
                        $title = 'Error';
                        $content = '<p>Your comment cannot be greater than 200 characters.</p>';
                    }else{
                        $title = 'Request Sent';
                        $content = '<p>Your request has been sent to the group owner. You can check the status of your request on the highscores homepage.</p>';

                        $db->processQuery("INSERT INTO `group_requests` VALUES (null, ?, ?, ?, NOW())", array($user->id, $_POST['comment'], $id));
                    }
                }else{
                    $title = 'Final Step';
                    $content = 'If you want to leave a message for the group owner, you have the choice of entering in a smalll message below.
                        <hr>
                        <form action="highscores_joingroup.php" method="POST" style="text-align:center;">
                            <textarea maxlength="200" cols="40" rows="7" name="comment"></textarea>
                            <br/>
                            <input type="hidden" name="group" value="'. htmlentities($id) .'">
                            <input type="submit" value="Send Request">
                        </form>';
                }
            }else{
                $title = 'Error';
                $content = '<p>This group does not exist.</p>';
            }
        }else{
            if(strlen($_POST['groupname']) < 3){
                $title = 'Error';
                $content = '<p>Please keep your search query to a minimum of 3 characters.</p>';
            }else{
                //find the group
                $groups = $db->processQuery("SELECT `groupname`,`id` FROM `groups` WHERE `groupname` LIKE ? ORDER BY `reg_date`", array('%'.$_POST['groupname'].'%'), true);

                if($db->getRowCount() > 0){
                    $title = 'Group(s) Found, pick one';
                    $content = '
                        <table cellpadding="3" style="text-align:center;margin:0 auto;font-size:17px;">
                    ';

                    foreach($groups as $group){
                        $content .= '<tr><td>'. htmlentities($group['groupname']) .'</td><td><a href="highscores_joingroup.php?group='. $group['id'] .'">JOIN</a></td></tr>';
                    }

                    $content .= '</table>';
                }else{
                    $title = 'Error';
                    $content = '<p>No groups found.</p>';
                }
            }
        }
    }else{
        $title = 'Error';
        $content = '<p>You have to have an account to join a group.</p>';
    }
?>
<html>
    <head>
        <title>Highscores - Join</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
    </head>

<body>
    <div class="createbox">
        <p class="title"><?=$title;?></p>
        
        <?=$content;?>
    </div>
</body>
</html>