<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    if($user->isLoggedIn){
        $title = 'Error';
        $content = '<p>WUT R U DOING? U R GUNNA CREATE AN ACCOUNT WHILE LOGGED INTO AN ACCOUNT? WAT.</p>';
    }elseif(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])){        
        $username = trim($_POST['username'], ' ');
        $password = $_POST['password'];
        $password2= $_POST['password2'];
        
        if(strlen($username) > 12 || strlen($username) < 3){
            $title = 'Error';
            $content = '<p>Your username must be at least 3 characters, and no greater than 12 characters.</p>';
        }else if(strlen($password) < 3){
            $title = 'Error';
            $content = '<p>Your password cannot be less than 3 characters.';
        }else if($password != $password2){
            $title = 'Error';
            $content = '<p>Your passwords do not match!</p>';
        /*}else if($user->accountsConnectedToIP($_SERVER['REMOTE_ADDR']) >= 2){
            $title = 'Error';
            $content = '<p>You have the maximum number of allowed accounts for this IP.</p>';
        */}else if($user->usernameExists($username)){
            $title = 'Error';
            $content = '<p>This username already exists!</p>';
        }else{
            //make sure they don't already have an account that is disabled
            //with this ip
            
            $db->processQuery("SELECT * FROM `users` WHERE `ip` = ? AND `disabled` = 1 LIMIT 1", array($_SERVER['REMOTE_ADDR']));
            
            if($db->getRowCount() > 0 || isset($_COOKIE['banned'])){
                $title = 'Error';
                $content = 'You do not have permission to create a new account.';
                setcookie('banned','true',time() + (10 * 365 * 24 * 60 * 60), '/', '.rscharts.com');
            }else{
                if($user->createUser($username,$password)){
                    $title = 'Success';
                    $content = '<p>Congratulations! Your account has successfully been created. <a href="index.php">Home</a></p>';
                }else{
                    $title = 'Error';
                    $content = '<p>Your account could not be registered. Please try again soon!</p>';
                }
            }
        }
    }else{
        $title = 'Register Account';
        $content = '
        <form action="register.php" method="POST">
            <table>
                <tr><td>Username</td><td><input type="text" name="username" maxlength="12"></td></tr>
                <tr><td>Password</td><td><input type="password" name="password"></td></tr>
                <tr><td>Confirm Password</td><td><input type="password" name="password2"></td></tr>
                <tr><td colspan="2" style="text-align:right;"><input type="submit" value="Create Account"></td></tr>
            </table>
        </form>';
    }
?>
<html>
    <head>
        <title>Register</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
    </head>

<body>
    <div class="createbox">
        <p class="title"><?=$title;?></p>
        
        <?=$content;?>
    </div>
</body>
</html>