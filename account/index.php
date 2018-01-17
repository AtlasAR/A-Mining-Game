<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    if($user->isLoggedIn){
        $title = 'Account Center';
        
        $content = '<p style="text-align:center;margin-bottom:20px;"><a href="../index.php">Play game</a> | <a href="../forum/index.php">Visit Forums</a> | <a href="logout.php">Logout</a></p>';
        
        if($user->donations > 0){
            
            if(isset($_FILES['avatar']) && $user->donations >= 5){
                $name = $_FILES['avatar']['name'];
                
                //this is retarded, but strict standards I suppose!
                $ext = explode('.', $name);
                $ext = end($ext);
                
                if($_FILES['avatar']['error'] > 0){
                    $content .= 'Whoops! Failed to upload the image (error #'. $_FILES['avatar']['error'] .').';
                }elseif($_FILES['avatar']['size'] > 524288){
                    $content .= 'The image you supplied is larger than the max size of half a megabyte.';
                }elseif(!in_array($ext, array('jpg', 'jpeg', 'png'))){
                    $content .= 'You can only upload an image with the extension .jpg, .jpeg, or .png';
                }else{
                    $data = $db->processQuery("SELECT `avatar` FROM `users` WHERE `id` = ? LIMIT 1", array($user->id), true);
                    
                    if(!empty($data[0]['avatar']))
                        unlink($_SERVER['DOCUMENT_ROOT'].'/game/avatars/'. $user->id .'.'.$data[0]['avatar']);
                    
                    $content .= 'Your avatar has successfully been uploaded! The changes will take affect the next time you load the game. <a href="index.php">Back</a>';
                    move_uploaded_file($_FILES['avatar']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/game/avatars/'. $user->id .'.'.$ext);
                    
                    $db->processQuery("UPDATE `users` SET `avatar` = ? WHERE `id` = ? LIMIT 1", array($ext, $user->id));
                }
            }else{
                $content .= 'Welcome to your account center. Your donation total is $<b>'. number_format($user->donations, 2, '.', ',') .'</b>.';
                
                //if they donated the $5 minimum for benefits
                if($user->donations >= 5){
                    $content .= '<hr><br/><b>Upload in-game avatar</b>
                        <form action="index.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="avatar" id="file"><input type="submit" value="Upload">
                        </form>

                    ';
                }
            }
        }else{
            $content .= 'Welcome to your account center. There is currently nothing here.';
        }
    }else{
        $title = 'Error';
        $content = 'You must be logged in to see this content! <a href="login.php">Login</a> or <a href="register.php">Register</a>';
    }
?>
<html>
    <head>
        <title>Account Center</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
        <script type="text/javascript"><?=$test?></script>
    </head>

<body>
    <div class="createbox" style="width:700px;">
        <p class="title"><?=$title;?></p>
        
        <div style="margin:6px;">
            <?=$content;?>
        </div>
    </div>
</body>
</html>