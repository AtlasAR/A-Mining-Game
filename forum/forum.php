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
    $id = (int)$_GET['id'];
    $forum_name = htmlentities($f->forumNameByID($id));
    
    if(!$f->forumExists($id) || ($id == $f->imid && !$user->ideaMaker())) $base->redirect('index.php');
    
    $number = $db->processQuery("SELECT * FROM `forums_threads` WHERE `parent` = ?", array($id));
    $number = $db->getRowCount();

    //threads to show per page
    $per_page = 20;

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
        <title>Forums - <?=$forum_name?></title>
        <script type="text/javascript" src="../resources/jquery2.js"></script>
        <script type="text/javascript" src="../resources/jquery-ui.js"></script>
        <script type="text/javascript" src="../resources/jquery.timeago.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('abbr.timeago').timeago();
            });
        </script>
        <link rel="stylesheet" type="text/css" href="../css/forum-main.css?v=1">
    </head>
    <body>
        <?php include('navbar.php'); ?>
        <div id="container">
            <div class="forum">
                <span class="title"><span class="titletext">Viewing <?=$forum_name?> . . .</span></span>
                <div class="clearOrangeSpan"><a href="index.php"><< Back to Index</a></div>
                
                <div class="innerContainer">
                    <?php
                        if($user->isLoggedIn){
                            ?><br/><a href="new.php?forum=<?=$id?>" class="button">NEW TOPIC</a><hr><?php
                        }
                    ?>
                    <table cellpadding="8" cellspacing="4" style="width:100%;">
                        <tr>
                            <th>Title</th>
                            <th>Last Post</th>
                            <th>Posts</th>
                        </tr>
                        <?php
                            $threads = $db->processQuery("SELECT * FROM `forums_threads` WHERE `parent` = ? ORDER BY `pinned` DESC,`lastreply` DESC LIMIT $start,$per_page", array($id), true);

                            foreach($threads as $thread){
                                $lastPoster = htmlentities($user->getUsernameById($f->threadLastPoster($thread['id'])));
                                
                                $pre = '';
                                
                                if($thread['pinned'] == 1)
                                    $pre .= '<img src="../css/images/sticky.png" /> ';
                                
                                if($thread['locked'] == 1)
                                    $pre .= '<img src="../css/images/locked.png" /> ';
                                
                                $creator = htmlentities($user->getUsernameById($thread['userid']));
                                
                                ?>
                                    <tr class="dark">
                                        <td style="width:400px;">
                                            <b><?=$pre?><a href="thread.php?forum=<?=$id?>&id=<?=$thread['id']?>"><?=htmlentities($thread['title'])?></a></b>
                                            <br/><span style="font-size:12px;">by <?=$creator?></span>
                                        </td>
                                        <td>Posted by <b><?=$lastPoster?></b> <abbr class="timeago" title="<?=$thread['lastreply']?>"><?=$thread['lastreply']?></abbr></td>
                                        <td><?=number_format($f->threadPostCount($thread['id']))?></td>
                                    </tr>
                                <?php
                            }
                        ?>
                    </table>
                            
                    <center>
                        <ul class="nav">
                            <li>Page <?=$page?> of <?=$pages?></li>
                                <?php
                                    $baseURL = 'forum.php?id='. $id .'&';

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
