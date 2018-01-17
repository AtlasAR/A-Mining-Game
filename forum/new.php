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
    $id = (int)$_REQUEST['forum'];
    $forum_name = htmlentities($f->forumNameByID($id));
    $rights = $user->rights($user->id);
    
    if(!$f->forumExists($id) || !$user->isLoggedIn || ($id == $f->imid && !$user->ideaMaker())) $base->redirect('index.php');
    
    $mute = $user->muted();
    
    //html before innerContainer div
    $pre = '';
    
    if($user->forumBanned()){
        $content = 'You can no longer participate in the forums with this account. If you feel this is a mistake, please contact an administrator.';
    }elseif($mute){
        $content = 'Your account has been muted for '. $mute[0]['forum_mute_length'] .' hours on '. $mute[0]['forum_mute'] .'. 
            During this time, you cannot create threads or reply on the forum. If you feel this is a mistake, please contact an administrator.';
    }elseif($user->postingTooSoon()){
        $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
        $content = 'You are attempting to post too soon! Please wait 30 seconds inbetween posts.';
    }elseif(!$f->canMakeThread($id, $rights)){
        $pre = '<div class="clearOrangeSpan"><a href="forum.php?id='. $id .'"><< Back to '. $forum_name .'</a></div>';
        $content = 'You are not permitted to a create a new thread in this forum.';
    }elseif(!isset($_POST['title']) || !isset($_POST['content']) || !isset($_POST['submit'])){
        $pre = '<div class="clearOrangeSpan"><a href="forum.php?id='. $id .'"><< Back to '. $forum_name .'</a></div>';
        $content = '
            <form action="new.php" method="POST">
                <input type="hidden" name="forum" value="'. $id .'">
                <table>
                    <tr>
                        <td>Title</td>
                        <td><input type="text" name="title" maxlength="45" size="70"></td>
                    </tr>
                    <tr>
                        <td>Content</td>
                        <td><textarea name="content" cols="70" rows="20" maxlength="2000"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:right;"><input type="submit" name="submit" class="button" value="Create Thread"></td>
                    </tr>
                </table>
            </form>
        ';
    }else{
        $title = trim($_POST['title']);
        $thread = nl2br($_POST['content']);
        
        if(strlen($title) > 45 || strlen($title) == 0){
            $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
            $content = 'Your title can\'t be greater than 45 characters or empty.';
        }elseif((strlen($thread) > 5000 || strlen($thread) < 10) && !($rights > 0)){
            $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
            $content = 'Your thread content cannot exceed 5000 characters or be less than 30 characters.';
        }else{
            //successful posting!
            $db->processQuery("INSERT INTO `forums_threads` VALUES (null, ?, ?, ?, ?, 0, 0, NOW(), NOW())", array(
                $user->id,
                $id,
                $title,
                $thread
            ), true);
            
            if($db->getRowCount() > 0){
                $content = 'Your post was successful! Redirecting you to your newly created thread now...';
                $base->redirect('thread.php?forum='. $id .'&id='. $db->getInsertId());
            }else{
                $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
                $content = 'Uh, oh! We failed to create your thread. Try again in a few minutes.';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Forums - New Thread</title>
        <script type="text/javascript" src="../resources/jquery2.js"></script>
        <script type="text/javascript" src="../resources/jquery-ui.js"></script>
        <link rel="stylesheet" type="text/css" href="../css/forum-main.css?v=1">
        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on('click', 'a[name="history_back"]', function(e){
                    e.preventDefault();
                    window.history.back();
                });
            });
        </script>
    </head>
    <body>
        <?php include('navbar.php'); ?>
        <div id="container">
            <div class="forum">
                <span class="title"><span class="titletext"><?=$forum_name?></span></span>
                <?=$pre?>
                
                <div class="innerContainer">
                    <?=$content;?>
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
