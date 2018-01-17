<?php
include('../includes/config.php');
include('../structure/database.php');
include('../structure/user.php');

$db = new database($db_host, $db_name, $db_user, $db_password);
$user = new user($db);

?>
<html>
    <head>
        <title>Donate</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
        <script type="text/javascript">
            
        </script>
    </head>

<body>
    <div class="createbox" style="width:1000px;">
        <p class="title">Make a donation!</p>
        
        <div style="display:inline-block;margin:10px;">
            <h3>Why donate?</h3>
            <p>
                Donations are what pay for this games hosting, and our ability to further develop the game. Without the support of our fellow players & donators, this game would not be nearly as a success as it is now;
                however, any donations should be out of your own free will. We will never ask or pressure you to donate, or give unfair advantages to donators.
            </p>
            <p>
                <h3>What are the benefits of donating?</h3>
                <span style="font-weight:bold;color:red;">You must donate a minimum of $5 (does not have to be in one donation) to receive the following:</span>
                <ul>
                    <li>Account receives a donator status</li>
                    <li>Donator icon on highscores</li>
                    <li>Ability to change character avatar in-game</li>
                    <li>Username added to global boss & random boss name pool<font color="green">**</font></li>
                    <li>Access to any future benefits</li>
                </ul>
                
                When you donate, the amount you donated is automatically added to your account. You can see how much you donated in your account panel.
            </p>
            <p>
                <h3>Already donated?</h3>
                If you have previously donated before the new system was implemented, you can supply your transaction ID and the money you donated should automatically be added to your account donation total.&nbsp;
                <b><a href="claim.php">Claim your donations here!</a></b>
            </p>
            <p>
                <h3>Donate</h3>
                If you wish to donate, you can continue to PayPal by clicking on the button below.
                <br/><br/>
                <?php
                    if($user->isLoggedIn){
                        ?>
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                <input type="hidden" name="cmd" value="_s-xclick">
                                <input type="hidden" name="hosted_button_id" value="GCDL5A7NY9TRE">
                                <input type="hidden" name="custom" value="<?=$user->id?>">
                                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                            </form>
                        <?php
                    }else{
                        ?>
                            <i>You must be logged in to donate.</i>
                        <?php
                    }
                ?>
            </p>
            <hr>
            <span style="font-size:15px;"><font color="green">**</font> takes effect on the next server restart after your payment is processed</span>
        </div>
    </div>
</body>
</html>