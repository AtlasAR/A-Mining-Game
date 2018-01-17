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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Forum Index</title>
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
                <?php
                    $cats = $f->extractCats();
                    
                    foreach($cats as $cat){
                                            
                        ?>
                            <div class="forum">
                            <span class="title"><span class="titletext"><?=htmlentities($cat['name'])?></span></span>
                            <table cellpadding="7" class="center">
                            <tr>
                                <th colspan="1">Forum</th>
                                <th colspan="1">Last Post</th>
                                <th colspan="1">Threads</th>
                                <th colspan="1">Posts</th>
                            </tr>
                            <?php

                            $forums = $f->extractForums($cat['id']);

                            foreach($forums as $forum){
                                if($forum['id'] == $f->imid && !$user->ideaMaker())
                                    continue;
                                
                                $postcount = $f->forumPostCount($forum['id']);
                                $lastPostDetails = $f->forumLastPoster($forum['id']);
                                $lastPoster = htmlentities($user->getUsernameById($lastPostDetails['userid']));
                                
                                if(empty($lastPoster))
                                    $lastpost = 'None.';
                                else
                                    $lastpost = '<abbr class="timeago" title="'. $lastPostDetails['time'] .'">'.$lastPostDetails['time'].'</abbr><br/>By '.$lastPoster;
                            ?>
                            

                                <tr class="dark">
                                    <td style="width:425px;">
                                        <a href="forum.php?id=<?=$forum['id']?>" style="font-size:18px;font-weight:bold;color:#ABE3DF;"><?=htmlentities($forum['forum'])?></a>
                                        <br/>
                                        <?=htmlentities($forum['description'])?>
                                    </td>
                                    <td style="width:175px;"><?=$lastpost?></td>
                                    <td><?=number_format($postcount['threads'])?></td>
                                    <td><?=number_format($postcount['posts'])?></td>
                                </tr>
                        <?php } ?>
                            </table>
                        </div>
                    <?php } ?>
                        
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
