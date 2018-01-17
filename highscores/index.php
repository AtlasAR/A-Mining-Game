<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/highscores.php');
    include('../structure/user.php');
    include('../structure/base.php');

    
    $db = new database($db_host, $db_name, $db_user, $db_password);
    $hs = new highscores($db);
    $user = new user($db);
    $base = new base();
    
    if($user->groupID)
        $groupName = $hs->groupNameByID($user->groupID);
    
    //moderation
    if(isset($_GET['ban']) && $user->rights($user->id) > 1){
        $data = $db->processQuery("SELECT `ip`,`userid` FROM `highscores` WHERE `id` = ? LIMIT 1", array($_GET['ban']), true);
                $db->processQuery("UPDATE `users` SET disabled = 1 WHERE `ip` = ? OR `id` = ? LIMIT 1", array($data[0]['ip'], $data[0]['userid']));
                $db->processQuery("DELETE FROM `highscores` WHERE `ip` = ?", array($data[0]['ip']));
    }
    
    if(isset($_GET['group']) && !$hs->groupExistsByID($_GET['group'])) $base->redirect('index.php?page=1');
    
    if(isset($_GET['group'])){
        $number_saves = $db->processQuery("SELECT * FROM `highscores`,`users` WHERE (users.`id` = highscores.`userid`) AND `group` = ?", array($_GET['group']));
        $number_saves = $db->getRowCount();
    }else{
        $number_saves = $db->processQuery("SELECT * FROM `highscores`", array());
        $number_saves = $db->getRowCount();
    }

    //players to show per page
    $per_page = 20;

    //number of pages
    $pages = ceil($number_saves/$per_page);
  
    $page = (!ctype_digit($_GET['page']) || $_GET['page'] < 1 || $_GET['page'] > $pages) ? 1 : $_GET['page'];
    
    if(isset($_GET['user'])){
        $query_user = $db->processQuery("SELECT `id` FROM `users` WHERE `username` = ? LIMIT 1", array($_GET['user']), true);
        $search_userid = $query_user[0]['id'];
            
        if($db->getRowCount() > 0){
            $rank = $hs->getRank($search_userid);
        
            if($rank > 0)
                $page = ceil($rank/$per_page);
        }
    }
    
    $start = (($page-1)*$per_page);

    $next = ($page < $pages) ? ($page+1) : $page;
    $prev = ($page > 1) ? ($page-1) : 1;
    
    //what theme are they using?
    switch($_COOKIE['theme']){
        default:
            $theme = 'default';
            break;
        case 'nan':
            $theme = 'nan';
            break;
    }
?>

<html>
<head>
    <title>Highscores</title>
    <link rel="stylesheet" name="includeStyle" type="text/css" href="../css/highscores_<?=$theme?>.css?v=13">
    <script type="text/javascript" src="../resources/jquery2.js"></script>
    <script type="text/javascript" src="../resources/jquery-ui.js"></script>
    <?php 
        if(strtolower($_GET['user']) == 'dev'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/rasputin.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'cretin'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/imperialmarch.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                    
                    $(document).ready(function(){
                        $('body').css('background-color', 'black');
                        $('body').css('color', 'red');
						$('#highscores *').fadeOut(7000);
						
						setTimeout(stormPooper, 7000);
						
						var clicks = 0;
						function stormPooper(){
							$('#highscores').append('<p style="margin:0 auto;"><img name="stormpooper" src="stormpooper.gif" /><br/><font color="white">Storm Pooper, Lvl <span name="level">1</span></font></p>');
							
							$(document).on('click', '#highscores img[name="stormpooper"]', function(){
								clicks++;
								$(this).css({
									'width' : $(this).width()+1+'px',
									'height' : $(this).height()+1+'px'
								});
								$('span[name="level"]').text(clicks/10);
							});
						}
                    });
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'kim'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/kimnewsong.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'runedaegun'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/tiger.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'buttsecks999'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/loadsamoney.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'ben'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/trollol.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'nan'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/igetoff.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'zestorax'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/zestorax.mp3');
                    audioElement.volume = 0.6;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'nomi'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/nomi.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'sameezy'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/sameezy.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }elseif(strtolower($_GET['user']) == 'mikegarciagm'){
            ?>
                <script type="text/javascript">
                    var audioElement = document.createElement('audio');
                    audioElement.setAttribute('src', '../sound/mikegarciagm.mp3');
                    audioElement.volume = 0.8;
                    audioElement.play();
                </script>
            <?php
        }
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $('a[name="ban"]').click(function(e){
                if(!confirm("Are you sure you wish to ban this user?"))
                    e.preventDefault();
            });
            
            $('a[name="leavegroup"]').click(function(){
                if(confirm("Are you sure to wish to leave the group you are currently in?")){
                    $.ajax({
                        url : 'highscores_leave.php?action=leave',
                        success: function(){
                            location.reload();
                        }
                    })
                }
            });
            
            $('a[name="disbandgroup"]').click(function(){
                if(confirm("Are you really sure you want to disband the group? Everyone in the group will automatically be kicked out.")){
                    if(confirm("So you're definitely sure?")){
                        $.ajax({
                            url : 'highscores_leave.php?action=disband',
                            success: function(){
                                location.reload();
                            }
                        });
                    }
                }
            });
            
            $('a[name="cancelrequest"]').click(function(){
                if(confirm("Are you sure you want to cancel the group request?")){
                    if(confirm("So you're definitely sure?")){
                        $.ajax({
                            url : 'highscores_cancelrequest.php',
                            success: function(){
                                location.reload();
                            }
                        });
                    }
                }
            });
            
            $('tr[name|="user"]').click(function(){
                var id = $(this).attr('name').split('-')[1];
                $('tr[name="gamestats-'+ id +'"]').toggle();
            });
            
            $(document).on('change', 'select[name="styleSelector"]', function(){
                var theme = $(this).val();
                
                $('link[name="includeStyle"]').attr('href', '../css/highscores_'+ theme +'.css');
                $.ajax({
                    url: 'change_theme.php',
                    type: 'POST',
                    data : {theme : theme}
                });
            });
            
            devEffect();
            function devEffect(){
                $('p[name="devwashere"]').effect('bounce', { times: 3 }, 'slow', devEffect);
            }
        });
    </script>
    <style>
        .yoloswag420{
            background-image:url('yes.png');
            background-repeat:repeat;
            color:blue;
            font-weight:bold;
        }
    </style>
</head>

<body>
    <div id="container">
        <?php
            if(isset($_GET['group']))
                echo '<h2><font color="green">'. htmlentities($hs->groupNameByID($_GET['group'])) .'</font>\'s group Highscores</h2>';
            else
                echo '<h2>Highscores</h2>';
        ?>
        <div id="left">
            <span style="float:left;">
                Select theme: 
                <select name="styleSelector">
                    <option value="default" <?=(($theme == 'default') ? 'selected="selected"' : '')?>>Default</option>
                    <option value="nan" <?=(($theme == 'nan') ? 'selected="selected"' : '')?>>Sleek Dark</option>
                </select>
            </span>
            <br/><br/>
            <div class="clear"></div>
            <div class="leftbox">
                <p class="title">GROUP HIGHSCORES</p>
                <p>Want to see which group is #1?<br/><a href="highscores_groups.php">Group Highscores</a></p>
            </div>
            
            <div class="leftbox">
                <p class="title">SEARCH</p>
                <p>
                    Find another user's rank. <font size="1">stalker</font><br/>
                    <form action="index.php">
                        <input type="text" name="user" value="<?=htmlentities($_GET['user'])?>"> <input type="submit" value="Go">
                    </form>
                </p>
            </div>
            
            <?php
                if(!$user->isLoggedIn){
                    ?>
                        <div class="leftbox">
                            <p class="title">Register</p>
                            <p>To participate in the highscores, you're required to create an account. <a href="../account/register.php">Create Account</a></p>
                        </div>
            
                        <div class="leftbox">
                            <p class="title">Login</p>
                            <p>Already have an account? <a href="../account/login.php">Login</a></p>
                        </div>
                    <?php
                }else{
                    ?>
                        <div class="leftbox">
                            <p class="title">MY RANK</p>
                            <?php
                                $rank = $hs->getRank($user->id);
                                
                                if($rank == 0){
                                    echo '<p>You are not ranked.</p>';
                                }else{
                                    if($rank == 666){
                                         echo '<p style="color:red;"><img src="devil_pitchfork.gif" /> You are ranked #'. $rank .'.</p>';
                                    }else{
                                         echo '<p>You are ranked #'. $rank .'.</p>';
                                    }
                                }
                            ?>
                        </div>
                    <?php
                    
                    if($user->ownsGroup()){
                        ?>
                            <div class="leftbox">
                                <p class="title">MANAGE GROUP</p>
                                <p>You have a group! You can start inviting people now. <a href="highscores_manage.php">Manage Group</a></p>
                            </div>

                            <div class="leftbox">
                                <p class="title">DISBAND GROUP</p>
                                <p>Disbanding the group will delete your group and kick everyone from the group. <a href="#" name="disbandgroup">Disband Group</a></p>
                            </div>
                        <?php
                    }
                    
                    if($user->groupID){
                        ?>
                            <div class="leftbox">
                                <p class="title"><?=htmlentities($groupName)?>'s worth</p>
                                <p><?=htmlentities($groupName)?> currently has a group total of $<?=number_format($hs->groupTotalMoneyEarned($user->groupID))?></p>
                            </div>
            
                            <div class="leftbox">
                                <p class="title"><?=htmlentities($groupName)?></p>
                                <?php
                                    if(!isset($_GET['group']))
                                        echo ' <p><a href="index.php?group='.$user->groupID.'">View group-only highscores</a></p>';
                                    else
                                        echo '<p><a href="index.php">Back to main</a></p>';
                                ?>
                            </div>
            
                            <div class="leftbox">
                                <p class="title">LEAVE GROUP</p>
                                <p><a href="#" name="leavegroup">Leave Group</a></p>
                            </div>
                        <?php
                    }else{
                        //check if they have a group request
                        $query = $db->processQuery("SELECT `group` FROM `group_requests` WHERE `userid` = ? LIMIT 1", array($user->id), true);

                        if($db->getRowCount() > 0){
                            ?>
                                <div class="leftbox">
                                    <p class="title">REQUEST STATUS</p>
                                    <p>Your request to join the group <b><?=htmlentities($hs->groupNameByID($query[0]['group']))?></b> is currently pending. <a href="#" name="cancelrequest">Cancel Request</a></p>
                                </div>

                            <?php
                        }else{
                            ?>
                                <div class="leftbox">
                                    <p class="title">CREATE GROUP</p>
                                    <p>Want to create a group that only invited people can post their scores in? <a href="highscores_creategroup.php">Create Group</a></p>
                                </div>
            
                                <div class="leftbox">
                                    <p class="title">JOIN GROUP</p>
                                    <p>Want to join a group? <a href="highscores_joingroup.php">Find Group</a></p>
                                </div>
                            <?php
                        }
                    }
                }
            ?>
            
            <div class="leftbox" style="text-align:center;">
                <p class="title">AD</p>
                <p>
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Side ad -->
                    <ins class="adsbygoogle"
                        style="display:inline-block;width:120px;height:600px"
                        data-ad-client="ca-pub-6464976486754613"
                        data-ad-slot="1703589603"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </p>
            </div>
        </div>
        <div id="highscores" class="dark">
            <table id="highscores_table" cellpadding="10" cellspacing="0">
                <tr class="dirtBlocks"><th>#</th><th>Username</th><th>Money Earned</th><th>Pickaxe</th><th>Worker OPM</th><th>Scientists</th><th>Last Update</th><th>Group</th></tr>
                <?php
                        //list top saves
                        if(isset($_GET['group']) && $hs->groupExistsByID($_GET['group'])){
                            $saves = $db->processQuery("SELECT 
                                                        hs.`id`,
                                                        hs.`userid`,
                                                        hs.`datetime`,
                                                        hs.`money_earned`,
                                                        hs.`pickaxe`,
                                                        hs.`worker_opm`,
                                                        hs.`scientists`,
                                                        hs.`data`,
                                                        users.`donations`,
                                                        groups.`id` as groupID,
                                                        groups.`color` as groupColor,
                                                        groups.`groupname`
                                                        FROM `highscores` as hs
                                                        LEFT JOIN `users` ON hs.`userid` = users.`id`
                                                        LEFT JOIN `groups` ON groups.`id` = ?
                                                        WHERE users.`group` = ?
                                                        ORDER BY `money_earned` DESC LIMIT $start,$per_page", array($_GET['group'], $_GET['group']), true);
                        }else{
                            $saves = $db->processQuery("SELECT 
                                                        hs.`id`,
                                                        hs.`userid`,
                                                        hs.`datetime`,
                                                        hs.`money_earned`,
                                                        hs.`pickaxe`,
                                                        hs.`worker_opm`,
                                                        hs.`scientists`,
                                                        hs.`data`,
                                                        users.`donations`,
                                                        groups.`id` as groupID,
                                                        groups.`color` as groupColor,
                                                        groups.`groupname`
                                                        FROM `highscores` as hs
                                                        LEFT JOIN `users` ON hs.`userid` = users.`id`
                                                        LEFT JOIN `groups` ON users.`group` = groups.`id`
                                                        ORDER BY `money_earned` DESC LIMIT $start,$per_page", array(), true);
                        }
                
                        $i = 1;
                        foreach($saves as $save) {
                            $rank = ($start+$i);
                            
                            //get group details
                            if($save['groupID'] > 0){
                                $name = htmlentities($save['groupname']);
                                
                                if(!empty($save['groupColor']))
                                    $group = '<b><font color="'. $save['groupColor'] .'">'.$name.'</font></b>';
                                else
                                    $group = $name;
                                
                                if($save['groupID'] == 908)
                                    $group = '<span name="dev">'. $group .'</span>';
                                elseif($save['groupID'] == 232)
                                        $group = '<img src="thedevs.gif" width="32" height="40" /><br/><font size="1">The Devs</font>';
                                elseif($details[0]['id'] == 1096)
                                    $name = '<span style="display:inline-block;"><img src="4chan.png" style="vertical-align:text-bottom;" width="28" height="16">&nbsp;'. $name .'</span>';
                                elseif($details[0]['id'] == 1053)
                                    $name = '<span style="display:inline-block;"><img src="crisisx.png" style="vertical-align:text-bottom;">&nbsp;'. $name .'</span>';
                            }else{
                                $group = false;
                            }
                            
                            $data = json_decode($save['data'], true);
                            
                            $workersHired = 0;
                            $workers = $data['employed'];
                            
                            foreach($workers as $worker)
                                $workersHired += $worker[0];
                            
                            $class = '';
                            if($user->id == $save['userid']){
                                $class = 'green ';
                            }elseif(isset($_GET['user']) && $save['userid'] == $search_userid){
                                $class = 'found ';
                            }
                            
                            $extra = '';
                            switch($rank){
                                case 1:
                                    $extra = '<img src="trophy.png">&nbsp;';
                                    break;
                                case 2:
                                    $extra = '<img src="trophy_silver.png">&nbsp;';
                                    break;
                                case 3:
                                    $extra = '<img src="trophy_bronze.png">&nbsp;';
                                    break;
                            }
                            
                            //level
                            $level = floor((sqrt(625+100*$data['exp'])-25)/50);
                            
                            if($data['finishedResearch']['masterResearcher'] != null)
                                $extra .= '<img src="trophy_masterresearcher.png">&nbsp;';
                            
                            if($save['donations'] >= 15)
                                $extra = '<img src="../css/images/donorIcon2.png">&nbsp;'.$extra;
                            else if($save['donations'] >= 5)
                                $extra = '<img src="../css/images/donorIcon.png">&nbsp;'.$extra;
                            
							
							//if($save['userid'] == 17978)
							//	echo '<tr><td colspan="8"><p name="devwashere" style="border:1px solid #780deb;background-color:#ead8fd;color:#3e077a;text-align:center;padding:6px;vertical-align:middle;"><img src="http://cursors1.totallyfreecursors.com/thumbnails/cookie-monster.gif" width="32" height="32"> Meet the RSCharts mascot.</p></td></tr>';
								
							?>
                                <tr name="user-<?=$save['userid']?>" class="<?=$class?>clickable">
                                    <td><?=$rank?></td>
                                    <td>
                                            <?= $extra.htmlentities($user->getUsernameById($save['userid']))?>
                                    </td>
                                    <td>$<?=number_format(htmlentities($save['money_earned']))?></td>
                                    <td><?=htmlentities($save['pickaxe'])?></td>
                                    <td><?=number_format(htmlentities($save['worker_opm']))?></td>
                                    <td><?=number_format(htmlentities($save['scientists']))?></td>
                                    <td><?=$save['datetime']?></td>
                                    <td>
                                        <?php
                                            if($group)
                                                echo $group;
                                            else
                                                echo 'N/A';
                                        ?>
                                    </td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td><td colspan="7">Money on hand: $<?=htmlentities(number_format($data['money']))?></td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td><td colspan="7">Money per tick: $<?=htmlentities(number_format($data['moneyPerTick']))?></td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td><td colspan="7">Times WorkerOPM has been researched: <?=htmlentities(number_format($data['workerOPMResearch']))?></td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td><td colspan="7">Worker Happiness: <?=htmlentities($data['workerHappiness'])?></td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td>
                                    <td colspan="7">
                                        Workers hired: <?=  htmlentities(number_format($workersHired))?>
                                    </td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td>
                                    <td colspan="7">
                                        <?php
                                            $xx = 1;
                                            foreach($workers as $name => $employed){
                                                switch($name){
                                                    case 'miner':
                                                        $name = 'Dedicated Miner';
                                                        break;
                                                    case 'miner2':
                                                        $name = 'Experienced Miner';
                                                        break;
                                                    case 'heavenlyminer':
                                                        $name = 'Heavenly Miner';
                                                        break;
                                                    case 'hellminer':
                                                        $name = 'Hell Miner';
                                                        break;
                                                    case 'enderminer':
                                                        $name = 'End Miner';
                                                        break;
                                                    case 'capturedminion':
                                                        $name = 'Captured Minion';
                                                        break;
                                                    case 'gb_capturedminion':
                                                        $name = 'GB Captured Minion';
                                                        break;
                                                }
                                                
                                                echo ucwords($name).': <b>'.number_format($employed[0]).'</b>';
                                                if($xx < count($workers))
                                                    echo ', ';
                                                
                                                $xx++;
                                            }
                                        ?>
                                    </td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td><td colspan="7">Boss currency: <?=htmlentities(number_format($data['bossCurrency']))?></td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td><td colspan="7">Bosses defeated: <?=htmlentities(number_format($data['bossesDefeated']))?></td>
                                </tr>
                                <tr name="gamestats-<?=$save['userid']?>" class="green hidden">
                                    <td><b>></b></td>
                                    <td colspan="7">
                                        Character level:
                                        <?php
                                            if($level > 100)
                                                echo '100 <b>('. $level .')</b>';
                                            else
                                                echo $level;
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            
                            $i++;
                        }
                ?>
            </table>
            
            <table cellpadding="3" style="margin-top:20px;">
                <tr>
                    <?php
                        $baseURL = (isset($_GET['group'])) ? 'index.php?group='. $_GET['group'] .'&' : 'index.php?';
                    
                        if($page > 1) echo '<td><a href="'. $baseURL .'page='. ($page-1) .'"><< Prev</a></td>';

                        $pagelist = '';
                        for($i = 1; $i <= $pages; $i++){
                            if($page==$i)
                                $pagelist .= ' <a href="'. $baseURL .'page='. $i .'"><b>'.$i.'</b></a> ';
                            else
                                $pagelist .= ' <a href="'. $baseURL .'page='. $i .'">'.$i.'</a> ';
                        }
                        
                        echo '<td>'. $pagelist . '</td>';

                        if($page < $pages) echo '<td><a href="'. $baseURL .'page='. ($page+1) .'">Next >></a></td>';
                    ?>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-44010885-1', 'rscharts.com');
    ga('send', 'pageview');

    </script>
</body>    
</html>