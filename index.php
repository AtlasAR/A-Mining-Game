<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>A GAME</title>
        <script type="text/javascript" src="resources/jquery2.js"></script>
        <script type="text/javascript" src="resources/jquery-ui.js"></script>
        <script type="text/javascript" src="resources/base64.js"></script>
        <script type="text/javascript" src="resources/jQueryRotateCompressed.js"></script>
        <script type="text/javascript" src="resources/socket.io.js"></script>
        <script type="text/javascript" src="game/client.js?v=37"></script>
        <link rel="stylesheet" type="text/css" href="css/style.css?v=19">
    </head>
    <body>
        <div id="updatebar" class="hidden">
            <p>An update is coming! The server will disconnect in <span name="seconds">60</span> seconds ... <a href="changelog.txt" target="_blank">see what's new</a>.</p>
        </div>

        <div id="container">
            <div id="save">
                <img src="game/img/icons/save.png" width="32" height="32"> Saved: <span name="time">never</span>
                <p name="gamesave_actions">
                    <span name="notLoggedIn">
                        <a href="#" id="create_account" style="color:orangered;">CREATE ACCOUNT</a>
                        <br/><br/>
                        <a href="account/login.php" style="color:orangered;">LOGIN</a>
                        <br/><br/>
                    </span>
                    <a href="forum/index.php" target="_blank" style="color:orangered;">FORUM</a>
                    <br/><br/>
                    <a href="highscores/index.php" style="color:green;" target="_blank">HIGHSCORES</a>
                    <br/><br/>
                    <a href="account/index.php" style="color:green;" target="_blank">ACCOUNT CENTER</a>
                    <br/><br/>
                    <a href="#" style="color:green;" id="faq">F.A.Q.</a>
                    <br/><br/>
                    <a href="#" style="color:green;" id="ore_prices">ORE PRICES</a>
                    <!--
                    <hr>
                    Players online: <span name="player_count">0</span>
                    <br/>
                    <font size="1">updates every 5 minutes</font>
                    -->
                    <div id="donation_goal" style="margin:15px 0px;">
                        <img src="game/img/icons/loading2.gif">
                    </div>
                    <a href="donate/index.php"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" /></a>
                    <br/>

                    <font size="1">keeps this game alive (covers hosting cost)</font>
                    <hr>
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Game ad -->
                    <ins class="adsbygoogle"
                        style="display:inline-block;width:250px;height:250px"
                        data-ad-client="ca-pub-6464976486754613"
                        data-ad-slot="7970449202"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </p>
            </div>
            <div id="gamecontainer">
                <div id="quickdetails" class="textleft center">
                    <table cellspacing="6">
                        <tr>
                            <td><img src="game/img/icons/moneyBag.png" width="20" height="32"></td><td id="money_display"></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><img src="game/img/icons/bossCurrency.png" width="20" height="32"></td><td id="bc_display" class="displaytext">0</td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><img src="game/img/icons/vault.png" width="20" height="32"></td><td id="vault_display" class="displaytext"></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td id="zombie_icon"><img src="game/img/npc/zombie.png" width="32" height="32"></td><td id="zombie_display" class="displaytext"></td>
                            <td id="chikolio_icon" class="hidden"><img src="game/img/npc/chicken.png" width="32" height="32"></td><td id="chikolio_display" class="hidden displaytext"></td>
                            <td id="underlord_icon" class="hidden"><img src="game/img/icons/zombiepigman.png" width="32" height="32"></td><td id="underlord_display" class="hidden displaytext"></td>

                            <td id="enderboss_icon" class="hidden">
                                <img src="game/img/npc/enderman_face.png" width="32" height="32">
                            </td>
                            <td id="enderboss_display" class="hidden displaytext"></td>
                            <td id="enderboss_health" class="hidden"></td>

                            <td name="boss_separation">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><img src="game/img/icons/portalparts_lit.png" width="32" height="32"></td><td id="portalparts_display" class="displaytext"></td><td></td>
                            <td class="hidden">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td name="globalBoss" class="globalbossevent">
                                <img src="game/img/icons/globalBoss.png" style="float:left;margin-right:3px;" />
                                Time until<br/>
                                <span name="timeRemaining">
                                    PLAYING
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="margin-bottom:80px;"></div>
                <div name="upperContainer">
                    <div id="upgrades_container">
                        <p class="title">Upgrades</p>
                        <div id="upgrades">
                            <div id="antimatter_upgrade_box" class="hidden"></div>
                            <div id="finalpickaxe_upgrade_box" class="hidden"></div>
                            <div id="enderpickaxe_upgrade_box" class="hidden"></div>
                            <div id="hellpickaxe_upgrade_box" class="hidden"></div>
                            <div id="pickaxe_upgrade_box" class="hidden"></div>
                            <div id="vault_upgrade_box" class="hidden"></div>
                            <div id="autopilot_upgrade_box" class="hidden"></div>
                            <div id="golem_upgrade_box" class="hidden"></div>
                            <div id="witch_upgrade_box" class="hidden"></div>
                            <div id="partways_upgrade_box" class="hidden"></div>
                            <div id="sword_upgrade_box" class="hidden"></div>
                            <div id="autowage_upgrade_box" class="hidden"></div>
                            <div id="buildportal_upgrade_box" class="hidden"></div>
                            <div id="igniteportal_upgrade_box" class="hidden"></div>
                            <div id="lawmaker_upgrade_box" class="hidden"></div>
                        </div>
                    </div>

                    <div id="mining_container">
                        <button class="button_mine" name="mine">START MINING</button>

                        <div id="mine_box">
                            <div name="pickaxe_icon_holder" class="left max50">
                                <img src="" width="91" height="92" id="pickaxe" />
                                <p class="textleft">
                                    <span id="pickaxe_var_name" style="font-weight:bold;"></span><br/>
                                    Max ores per: <span id="pickaxe_var_max"></span><br/>
                                    Sharpness: <span id="pickaxe_var_sharpness"></span><br/>
                                    Speed: <span id="pickaxe_var_speed"></span><br/>
                                    Drop Chance: <span id="pickaxe_var_dropchance"></span><br/>
                                    Animation: <span id="pickaxe_var_animation"><input type="checkbox" name="animation_toggle" checked="checked"></span><br/>
                                    <span id="autopilot_option" class="hidden">Autopilot: <input type="checkbox" name="autopilot_enabled" checked="checked"></span>
                                </p>
                            </div>

                            <div name="ores_collected" style="text-align:left;" class="right max50">

                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <div id="shrine" class="hidden">
                        <h2>Shrine health: <span name="health">0</span>/500000</h2>
                    </div>

                    <div id="orbs" class="hidden">
                        <div style="float:left;margin-right:30px;">
                            <img src="game/img/icons/orb.png" name="orb-1"><br/>
                            <div class="healthbar" name="orb-1" style="width:65px;"><span style="width:0;"></span></div>
                            <font size="1"><span name="orbhp-1">1000000</span> HP</font>
                            <br/>
                            <button name="attackorb-1">Attack</button>
                        </div>
                        <div style="float:left;margin-right:30px;">
                            <img src="game/img/icons/orb.png" name="orb-2"><br/>
                            <div class="healthbar" name="orb-2" style="width:65px;"><span style="width:0;"></span></div>
                            <font size="1"><span name="orbhp-2">1000000</span> HP</font>
                            <br/>
                            <button name="attackorb-2">Attack</button>
                        </div>
                        <div style="float:left;">
                            <img src="game/img/icons/orb.png" name="orb-3"><br/>
                            <div class="healthbar" name="orb-3" style="width:65px;"><span style="width:0;"></span></div>
                            <font size="1"><span name="orbhp-3">1000000</span> HP</font>
                            <br/>
                            <button name="attackorb-3">Attack</button>
                        </div>
                    </div>

                    <div id="enderbossFight" class="hidden">
                        <div style="float:left;">
                            <img src="game/img/npc/enderman_face.png" width="275" height="130"><br/>
                            <div class="healthbar large" name="health" style="width:275px;"><span style="width:0;"></span></div>
                            <br/>
                            <button name="attack_enderboss" style="width:275px">Attack</button>
                        </div>
                    </div>

                    <div id="randomBossPortal" class="hidden">
                        <div style="float:left;">
                            <span name="timer" style="font-size:19px;" class="hidden">You can summon a new boss in 0 minutes.</span>
                            <br/>
                            <br/>
                            <a href="#" name="bossPortal"><img src="game/img/icons/bossPortal.png"></a>
                        </div>
                    </div>

                    <div id="randomBossArea" class="hidden">
                        <div style="float:left;">
                            <p style="text-align:left;font-size:18px;">
                                Time remaining: <span name="timer">0</span><br/>
                                Waves: <span name="wave">0/0</span><br/>
                            </p>
                            <h2 name="bossName"></h2>
                            <hr>
                            <img src="game/img/npc/randomboss_npc.png" width="275" height="130"><br/>
                            <div class="healthbar large" name="health" style="width:275px;"><span style="width:0;"></span></div>
                            <br/>
                            <button name="attack_randomboss" style="width:275px">Attack</button>
                            <br/>
                            <br/>
                            <textarea name="battelog" rows="8" cols="33" class="enderBox"></textarea>
                        </div>
                    </div>

                    <div id="vault_container">
                        <div id="vault">
                            <p class="title">Your Vault</p>
                            <p class="title hidden" name="full" style="color:red;">(FULL)</p>
                            <table name="vault" cellpadding="4" class="center">
                                <tr><th>Ore</th><th>Amount</th><th>Worth</th></tr>
                            </table>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                
                <div name="globalBossContainer" class="hidden">
                    <div name="lobby">
                        <div style="float:left;margin-right:30px;">
                            <img src="game/img/icons/globalBoss_big.png" />
                            <br/>
                            <p class="globalbossGreen" style="padding:3px;">
                                <b>Players in Lobby:</b> <span name="lobbyPlayerCount">0</span><br/>
                                <b>Match starts in:</b> <span name="lobbyCountdown">0</span>
                            </p>
                            <div name="lobbyActivity" class="globalbossGreen" style="padding:3px;margin-bottom:10px;"></div>
                        </div>
                        <div style="float:left;">
                            <h2>Global Boss event!</h2>
                            
                            <div style="width:400px;">
                                <p>Welcome to the Global Boss event, where all players who chose to can participate.</p>
                                <p>In this event, all players work together to kill one boss. Right now the global boss event is brand new, and hasn't
                                really had much content added to it. In the future, I hope this event can be expanded to have more things to do! You click to attack the boss.</p>
                                <p><b>Rewards?</b><br/>The global boss event will drop items just as random bosses would. It also gives you experience
                                and boss coins; however, it may include items in its drop that random bosses do not.<br/><br/>In addition, if you deal at least
                                1,000 damage to the global boss, you will receive a "GB Minion" worker.</p>
                            </div>
                        </div>
                    </div>
                    <div name="fightArea" class="hidden" style="text-align:left;">
                        <center><h2><span name="name">Global Boss</span> (<span name="health_current">0</span>/<span name="health_total">0</span>)</h2></center>
                        <div class="globalBossBar" name="healthbar" style="width:100%;text-align:left;"><span style="width:0%;"></span></div>
                        <img name="globalBossImg" src="game/img/npc/randomboss_npc.png" style="margin-top:7px;width:100%;height:250px;" />
                        <div name="myDetails">
                            <b>Damage Dealt: <span name="damage">0</span></b>
                        </div>
                        
                        <div name="players" style="margin-top:30px;"></div>
                    </div>
                    <div class="clear"></div>
                </div>

                <div id="employment">
                    <hr>
                    <p name="tabs" class="title"><a href="#" name="tab-character/div" class="selected">Character</a>
                        &nbsp;&nbsp; <a href="#" name="tab-workers/table">Workers</a>
                        &nbsp;&nbsp; <a href="#" name="tab-research/div">Research</a> &nbsp;&nbsp;
                        <a href="#" name="tab-soldiers/table">Army</a> &nbsp;&nbsp; <a href="#" name="tab-attack/div">Attack</a>
                        &nbsp;&nbsp; <a href="#" name="tab-portal/div">Portal</a> <a href="#" name="tab-village/div" class="hidden">Village</a> &nbsp;&nbsp;
                        <a href="#" name="tab-stats/table">Stats</a> <span name="admin" class="hidden">&nbsp;&nbsp; <a href="#" name="tab-admin/div">Admin</a></span>
                    </p>

                    <div name="research" type="tab" style="margin-top:20px;" class="hidden">
                        <div name="1">
                            <button name="construct_lab" style="padding:30px;">Construct a research lab for $100,000,000</button>
                        </div>
                        <div name="lab" class="hidden">
                            <table class="left">
                                <tr>
                                    <td>
                                        <img src="game/img/npc/steve.png"><br/>
                                        <b><span name="scientists_price">$1,000,000</span></b><br/>
                                        Research rate +<span name="scientistTime">1</span> second(s)
                                        <br/>
                                        <br/>
                                        <center><font size="2"><a href="#" name="scientistBuyMode" style="color:green;">Use boss currency</a></font></center>
                                    </td>
                                    <td>
                                        You own <span name="scientists_owned">0</span> scientists.<br/>
                                        <button name="hire_scientist">Buy</button> &nbsp;
                                        <button name="fire_scientist">Sell</button><br/>
                                        <button name="hirex_scientists">Buy X</button>
                                        <button name="hiremax_scientists">Buy max</button>
                                    </td>
                                </tr>
                            </table>
                            <div class="left textleft" style="margin-left:40px;">
                                <button name="start_research" style="color:green;">Start Research Project</button>
                                <br/>
                                <br/>
                                <span name="no_projects">You currently have no projects.</span>
                                <div style="width:100%;" name="projects_holder"></div>
                            </div>
                            <div class="clear"></div>
                            <div name="research_options" class="hidden">
                                <hr>
                                <p class="title">CHOOSE A RESEARCH PROJECT</p>
                                <table></table>
                            </div>
                        </div>
                    </div>
                    <div name="character" type="tab" style="margin-top:20px;">
                        <div name="character">
                            <div name="characterIcon" style="float:left;margin-right:7px;">
                                <img src="game/img/npc/steve.png" />
                            </div>
                            <div name="characterStats" style="float:left;text-align:left;">
                                <b>Level <span name="currentLevel">1</span></b> (<span name="currentEXP">0</span>/<span name="requiredEXP">0</span>)
                                <div class="bar" name="levelBar" style="width:200px;"><span style="width:0%;">0%</span></div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div name="items">
                            <h3>Capacity: <span name="item_count">0/10</span> items</h3>
                            <hr>
                            <span style="float:left;text-align:left;">
                                <input type="checkbox" name="dropConfirm" checked="checked" /> Ask for confirmation for each drop
                            </span>
                            <br/><br/>
                            <div name="items_holder" style="float:left;text-align:left;margin-bottom:40px;">
                                You have no items in your inventory.
                            </div>
                        </div>
                    </div>
                    <table name="workers" cellpadding="8" type="tab" class="hidden">
                        
                    </table>
                    <table name="soldiers" cellpadding="8" type="tab" class="hidden">

                    </table>
                    <table name="stats" cellpadding="8" type="tab" class="hidden textleft">
                        <tr><td>Money earned</td><td name="totalmoney">0</td></tr>
                        <tr><td>Money per tick</td><td name="moneypertick">0</td></tr>
                        <tr><td>Money from looting</td><td name="totallootmoney">0</td></tr>
                        <tr><td>Total Worker OPM</td><td name="totalworkeropm">0</td></tr>
                        <tr><td>Times Worker Efficiency<br/>has been researched</td><td name="wopmtimesresearch">0</td></tr>
                        <tr><td>Total Army KPE</td><td name="armystrength">0</td></tr>
                        <tr><td>Enemies killed</td><td name="totalenemieskilled">0</td></tr>
                        <tr><td>Defenders killed</td><td name="totaldefenderskilled">0</td></tr>
                        <tr><td>Battles won</td><td name="totalbattleswon">0</td></tr>
                        <tr><td>Battles lost</td><td name="totalbattleslost">0</td></tr>
                        <tr><td>Last Submit</td><td name="lasthssubmit">never</td></tr>
                    </table>
                    <div name="attack" type="tab" class="hidden center" style="margin-top:20px;">

                    </div>
                    <div name="portal" type="tab" class="hidden center" style="margin-top:20px;">
                        <font color="red">BEWARE...</font>
                    </div>
                    <div name="village" type="tab" class="hidden center" style="margin-top:20px;">
                        <div name="constructvillage">
                            You need a constitution to start a village.
                        </div>
                        <div name="overview">
                            
                        </div>
                    </div>
                    <div name="chat" type="tab" class="hidden" style="margin-top:20px;">
                        <div id="chatbox">
                            
                        </div>
                    </div>
                    <div name="admin" type="tab" class="hidden">
                        <center><h2>Admin Panel</h2></center><hr>
                        <div name="users" style="float:left;margin-right:20px;">
                            
                        </div>
                        <div name="global" style="float:left;">
                            <h3>Global Actions</h3>
                            <button name="admin-initUpdate">Initiate Update</button>
                        </div>
                        <div name="user" style="float:left;" class="hidden">
                            
                        </div>
                        <div class="clear" style="margin-bottom:175px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="popup" class="popup hidden">
            <div name="store_content">
                <p name="title" class="title"></p>
                <div name="content">

                </div>
                <span name="buttons">
                    <button name="continue">CONTINUE</button>
                </span>
                <div class="clear"></div>
            </div>
        </div>

        <div id="loading_screen">
            <h2>A G A M E . . .</h2>
            <br/>
            <img src="game/img/icons/loading.gif" width="50" height="50" />
            <br/>
            
            <font size="1"><span name="connecting">Connecting to game server ...</span></font>
            <font size="1"><span name="resources" class="hidden">Please wait while the game resources are loaded.</span></font>
        </div>
    </body>
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-44010885-1', 'rscharts.com');
    ga('send', 'pageview');

    </script>
</html>
