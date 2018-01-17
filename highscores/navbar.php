<div id="navbar">
    <a href="index.php">HIGHSCORES</a> |
    <?php
    if($user->isLoggedIn){
        ?>
            <a href="../account/index.php">MY ACCOUNT</a>
        <?php
    }else{
        ?>
            <a href="../account/register.php">REGISTER</a> |
            <a href="../account/login.php">LOGIN</a>
        <?php
    }

    ?>
</div>