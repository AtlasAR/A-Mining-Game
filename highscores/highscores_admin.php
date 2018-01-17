<?php
    include('includes/config.php');
    include('structure/database.php');
    include('structure/user.php');
    include('structure/base.php');
    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    $base = new base();
    $user = new user($db);
    
    if($user->rights($user->id) > 0) $base->redirect('index.php');
    
    
    //players to show per page
    $per_page = 1;

    $db->processQuery("SELECT * FROM `feedback` WHERE `solved` = 0 AND `response` = ''", array());
    $amount = $db->getRowCount();
    
    //number of pages
    $pages = ceil($amount/$per_page);

    $page = (!ctype_digit($_GET['page']) || $_GET['page'] < 1 || $_GET['page'] > $pages) ? 1 : $_GET['page'];

    $start = (($page-1)*$per_page);
    
    if($_POST['id']){
        if($_POST['delete'] == 'Delete'){
            $db->processQuery("DELETE FROM `feedback` WHERE `id` = ? LIMIT 1", array($_POST['id']));
        }else{
            $db->processQuery("UPDATE `feedback` SET `response` = ? WHERE `id` = ? LIMIT 1", array(
                $_POST['response'],
                $_POST['id']
            ));
        }
    }
    
    $feedback = $db->processQuery("SELECT `id`,`data`,`feedback` FROM `feedback` WHERE `solved` = 0 AND `response` = '' ORDER BY `id` ASC LIMIT $start,$per_page", array(), true);
?>

<hr>
<center>
<form action="respond.php" method="POST">
    <table>
        <?
            foreach($feedback as $r){
                echo '<input type="hidden" name="id" value="'. $r['id'] .'">';
                echo '<tr style="background-color:#676766;color:#C6A500;"><td>FEEDBACK #'. $r['id'] .'</td></tr>';
                echo '<tr><td>'. nl2br(htmlentities($r['feedback'])) .'</td></tr>';
                if(!empty($r['data']))
                    echo '<tr><td><input type="text" length="200" value="'. $r['data'] .'"></td></tr>';
                echo '<tr><td><textarea name="response" style="height:250px;width:100%"></textarea></td></tr>';
                echo '<tr><td><input type="submit" name="delete" value="Delete"><input type="submit" name="respond" value="Respond"></td></tr>';
            }
        ?>
    </table>
</form>
<center>
    <?php
        if($page > 1) echo '<td><a href="respond.php?page='. ($page-1) .'"><< Prev</a></td>';
    ?>
    &nbsp;&nbsp;&nbsp;
    <?php
        if($page < $pages) echo '<td><a href="respond.php?page='. ($page+1) .'">Next >></a></td>';
    ?>
</center>