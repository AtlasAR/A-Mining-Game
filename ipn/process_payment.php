<?php
error_reporting(E_ALL);
require('../includes/config.php');
require('../structure/database.php');
require('../structure/ipn.php');

$db = new database($db_host, $db_name, $db_user, $db_password);
$ipn = new ipn($db);

if($ipn->verify()){
    $userid = $_POST['custom'];
    $amount = $_POST['mc_gross'];
    
    $db->processQuery("UPDATE `data` SET `donations` = `donations` + ?", array($amount));
    $db->processQuery("UPDATE `users` SET `donations` = `donations` + ? WHERE `id` = ? LIMIT 1", array($amount, $userid));
    $db->processQuery("INSERT INTO `transactions` VALUES (?)", array($_POST['txn_id']));
}

?>