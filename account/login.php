<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    if($user->isLoggedIn){
        $title = 'Error';
        $content = '<p>You\'re already logged in!</p>';
    }else{
        if(!isset($_POST['username']) && !isset($_POST['password'])){
            $title = 'Login';
            $content = '
                <p>Login to your account.</p>
                <hr>
                <form action="login.php" method="POST">
                    <table>
                        <tr><td>Username</td><td><input type="text" name="username" maxlength="12"></td></tr>
                        <tr><td>Password</td><td><input type="password" name="password"></td></tr>
                        <tr><td colspan="2" style="text-align:right;"><input type="submit" value="Login"></td></tr>
                    </table>
                </form>
            ';
        }else{
            $username = trim($_POST['username']);
            $password = hash(sha256, md5(sha1(trim($_POST['password']))));

            $user_data = $db->processQuery("SELECT `password`,`id`,`disabled` FROM `users` WHERE `username` = ? LIMIT 1", array($username), true);

            if($db->getRowCount() > 0){
                if($user_data[0]['disabled'] == 0){
                    $db_password = substr(substr($user_data[0]['password'], 54), 0, -3);

                    if($db_password == $password){
                        $title = 'Success!';
                        $content = '<p>You have successfully been logged in! <a href="index.php">Click here to continue...</a></p>';

                        $session = $user->generateSession($user_data[0]['id']);
                        
                        if($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
                            setcookie('session', $session, time()+250000, '/');
                        else
                            setcookie('session', $session, time()+250000, '/', 'rscharts.com');
                    }else{
                        $title = 'Error';
                        $content = '<p>The given password is incorrect. <a href="login.php">Go Back</a></p>';
                    }
                }else{
                    $title = 'Error';
                    $content = '<p>This account has been disabled. <a href="login.php">Go Back</a></p>';
                }
            }else{
                $title = 'Error';
                $content = '<p>No account with this username exists. <a href="login.php">Go Back</a></p>';
            }
        }
    }
?>
<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
        <script type="text/javascript">
            
        </script>
    </head>

<body>
    <div class="createbox">
        <p class="title"><?=$title;?></p>
        
        <?=$content;?>
    </div>
</body>
</html>