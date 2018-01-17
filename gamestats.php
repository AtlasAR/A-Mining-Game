<?php
    include('includes/config.php');
    include('structure/database.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    
    $players = $db->processQuery("SELECT `save`, `username` FROM `users`", array(), true);
    
    $money = 0;
    $curMoney = 0;
    $bc = 0;
    $scientists = 0;
    $bossesDefeated = 0;
    $OPMResearch = 0;
    $mpt = 0;
    $exp = 0;
    $hLVL = array('username' => '', 'lvl' => 0);
    foreach($players as $player){
        $data = json_decode($player['save'], true);
        
        $money += $data['totalMoneyEarned'];
        $curMoney += $data['money'];
        $bc += $data['bossCurrency'];
        $scientists += $data['scientists']+$data['scientistsBC'];
        $bossesDefeated += $data['bossesDefeated'];
        $OPMResearch += $data['workerOPMResearch'];
        $mpt += $data['moneyPerTick'];
        $exp += $data['exp'];
        
        $level = floor((sqrt(625+100*$data['exp'])-25)/50);
        
        if($level > $hLVL['lvl']){
            $hLVL['lvl'] = $level;
            $hLVL['username'] = $player['username'];
        }
    }
    
?>

Players have earned a total amount of $<?=number_format($money)?> altogether.<br/>
There is currently $<?=number_format($curMoney)?> in the game.<br/>
There is currently <?=number_format($bc)?> BC in the game.<br/>
If all players were combined, we would have a money per tick of $<?=number_format($mpt)?><Br/>
Players have defeated a total of <?=number_format($bossesDefeated)?> random bosses.<br/>
Altogether players have purchased <?= number_format($scientists)?> scientists and researched the WorkerOPM research <?=number_format($OPMResearch)?> times.<br/>
Altogether players have earned a total of <?=number_format($exp)?> XP.<br/>
Currently, the highest character level is <?=number_format($hLVL['lvl'])?> (<?=$hLVL['username']?>).