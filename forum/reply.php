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
    
    //requested forum ID & threadID
    $id = (int)$_REQUEST['forum'];
    $thread = (int)$_REQUEST['id'];
    $rights = $user->rights($user->id);
    
    $forum_name = htmlentities($f->forumNameByID($id));
    $thread_name = htmlentities($f->threadNameByID($thread));
    
    if(!$f->forumExists($id) || !$f->threadExists($thread) || !$user->isLoggedIn || ($id == $f->imid && !$user->ideaMaker())) $base->redirect('index.php');
    
    $mute = $user->muted();
    $pre = '';
    
    if($user->forumBanned()){
        $content = 'You can no longer participate in the forums with this account. If you feel this is a mistake, please contact an administrator.';
    }elseif($mute){
        $content = 'Your account has been muted for '. $mute[0]['forum_mute_length'] .' hours on '. $mute[0]['forum_mute'] .'. 
            During this time, you cannot create threads or reply on the forum. If you feel this is a mistake, please contact an administrator.';
    }elseif($user->postingTooSoon()){
        $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
        $content = 'You are attempting to post too soon! Please wait 30 seconds inbetween posts.';
    }elseif($f->threadLocked($thread) && !($rights >= 1)){
        $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
        $content = 'You can\'t reply to a thread that has been locked.';
    }elseif(!isset($_POST['content']) || !isset($_POST['submit'])){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $id .'&id='. $thread .'"><< Back to '. htmlentities($thread_name) .'</a></div>';
        $content = '
            <form action="reply.php" method="POST">
                <input type="hidden" name="forum" value="'. $id .'">
                <input type="hidden" name="id" value="'. $thread .'">
                <table>
                    <tr>
                        <td>Reply</td>
                        <td><textarea name="content" cols="70" rows="20" maxlength="2000"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:right;"><input type="submit" name="submit" class="button" value="Post Reply"></td>
                    </tr>
                </table>
            </form>
        ';
    }else{
        $reply = nl2br($_POST['content']);
        
        if((strlen($reply) > 5000 || strlen($reply) < 10) && !($rights > 0)){
            $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
            $content = 'Your reply cannot exceed 5000 characters or be less than 10 characters.';
        }else{
            //successful posting!
            $db->processQuery("INSERT INTO `forums_posts` VALUES (null, ?, ?, ?, ?, 0, NOW())", array(
                $user->id,
                $thread,
                $reply,
                $_SERVER['REMOTE_ADDR']
            ));
            
            //update thread lastreply
            $db->processQuery("UPDATE `forums_threads` SET `lastreply` = NOW() WHERE `id` = ? LIMIT 1", array($thread));
            
            if($db->getRowCount() > 0){
                $content = 'Your post was successful! Redirecting you back to the thread...';
                $base->redirect('thread.php?forum='. $id .'&id='.$thread);
            }else{
                $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
                $content = 'Uh, oh! We failed to post your reply. Try again in a few minutes.';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Forums - Reply</title>
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
                <span class="title"><span class="titletext"><?=$fourm_name?></span></span>
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
