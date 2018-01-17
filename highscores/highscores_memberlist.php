<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $hs = new highscores($db);
    $user = new user($db);
    
    $id = $_REQUEST['group'];
    if($user->isLoggedIn && $hs->isOwner($user->id, $id)){
        //PAGINATION VARS
        $number = $db->processQuery("SELECT * FROM `users` WHERE `group` = ?", array($id));
        $number = $db->getRowCount();

        //posts to show per page
        $per_page = 30;

        //number of pages
        $pages = ceil($number/$per_page);
        $pages = ($pages == 0) ? 1 : $pages;

        $page = (!ctype_digit($_GET['page']) || $_GET['page'] < 1 || $_GET['page'] > $pages) ? 1 : $_GET['page'];

        $start = (($page-1)*$per_page);

        $next = ($page < $pages) ? ($page+1) : $page;
        $prev = ($page > 1) ? ($page-1) : 1;
        //END PAGINATION VARS
        
        $groupname = $hs->groupNameByID($id);
        
        //the members we will be showing for the selected page
        $members = $db->processQuery("SELECT `id`,`username` FROM `users` WHERE `group` = ? ORDER BY `username` ASC LIMIT $start,$per_page", array($id), true);
        $members_displayed = $db->getRowCount();
        
        $title = 'Group Memberlist for '. htmlentities($groupname);
        $content = '<p>Showing '. $members_displayed .'/'. $number.' members.</p><hr>';
        
        //$content .= '<p>Search for a member via username<br/><input type="text" name="username"><button name="search">Search</button></p>';
        
        //now display each of those members
        $content .= '<table class="light" cellpadding="7">';
        foreach($members as $member){
            if($member['id'] != $user->id)
                $options = '<a href="#" name="kick-'. $member['id'] .'">[kick]</a>';
            
            $content .= '<tr><td>'.htmlentities($member['username']).'</td><td>'. $options .'</td></tr>';
        }
        $content .= '</table>';
        
        $content .= '
        <table cellpadding="3" style="margin-top:20px;">
                <tr>';
        
        //PAGE LIST
        $baseURL = 'highscores_memberlist.php?group='. $_GET['group'] .'&';
        if($page > 1) $content .= '<td><a href="'. $baseURL .'page='. ($page-1) .'"><< Prev</a></td>';

        $pagelist = '';
        for($i = 1; $i <= $pages; $i++){
            if($page==$i)
                $pagelist .= ' <a href="'. $baseURL .'page='. $i .'"><b>'.$i.'</b></a> ';
            else
                $pagelist .= ' <a href="'. $baseURL .'page='. $i .'">'.$i.'</a> ';
        }

        $content .= '<td>'. $pagelist . '</td>';

        if($page < $pages) $content .= '<td><a href="'. $baseURL .'page='. ($page+1) .'">Next >></a></td>';
        
        $content .= '
            </tr>
        </table>';
    }else{
        $title = 'Error';
        $content = '<p>You must be the owner of the group to do this. <a href="index.php">Back</a></p>';
    }
?>
<html>
    <head>
        <title>Highscores - Group Members</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
        <style>
            .light {
                font-size:20px;
                padding:6px;
                background-color:#F6F6F6;
                margin:10px;
            }
        </style>
        <script type="text/javascript" src="../resources/jquery2.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $('a[name|="kick"]').click(function(){
                    var id = $(this).attr('name').split('-')[1];
                    var self = this;
                    
                    $.ajax({
                        url : 'highscores_kickuser.php',
                        type : 'POST',
                        data : { id : id },
                        success : function(r){
                            if(r == 'success')
                                $(self).closest('tr').remove();
                            else
                                alert('Failed to kick the user from the group.');
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