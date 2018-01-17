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
        $groupname = $hs->groupNameByID($groupID);
        
        $title = 'Manage Your Group';
        $content = '<p>Managing <i>'. htmlentities($groupname) .'</i>... <a href="index.php">Home</a><br/><br/><b>total group money</b><br/>$'. number_format($hs->groupTotalMoneyEarned($groupID)) .'</p>';
        
        
        $color = $hs->getGroupColor($groupID);
        $content .= '<hr><center><b>GROUP OPTIONS</b></center><br/>
            <p>
                <b>Members</b><br/>
                <a href="highscores_memberlist.php?group='. $groupID .'">Group member list</a>
            </p>
            <br/>
            <p>
                <b>Group  name color</b><br/>
                (requires a total of $100,000,000,000,000 group money earned)<br/><br/>
                <input type="radio" name="color" value="green" '. (($color == 'green') ? 'checked="checked"' : '') .'>Green
                <input type="radio" name="color" value="red" '. (($color == 'red') ? 'checked="checked"' : '') .'>Red
                <input type="radio" name="color" value="blue" '. (($color == 'blue') ? 'checked="checked"' : '') .'>Blue
                <input type="radio" name="color" value="orange" '. (($color == 'orange') ? 'checked="checked"' : '') .'>Orange
                <input type="radio" name="color" value="yellow" '. (($color == 'yellow') ? 'checked="checked"' : '') .'>Yellow
                <input type="radio" name="color" value="purple" '. (($color == 'purple') ? 'checked="checked"' : '') .'>Purple
                <input type="radio" name="color" value="pink" '. (($color == 'pink') ? 'checked="checked"' : '') .'>Pink
                <input type="radio" name="color" value="brown" '. (($color == 'brown') ? 'checked="checked"' : '') .'>Brown
            </p>
            ';
        
        $content .= '<hr><center><b>REQUESTS</b></center><br/>';
        
        $requests = $db->processQuery("SELECT `id`,`userid`,`comment` FROM `group_requests` WHERE `group` = ? ORDER BY `date`", array($groupID), true);
        
        if($db->getRowCount() > 0){
            $content .= '<table cellpadding="10">';
        
            foreach($requests as $request){
                $content .= '<tr><td><a href="#" name="accept-'. $request['id'] .'">ACCEPT</a> &nbsp;&nbsp; <a href="#" name="deny-'. $request['id'] .'">DENY</a></td></tr>';
                $content .= '<tr><td>'. htmlentities($user->getUsernameById($request['userid'])) .'</td></tr>';

                if(strlen($request['comment']) == 0)
                    $content .= '<tr><td><i>No comment</i></td></tr>';
                else
                    $content .= '<tr><td><i>'. htmlentities($request['comment']) .'</i></td></tr>';

            }

            $content .= '</table>';
        }else{
            $content .= '<p>You have no join requests at this time.</p>';
        }
    }else{
        $title = 'Error';
        $content = 'You must have a group to access this content.';
    }
?>
<html>
    <head>
        <title>Highscores - Manage</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
        <script type="text/javascript" src="../resources/jquery2.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $('a[name|="accept"]').click(function(e){
                    e.preventDefault();
                    
                    var request = $(this).attr('name').split('-')[1];
                    
                    $.ajax({
                        url : 'highscores_handle.php?request='+request+'&action=accept'
                    });
                    
                    var derp = $(this).closest('tr'); 
                    derp.hide(500);
                    derp.next().hide(500);
                });
                
                $('a[name|="deny"]').click(function(e){
                    e.preventDefault();
                    
                    var request = $(this).attr('name').split('-')[1];
                    
                    $.ajax({
                        url : 'highscores_handle.php?request='+request+'&action=deny'
                    });
                    
                    var derp = $(this).closest('tr');
                    derp.hide(500);
                    derp.next().hide(500);
                });
                
                $('input[name="color"]').click(function(){
                    var color = $(this).val();
                    
                    $.ajax({
                        url : 'highscores_groupsettings.php',
                        type: 'POST',
                        data: {
                            action:'setcolor',
                            color: color
                        }
                    });
                });
            });
        </script>
    </head>

<body>
    <div class="createbox" style="width:700px;">
        <p class="title"><?=$title;?></p>
        
        <?=$content;?>
    </div>
</body>
</html>