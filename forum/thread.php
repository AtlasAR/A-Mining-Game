<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/user.php');
    include('../structure/forum.php');
    include('../structure/base.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    $f = new forum($db);
    $base = new base();
    
    //requested forum ID
    $forum = (int)$_GET['forum'];
    $thread = (int)$_GET['id'];
    $rights = $user->rights($user->id);
    
    if(!$f->forumExists($forum) || !$f->threadExists($thread) || ($forum == $f->imid && !$user->ideaMaker())) $base->redirect('index.php');
    
    $data = $db->processQuery("SELECT `title`,`userid`,`content`,`datetime`,`locked`,`pinned` FROM `forums_threads` WHERE `id` = ? LIMIT 1", array($thread), true);
    $data = $data[0];
    
    $number = $db->processQuery("SELECT * FROM `forums_posts` WHERE `thread` = ?", array($thread));
    $number = $db->getRowCount();

    //posts to show per page
    $per_page = 15;

    //number of pages
    $pages = ceil($number/$per_page);
    $pages = ($pages == 0) ? 1 : $pages;

    $page = (!ctype_digit($_GET['page']) || $_GET['page'] < 1 || $_GET['page'] > $pages) ? 1 : $_GET['page'];

    $start = (($page-1)*$per_page);

    $next = ($page < $pages) ? ($page+1) : $page;
    $prev = ($page > 1) ? ($page-1) : 1;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Forums - <?=htmlentities($data['title'])?></title>
        <script type="text/javascript" src="../resources/jquery2.js"></script>
        <script type="text/javascript" src="../resources/jquery-ui.js"></script>
        <script type="text/javascript" src="js/mod.js"></script>
        <script type="text/javascript" src="../resources/jquery.timeago.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('abbr.timeago').timeago();
            });
        </script>
        <link rel="stylesheet" type="text/css" href="../css/forum-main.css">
        <link rel="stylesheet" type="text/css" href="../css/forum-thread.css?v=2">
    </head>
    <body>
        <?php include('navbar.php'); ?>
        <div id="container">
            <div class="forum">
                <span class="title"><span class="titletext"><?=$f->forumNameByID($forum)?></span></span>
                <div class="clearOrangeSpan"><a href="forum.php?id=<?=$forum?>"><< Back to <?=$f->forumNameByID($forum)?></a></div>
                
                <div class="innerContainer">
                    
                    <center><h2><?=htmlentities($data['title'])?></h2></center>
                    <?php
                        if($user->isLoggedIn){
                            if($data['locked'] == 0 || ($data['locked'] == 1 && $rights > 0))
                                echo '<a href="reply.php?forum='.$forum.'&id='.$thread.'" class="button">REPLY</a>';
                            
                            if($rights > 0){
                                echo '<a href="#" name="lock-'. $thread .'" class="button">'. (($data['locked'] == 1) ? 'UNLOCK' : 'LOCK') .'</a>';
                                echo '<a href="#" name="sticky-'. $thread .'" class="button">'. (($data['pinned'] == 1) ? 'UNSTICK' : 'STICK') .'</a>';
                            }
                            
                            if($rights > 1){
                                echo '<a href="#" name="delete-'. $thread .'" class="button">DELETE</a>';
                            }
                            
                            echo '<br/><br/>';
                        }
                        
                        $append = '';
                        if($rights > 0 || $user->id == $data['userid']){
                            $append .= ', <a href="modifythread.php?id='. $thread .'">edit</a>';
                        }
                    ?>
                    <table cellpadding="7" class="reply">
                        <tr>
                            <td class="left <?=$f->postClass($user->rights($data['userid']))?>">
                                <?=htmlentities(ucfirst($user->getUsernameById($data['userid'])))?>
                                <br/><?=$user->rankTitle($data['userid'])?>
                                <br/><?=htmlentities($user->groupName($data['userid']))?>
                            </td>
                            <td class="right <?=$f->postClass($user->rights($data['userid']))?>">
                                <span name="details">Posted <abbr class="timeago" title="<?=$data['datetime']?>"><?=$data['datetime']?></abbr><?=$append?></span>
                                <br/><br/>
                                <?php
                                    if($user->rights($data['userid']) > 1)
                                        echo $data['content'];
                                    else
                                        echo $base->br2nl(htmlentities($data['content']));
                                ?>
                            </td>
                        </tr>
                    </table>
                    
                    <?php
                        $posts = $db->processQuery("SELECT `id`,`userid`,`content`,`hidden`,`datetime` FROM `forums_posts` WHERE `thread` = ? ORDER BY `id` ASC LIMIT $start,$per_page", array($thread), true);
                        
                        foreach($posts as $post){
                            $append = '';
                            if($rights > 0 || $user->id == $post['userid']){
                                $append .= ', <a href="modifyreply.php?thread='. $thread .'&id='. $post['id'] .'">edit</a>';
                            }
                            
                            if($rights > 0){
                                if($post['hidden'] == 0)
                                    $append .= ', <a href="#" name="hidepost-'.$post['id'] .'">hide</a>';
                                else
                                    $append .= ', <a href="#" name="hidepost-'.$post['id'] .'">unhide</a>';
                            }
                            
                            if($post['hidden'] == 0){
                                ?>
                                        <br/>
                                        <table name="post-<?=$post['id']?>" cellpadding="7" class="reply <?=$f->postClass($user->rights($post['userid']))?>">
                                            <tr>
                                                <td class="left <?=$f->postClass($user->rights($post['userid']))?>">
                                                    <?=htmlentities(ucfirst($user->getUsernameById($post['userid'])))?>
                                                    <br/><?=$user->rankTitle($post['userid'])?>
                                                    <br/><?=htmlentities($user->groupName($post['userid']))?>
                                                </td>
                                                <td class="right <?=$f->postClass($user->rights($post['userid']))?>">
                                                    <span name="details">Posted <abbr class="timeago" title="<?=$post['datetime']?>"><?=$post['datetime']?></abbr><?=$append?></span>
                                                    <br/><br/>
                                                    <?php
                                                        if($user->rights($post['userid']) > 1)
                                                            echo $post['content'];
                                                        else
                                                            echo $base->br2nl(htmlentities($post['content']));
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                <?php
                            }else{
                                if($rights > 0){
                                    ?>
                                        <br/>
                                        <table name="post-<?=$post['id']?>" cellpadding="7" class="reply <?=$f->postClass($user->rights($post['userid']))?>">
                                            <tr>
                                                <td class="left <?=$f->postClass($user->rights($post['userid']))?>">
                                                    <?=htmlentities(ucfirst($user->getUsernameById($post['userid'])))?>
                                                    <br/><?=$user->rankTitle($user->rights($post['userid']))?>
                                                    <br/><?=htmlentities($user->groupName($post['userid']))?>
                                                    <span name="hidden" style="display:inline-block;clear:both;margin-top:15px;"><b>**hidden**</b></span>
                                                </td>
                                                <td class="right <?=$f->postClass($user->rights($post['userid']))?>">
                                                    <span name="details">Posted <abbr class="timeago" title="<?=$post['datetime']?>"><?=$post['datetime']?></abbr><?=$append?></span>
                                                    <br/><br/>
                                                    <?php
                                                        if($user->rights($post['userid']) > 1)
                                                            echo $post['content'];
                                                        else
                                                            echo $base->br2nl(htmlentities($post['content']));
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    <?php
                                }else{
                                    ?>
                                            <br/>
                                            <table name="post-<?=$post['id']?>" cellpadding="7" class="reply">
                                                <tr>
                                                    <td class="left">
                                                        &nbsp;
                                                    </td>
                                                    <td class="right">
                                                        <p>This message has been hidden by a moderator.</p>
                                                    </td>
                                                </tr>
                                            </table>
                                    <?php
                                }
                            }
                        }
                            
                        ?>
                    
                    <center>
                        <ul class="nav">
                            <li>Page <?=$page?> of <?=$pages?></li>
                                <?php
                                    $baseURL = 'thread.php?forum='. $forum .'&id='. $thread.'&';

                                    if($page > 1)
                                        echo '<li><a href="'. $baseURL .'page='. ($page-1) .'">Prev</a></li>';

                                    for($i = 1; $i <= 3; $i++){
                                        if($i <= $pages)
                                            echo '<li><a href="'. $baseURL .'page='. $i .'" '. (($i == $page) ? 'class="selected"' : '') .'>'. $i .'</a></li>';
                                        else
                                            break;
                                    }

                                    if($page < $pages)
                                        echo '<li><a href="'. $baseURL .'page='. ($page+1) .'">Next</a></li>';
                                ?>
                        </ul>
                    </center>
                </div>

                <div class="clear"></div>
            </div>
        </div>
    </body>
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-44010885-1', 'rscharts.com');
    ga('send', 'pageview');

    </script>
</html>
