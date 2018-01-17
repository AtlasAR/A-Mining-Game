<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    if(!$user->isLoggedIn){
        $title = 'Error';
        $content = '<p>One does not simply logout while already logged out.</p>';
    }else{
        if($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
            setcookie('session', null, -1, '/');
        else
            setcookie('session', null, -1, '/', 'rscharts.com');
        
        $title = 'Logged out!';
        $content = '<p>You have been logged out!</p>';
    }
?>
<html>
    <head>
        <title>Logout</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
    </head>

<body>
    <div class="createbox">
        <p class="title"><?=$title;?></p>
        
        <?=$content;?>
    </div>
</body>
</html>