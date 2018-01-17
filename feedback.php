<?php
    include('includes/config.php');
    include('structure/database.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    
    if(isset($_POST['feedback'])){
        $db->processQuery("INSERT INTO `feedback` VALUES (null, ?, '', 0, ?, ?, ?)", array(
            $_POST['feedback'],
            $_POST['uniqueID'],
            ($_POST['collectData'] == 1) ? $_COOKIE['saved_game'] : '',
            $_SERVER['REMOTE_ADDR'])
        );
    
        if($db->getRowCount() > 0)
            echo $db->getInsertId();
        else
            echo 'fail';
    }else if(isset($_POST['getfeedback']) && isset($_POST['uniqueID'])){
        $results = $db->processQuery("SELECT `id`,`solved`,`response` FROM `feedback` WHERE (`uniqueID` = ? OR `ip` = ?) AND `solved` = 0 ORDER BY `id` ASC LIMIT 15", array(
            $_POST['uniqueID'],
            $_SERVER['REMOTE_ADDR']
        ), true);
        
        if($db->getRowCount() == 0){
            echo 'No open feedback requests found.';
        }else{
            echo '<table style="width:100%;" cellpadding="3">';
            foreach($results as $r){
                echo '<tr name="'. $r['id'] .'" style="background-color:#676766;color:#C6A500;"><td>FEEDBACK #'. $r['id'] .'&nbsp;&nbsp;'. ((!empty($r['response'])) ? '(RESPONDED)' : '') .'</td></tr>';
                echo '<tr name="'. $r['id']  .'"><td>'. ((!empty($r['response'])) ? $r['response'] : 'This request should have a response soon!') .'</td></tr>';
                echo '<tr name="'. $r['id']  .'"><td style="text-align:right;"><font size="1"><a href="#" name="feedbacksolved-'. $r['id'] .'" style="color:#C6A500;">Mark Solved</a></font></td></tr>';
            }
            echo '</table><br/><br/>';
        }
    }else if(isset($_POST['marksolved']) && isset($_POST['id']) && isset($_POST['uniqueID'])){
        $db->processQuery("UPDATE `feedback` SET `solved` = 1 WHERE (`uniqueID` = ? OR `ip` = ?) AND `id` = ? LIMIT 1", array(
            $_POST['uniqueID'],
            $_SERVER['REMOTE_ADDR'],
            $_POST['id']
        ));
        
        if($db->getRowCount() > 0)
            echo 'success';
        else
            echo 'fail';
    }
?>