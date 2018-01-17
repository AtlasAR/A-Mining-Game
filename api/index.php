<?php
    include('../includes/config.php');
    include('../structure/database.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    
    $allowed = array(
        'money', 'totalMoneyEarned', 'moneyPerTick', 'lastSubmit', 'lastSave',
        'pickaxe_type', 'scientists', 'bossesDefeated', 'bossCurrency', 'statLootMoney', 'statEnemiesKilled', 'statDefendersKilled',
        'statBattlesWon', 'employed', 'finishedResearch', 'research'
    );
    
    if(isset($_GET['user'])){
        $username = $_GET['user'];
        
        if(isset($_GET['data'])){
            $query = $db->processQuery("SELECT `save` FROM `users` WHERE `username` = ? LIMIT 1", array($username), true);
            
            if($db->getRowCount() > 0){
                $stats = array();
                $data = json_decode(stripslashes($query[0]['save']), true);
                foreach($data as $var => $vardata){
                    if(in_array($var, $allowed))
                            $stats[$var] = $vardata;
                }
                
                echo json_encode($stats);
            }else{
                
            }
                
        }
    }else if(isset($_GET['allowed'])){
        echo json_encode($allowed);
    }
?>