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
    
    $number_saves = $db->processQuery("SELECT * FROM `highscores` as hs LEFT JOIN `users` ON (hs.`userid` = users.`id` AND users.`group` != 0) GROUP BY users.`group`", array());
    $number_saves = $db->getRowCount();

    //players to show per page
    $per_page = 20;

    //number of pages
    $pages = ceil($number_saves/$per_page);

    $page = (!ctype_digit($_GET['page']) || $_GET['page'] < 1 || $_GET['page'] > $pages) ? 1 : $_GET['page'];

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
    <title>Group Highscores</title>
    <link rel="stylesheet" type="text/css" href="../css/highscores_<?=$theme?>.css?v=14">
    <script type="text/javascript" src="../resources/jquery2.js"></script>
    <script type="text/javascript" src="../resources/jquery-ui.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
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
            
            devEffect();
            function devEffect(){
                $('span[name="dev"]').effect('bounce', { times: 3 }, 'slow', devEffect);
            }
        });
    </script>
</head>

<body>
    <div id="container">
        <h2>Group Highscores</h2>
        <div id="left">
            <div class="leftbox">
                <p class="title">PLAYER HIGHSCORES</p>
                <p>Want to see which player is #1?<br/><a href="index.php">Player Highscores</a></p>
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
                                    <p class="title">JOIN GROUP</p>
                                    <p>Want to join a group? <a href="highscores_joingroup.php">Find Group</a></p>
                                </div>
                            <?php
                        }
                    }
                }
            ?>
            
            <!--<div class="leftbox" style="text-align:center;">
            <p class="title">AD</p>
                <p>
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>-->
                    <!-- Side ad -->
           <!--         <ins class="adsbygoogle"
                        style="display:inline-block;width:120px;height:600px"
                        data-ad-client="ca-pub-6464976486754613"
                        data-ad-slot="1703589603"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </p>
            </div>-->
        </div>
        
        <div style="float:left;margin-bottom:5px;">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- Leaderboard Ad -->
            <ins class="adsbygoogle"
                style="display:inline-block;width:728px;height:90px"
                data-ad-client="ca-pub-6464976486754613"
                data-ad-slot="4016097600"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
        
        <div id="highscores">
            <table cellpadding="10">
                <th>#</th><th>Total Money Earned</th><th>Total Worker OPM</th><th>Scientists</th><th>Members</th><th>Group</th>
                <?php
                        $groups = $db->processQuery("
                                SELECT
                                COUNT(*) as members,
                                SUM(hs.`money_earned`) as earned,
                                SUM(hs.`worker_opm`) as opm,
                                SUM(hs.`army_strength`) as power,
                                SUM(hs.`scientists`) as scientists,
                                users.`group` as groupID
                                FROM `highscores` as hs LEFT JOIN `users` ON (hs.`userid` = users.`id` AND users.`group` != 0)
                                WHERE users.`group` != 0
                                GROUP BY users.`group`
                                ORDER BY SUM(hs.`money_earned`)
                                DESC LIMIT $start,$per_page", array(), true);
                
                        $i = 1;
                        foreach($groups as $group) {
                            $rank = ($start+$i);
                            
                            //get group details
                            $details = $db->processQuery("SELECT `color`,`id` FROM `groups` WHERE `id` = ? LIMIT 1", array($group['groupID']), true);
                            $name = htmlentities($hs->groupNameByID($group['groupID']));

                            if(!empty($details[0]['color']))
                                $name = '<b><font color="'. $details[0]['color'] .'">'.$name.'</font></b>';
                            
                            if($details[0]['id'] == 232 || $details[0]['id'] == 908)
                                $name = '<span name="dev">'. $name .'</span>';
                            elseif($details[0]['id'] == 1096)
                                $name = '<span style="display:inline-block;"><img src="4chan.png" style="vertical-align:text-bottom;" width="28" height="16">&nbsp;'. $name .'</span>';
                            elseif($details[0]['id'] == 1053)
                                    $name = '<span style="display:inline-block;"><img src="crisisx.png" style="vertical-align:text-bottom;">&nbsp;'. $name .'</span>';
                            
                            /*if($details[0]['id'] == 145){
                                ?>
                                <tr>
                                    <td colspan="7"><div style="border:2px solid #9121a4;background-color:#ebc1f2;padding:5px;color:#46104f;text-align:center;">&#9661; JOIN REDDIT'S GROUP TODAY <span style="font-size:2px;">if you want to lose.</span> &#9661;</div></td>
                                </tr>
                                <?php
                            }*/
                            
                            ?>
                                <tr>
                                    <td><?=$rank?></td>
                                    <td>$<?=number_format(htmlentities($group['earned']))?></td>
                                    <td><?=number_format(htmlentities($group['opm']))?></td>
                                    <td><?=number_format(htmlentities($group['scientists']))?></td>
                                    <td><?=number_format($group['members'])?></td>
                                    <td style="width:164px;"><?='<a href="index.php?group='. $details[0]['id'] .'" style="text-decoration:none;">'.$name.'</a>'?></td>
                                </tr>
                            <?php
                            
                            $i++;
                        }
                ?>
            </table>
            
            <table cellpadding="3" style="margin-top:20px;">
                <tr>
                    <?php
                        $baseURL = 'highscores_groups.php?';
                    
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