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
    $id = (int)$_REQUEST['id'];
    $rights = $user->rights($user->id);
    
    if(!$f->threadExists($id) || !$user->isLoggedIn || ($id == $f->imid && !$user->ideaMaker())) $base->redirect('index.php');
    
    //thread details
    $data = $db->processQuery("SELECT `parent`,`userid`,`content` FROM `forums_threads` WHERE `id` = ? LIMIT 1", array($id), true);
    $data = $data[0];
    
    $thread_name = htmlentities($data['title']);
    
    $mute = $user->muted();
    $pre = '';
    
    if($user->forumBanned()){
        $content = 'You can no longer participate in the forums with this account. If you feel this is a mistake, please contact an administrator.';
    }elseif($mute){
        $content = 'Your account has been muted for '. $mute[0]['forum_mute_length'] .' hours on '. $mute[0]['forum_mute'] .'. 
            During this time, you cannot create threads or reply on the forum. If you feel this is a mistake, please contact an administrator.';
    }elseif($f->threadLocked($id) && !($rights >= 1)){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $data['parent'] .'&id='. $id .'"><< Back to '. $thread_name .'</a></div>';
        $content = 'You can\'t modify a thread that has been locked.';
    }elseif($data['userid'] != $user->id && !($rights >= 1)){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $data['parent'] .'&id='. $id .'"><< Back to '. $thread_name .'</a></div>';
        $content = 'You can\'t modify a thread that isn\'t yours.';
    }elseif(!isset($_POST['content']) || !isset($_POST['submit'])){
        $pre = '<div class="clearOrangeSpan"><a href="thread.php?forum='. $data['parent'] .'&id='. $id .'"><< Back to '. $thread_name .'</a></div>';
        $content = '
            <form action="modifythread.php" method="POST">
                <input type="hidden" name="id" value="'. $id .'">
                <table>
                    <tr>
                        <td>Content</td>
                        <td><textarea name="content" cols="70" rows="20" maxlength="2000">'. htmlentities($base->remBr($data['content'])) .'</textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:right;"><input type="submit" name="submit" class="button" value="Update Thread"></td>
                    </tr>
                </table>
            </form>
        ';
    }else{
        $thread = nl2br($_POST['content']);
        
        if((strlen($thread) > 5000 || strlen($thread) < 10) && !($rights > 0)){
            $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
            $content = 'Your thread cannot exceed 5000 characters or be less than 10 characters.';
        }else{
            //successful! update the thread now!
            $db->processQuery("UPDATE `forums_threads` SET `content` = ? WHERE `id` = ? LIMIT 1", array($thread, $id));
            
            if($db->getRowCount() > 0){
                $content = 'Your thread was successfully modified! Redirecting you back to the thread...';
                $base->redirect('thread.php?forum='. $data['parent'] .'&id='.$id);
            }else{
                $pre = '<div class="clearOrangeSpan"><a href="#" name="history_back"><< Back</a></div>';
                $content = 'Uh, oh! We failed to update your thread. Try again in a few minutes.';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Forums - Modify Thread</title>
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
