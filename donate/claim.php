<?php
include('../includes/config.php');
include('../structure/database.php');
include('../structure/user.php');
include('../structure/ipn.php');

$db = new database($db_host, $db_name, $db_user, $db_password);
$user = new user($db);
$ipn = new ipn($db);

?>
<html>
    <head>
        <title>Claim Donation</title>
        <link rel="stylesheet" type="text/css" href="../css/highscores.css?v=1">
    </head>

<body>
    <div class="createbox" style="width:1000px;">
        <p class="title">Claim previous donation</p>
        
        <div style="display:inline-block;margin:10px;">
            <?php
                if($user->isLoggedIn){
                    if(!isset($_POST['txn_id'])){
                        ?>
                            <p>
                                <h3>Provide your transaction ID</h3>
                                In order for us to receive your previous donation, you must supply your transaction ID. Please paste it into the box below:<br/>
                                <form action="claim.php" method="POST">
                                    <input type="text" name="txn_id"> <input type="submit" value="Claim">
                                </form>
                            </p>
                        <?php
                    }else{
                        $txn_id = $_POST['txn_id'];
                        
                        $response = $ipn->lookupTransaction($txn_id);
                        
                        if(isset($response['L_ERRORCODE0'])){
                            ?>
                                <p>
                                    <h3>Whoops!</h3>
                                    The provided transaction ID does not exist! <a href="claim.php">Back</a>
                                </p> 
                            <?php
                        }else{
                            //check if TXN_ID already exists
                            $db->processQuery("SELECT * FROM `transactions` WHERE `txn_id` = ? LIMIT 1", array($txn_id));
                            
                            if($db->getRowCount() > 0){
                                ?>
                                    <p>
                                        <h3>Whoops!</h3>
                                        This transaction ID has already been claimed.
                                    </p> 
                                <?php
                            }else{
                                $amount = $response['L_AMT0'];
                                
                                $db->processQuery("INSERT INTO `transactions` VALUES (?)", array($txn_id));
                                $db->processQuery("UPDATE `users` SET `donations` = `donations` + ? WHERE `id` = ? LIMIT 1", array($amount, $user->id));
                                
                                ?>
                                    <p>
                                        <h3>Donation Claimed</h3>
                                        Your donation of <b><?=$amount .' '. $response['L_CURRENCYCODE0']?></b> has been claimed and added to your donation total.
                                    </p>
                                <?php
                            }
                        }
                    }
                }else{
                    ?>
                        <p>
                            <h3>Whoops!</h3>
                            You need to be logged in to claim your donation, <a href="../account/login.php">login now</a>.
                        </p>
                    <?php
                }
            ?>
        </div>
    </div>
</body>
</html>