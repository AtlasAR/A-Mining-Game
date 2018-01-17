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
    $id = (int)$_REQUEST['thread'];
    $reply_id = (int)$_REQUEST['id'];
    $rights = $user->rights($user->id);
    
    if(!$f->threadExists($id) || !$f->replyBelongsToThread($reply_id, $id) || !$user->isLoggedIn) $base->redirect('index.php');
    
    //reply details
    $data = $db->processQuery("
        SELECT t.`parent`,t.`title`,p.`userid` as userid,p.`content` as content FROM `forums_posts` as p
        LEFT JOIN `forums_threads` AS t ON (p.`thread` = t.`id`)
        WHERE p.`id` = ? LIMIT 1", array($reply_id), true);
    $data = $data[0];
    
    $thread_name = htmlentities($data['title']);
    $forum = $data['parent'];
    
    if($forum == $f->imid && !$user->ideaMaker()) $base->redirect('index.php');
    
    $mute = $user->muted();
    $pre = '';
    
    if($user->forumBanned()){
        $content = 'You can no longer participate in the forums with this account. If you feel this is a mistake, please contact an administrator.';
    }elseif($mute){
        $content = 'Your account has been muted for '. $mute[0]['forum_mute_length'] .' hours on '. $mute[0]['forum_mute'] .'. 
            During this time, you cannot create threads or reply on the forum. If you feel this is a mistake, please contact an administrator.';
    }elseif($f->threadLocked($id) && !($rights >= 1)){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $forum .'&id='. $id .'"><< Back to '. $thread_name .'</a></div>';
        $content = 'You can\'t modify a post that belongs to a thread that has been locked.';
    }elseif($data['userid'] != $user->id && !($rights >= 1)){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $forum .'&id='. $id .'"><< Back to '. $thread_name .'</a></div>';
        $content = 'You can\'t modify a reply that isn\'t yours.';
    }elseif(!isset($_POST['content']) || !isset($_POST['submit'])){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $forum .'&id='. $id .'"><< Back to '. $thread_name .'</a></div>';
        $content = '
            <form action="modifyreply.php" method="POST">
                <input type="hidden" name="id" value="'. $reply_id .'">
                <input type="hidden" name="thread" value="'. $id .'">
                <table>
                    <tr>
                        <td>Reply</td>
                        <td><textarea name="content" cols="70" rows="20" maxlength="2000">'. htmlentities($base->remBr($data['content'])) .'</textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:right;"><input type="submit" name="submit" class="button" value="Update Post"></td>
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
            //successful! update the reply now!
            $db->processQuery("UPDATE `forums_posts` SET `content` = ? WHERE `id` = ? LIMIT 1", array($reply, $reply_id));
            
            if($db->getRowCount() > 0){
                $content = 'Your reply was successfully modified! Redirecting you back to the thread...';
                $base->redirect('thread.php?forum='. $forum .'&id='. $id);
            }else{
                $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
                $content = 'Uh, oh! We failed to update your reply. Try again in a few minutes.';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Forums - Modify Reply</title>
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
