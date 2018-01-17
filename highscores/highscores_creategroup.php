<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $hs = new highscores($db);
    $user = new user($db);
    
    if($user->ownsGroup()){
        $title = 'Error';
        $content = '<p>You already own a group.</p>';
    }elseif(isset($_POST['groupname'])){
        $groupname = $_POST['groupname'];
        
        if(strlen($groupname) > 20 || strlen($groupname) < 3){
            $title = 'Error';
            $content = '<p>Your group name cannot be larger than 20 characters, and cannot be less than 3 character.</p>';
        }else if($hs->groupExists($groupname)){
            $title = 'Error';
            $content = '<p>This group name already exists!</p>';
        }else{
            if($hs->createGroup($user->id, $groupname)){
                $title = 'Success';
                $content = '<p>Your new group <b>'. htmlentities($groupname) .'</b> has been created! <a href="highscores_manage.php">Start managing it!</a></p>';
            }else{
                $title = 'Error';
                $content = '<p>Your group could not be created. Please try again soon!</p>';
            }
        }
    }else{
        $title = 'Create Group';
        $content = '
        <form action="highscores_creategroup.php" method="POST">
            <table>
                <tr><td>Group Name</td><td><input type="text" name="groupname" maxlength="20"></td></tr>
                <tr><td colspan="2" style="text-align:right;"><input type="submit" value="Create your group"></td></tr>
            </table>
        </form>';
    }
?>
<html>
    <head>
        <title>Highscores - Create Group</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
    </head>

<body>
    <div class="createbox">
        <p class="title"><?=$title;?></p>
        
        <?=$content;?>
    </div>
</body>
</html>