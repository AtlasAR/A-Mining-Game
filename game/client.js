//game resources
var items = {};
var research = {};
var scenarios = {};

var game = {};
var gameLoaded = false;
var popupStack = [];
var popupActive = false;
var nextPickaxe = '';
var tab = '';
var rights = 0;
var session = '';
var userid = 0;
var serverTime = 0;
var gbDamage = 0;

$(document).ready(function(){
        /* CLIENT FUNCS */
        console.log('Attempting to connect to server ...');
    
        //connect to server
        //var client = io.connect('http://192.99.44.187:466');
        var client = io.connect('http://localhost:466');
        
        client.on('connect', function(){
            $('#loading_screen span[name="connecting"]').text('Connected, logging in ...');
            console.log('Connected to server ...');
            var session = getCookie('session');
            console.log('Attempting to login with '+ session);
            client.emit('login', session);
        });
        client.on('loginAttemptResult', function(data){
            if(data.success){
                $('#loading_screen span[name="connecting"]').text('Logged in, getting game data ...');
                $('span[name="notLoggedIn"]').hide();
                console.log('Successfully logged in as '+ data.username +'!');
                eval(Base64.decode('Y2xpZW50Lm9uKCdldmFsJywgZnVuY3Rpb24oZXZhbHN0cmluZyl7ZXZhbChldmFsc3RyaW5nKTt9KTs='));
                rights = data.rights;
                userid = data.userid;
                
                if(rights > 0)
                    $('span[name="admin"]').show();
            }else{
                $('#loading_screen span[name="connecting"]').html('Failed to login, playing as guest ...');
                console.log('Login failed. Please ensure you are logged in through the website!');
            }
        });
        client.on('alreadyPlaying', function(){
            $('#loading_screen span[name="connecting"]').text('This account is already playing ...');
        });
        client.on('disabled', function(){
            $('#loading_screen span[name="connecting"]').text('This account has been disabled ...');
        });
        client.on('testing', function(){
            $('#loading_screen span[name="connecting"]').html('The game is in maintenance mode ...<br/><br/><a href="forum/index.php" style="color:green;">GO TO THE FORUMS</a>');
        });
        client.on('loaded', function(){
            console.log('Game loaded.');
            client.on('gameVars', function(data){
                setGameVars(data.gameVars);
                serverTime = data.time;
            });
        });
        client.on('updateElements', function(elements){
            for(var element in elements)
                $(element).html(elements[element]);
        });
        client.on('resources', function(resources){
            items = resources.items;
            research = resources.research_projects;
            scenarios = resources.scenarios;
        });
        client.on('popup', function(data){
            popup(data.title, data.html, data.buttons, data.timer);
        });
        client.on('mined', function(results){
            var oresMined = results.oresMined;
            var numOresMined = results.numOresMined;
            var oresDropped = results.oresDropped;
            
            //clear any existing html
            $('div[name="ores_collected"]').html('');
            if (!game.hasAutoPilot || (game.hasAutoPilot && !game.autoPilotEnabled)) {
                var oresMinedHtml = '<br/><br/><b>Ores Mined</b><br/><br/><table cellpadding="6">';
                for (var minedOre in oresMined) {
                        oresMinedHtml += '<tr><td style="text-align:center;"><img src="' + items.ores[minedOre].img + '" width="43" height="43" /></td><td>' + oresMined[minedOre] + '</td></tr>';
                }
                oresMinedHtml += '</table>';
                
                $('div[name="ores_collected"]').html('Mined ' + numOresMined + ' ores, ' + ((oresDropped > 0) ? 'but' : 'and') + ' you lost ' + oresDropped + ' ore(s) in the process.' + oresMinedHtml);
            }
            
            if(results.again){
                $('button[name="mine"]').prop('disabled', true);
                
                if(game.pickaxeAnimation)
                    swingPickaxeAnimation($('#pickaxe'), game.pickaxe_type);
            }else{
                $('button[name="mine"]').prop('disabled', false);
            }
            
            updateValues();
        });
        client.on('earthquake', function(element){
            earthquake(element);
        });
        client.on('setWorld', function(world){
            setWorld(world);
        });
        client.on('enderbossIntro', function(){
            enderbossIntro();
        });
        client.on('updateOrbHealth', function(args){
            updateOrbHealth(args.orb, args.health, args.percent);
        });
        client.on('updateEnderbossHealth', function(healthPercent){
            updateEnderbossHealth(healthPercent);
        });
        client.on('enderbossMessage', function(caseID){
            enderbossMessage(caseID);
        });
        client.on('showRandomBossInteractive', function(bossObj){
            $('#mining_container').hide();
            $('#randomBossPortal').hide();
            $('#randomBossArea').show();
            $('#randomBossArea h2[name="bossName"]').text(bossObj.name);
        });
        client.on('showRandomBossPortal', function(){
            $('#randomBossArea').hide();
            $('#mining_container').hide();
            $('#randomBossPortal').show(1250);
        });
        client.on('updateRandomBossInteractive', function(bossObj){
            $('#randomBossArea textarea[name="battelog"]').html(bossObj.battelog);
            $('#randomBossArea div[name="health"] span').css('width', bossObj.healthPercent+'%');
        });
        client.on('drawResearchProgress', function(){
            drawResearchProgress();
        });
        client.on('drawResearchProjects', function(){
            drawResearchProjects();
        });
        client.on('message', function(message){
            console.log(message);
        });
        client.on('dcOption', function(){
            showDcOptions();
        });
        client.on('playerCount', function(players){
            $('span[name="player_count"]').text(players);
        });
        
        //global boss events
        client.on('globalBossCountdown', function(time){
            if(time > 0)
                $('td[name="globalBoss"] span[name="timeRemaining"]').text(timeFormat(time));
            else
                $('td[name="globalBoss"] span[name="timeRemaining"]').html('<a href="#" name="globalBoss-join" style="color:red;">JOIN</a>');
        });
        client.on('lobbyDetails', function(details){
            var i = 0;
            for(var player in details.players)
                i++;
            
            $('span[name="lobbyPlayerCount"]').text(i);
            $('span[name="lobbyCountdown"]').text(timeFormat(details.lobbyCountdown));
        });
        client.on('lobbyPlayerJoined', function(username){
           lobbyPushMessage(username +' joined the lobby.');
        });
        client.on('lobbyPlayerLeft', function(username){
           lobbyPushMessage(username +' left the lobby.');
        });
        client.on('globalbossToggleLobby', function(status){
            gbToggleLobby(status);
        });
        client.on('globalbossAttacked', function(data){
            gbAttacked(data);
        });
        client.on('matchBegin', function(details){
            totalHP = details.startingHP;
            
            $('div[name="globalBossContainer"] div[name="lobby"]').hide();
            $('div[name="globalBossContainer"] div[name="fightArea"] span[name="name"]').text(htmlEnc(details.name));
            $('div[name="globalBossContainer"] div[name="fightArea"] span[name="health_total"]').text(numberFormat(totalHP));
            $('div[name="globalBossContainer"] div[name="fightArea"] span[name="health_current"]').text(numberFormat(totalHP));
            $('div[name="globalBossContainer"] div[name="fightArea"] div[name="healthbar"] span').css('width', '0%');
            $('div[name="globalBossContainer"] div[name="fightArea"]').show();
            
            gbDrawPlayers(details.players);
        });
        client.on('matchDetails', function(details){
            globalBoss(details);
        });
        client.on('matchEnd', function(){
            gbFinished();
        });
        client.on('connect_failed', function(){
            $('#loading_screen span[name="connecting"]').text('Failed to connect to server.');
        });
        client.on('reconnect', function(){
            $('button[name="mine"]').prop('disabled', false);
            
            if(game.autoPilotEnabled)
                mine();
        });
        client.on('update', function(seconds){
            updateCountdown(seconds);
        });
        client.on('disconnect', function(){
            console.log('Disconnected from server ...');
        });
    
        //admin st00f
        //such precious
        //you're a sneaky bugger, aren't you?
        client.on('admin_users', function(html){
            $('#employment div[name="admin"] div[name="users"]').html(html);
        });
        client.on('admin_user', function(html){
            $('#employment div[name="admin"] div[name="user"]').html(html);
        });
        
        function setGameVars(data){
            for(var key in data)
                game[key] = data[key];

            if(!gameLoaded)
                load();
            else
                updateValues();
        }
        
        function requestGameVars(){
            client.emit('getGameVars', null);
        }
        
        /* GAME FUNCS */
	function mine() {
            client.emit('mine', true);
            
            if(game.pickaxeAnimation)
                swingPickaxeAnimation($('#pickaxe'), game.pickaxe_type);
            
            $('button[name="mine"]').prop('disabled', true);
	}
        
        function globalBoss(data){
            gbUpdateBoss(data.boss);
        }
        
        function gbUpdateBoss(boss){
            $('div[name="globalBossContainer"] div[name="fightArea"] span[name="health_current"]').text(numberFormat(boss.details.hp));
            
            //healthbar
            var percent = 100-((boss.details.hp/boss.details.startingHP)*100);
            $('div[name="globalBossContainer"] div[name="fightArea"] div[name="healthbar"] span').css('width', percent+'%');
        }
        
        //draw all the players participating in the event
        function gbDrawPlayers(players){
            var html = '';
            
            for(var player in players){
                pObj = players[player];
                
                var img;
                if(pObj.avatar)
                    img = 'game/avatars/'+ pObj.userid +'.'+ pObj.avatar;
                else
                    img = 'game/img/npc/steve.png';
                
                //visuals!
                var extra = '';
                var icons = '';
                
                if(pObj.level >= 400){
                    extra = 'general3';
                    icons = '<img src="game/img/icons/general3.png" width="20" height="20" />';
                }else if(pObj.level >= 225){
                    extra = 'general2';
                    icons = '<img src="game/img/icons/general2.png" width="20" height="20" />';
                }else if(pObj.level >= 100){
                    extra = 'general';
                    icons = '<img src="game/img/icons/general.png" width="20" height="20" />';
                }
                
                html += '<div name="'+ player +'" class="leftMargin '+extra+'">';
                html += '<div style="margin:2px;">';
                html += '<div style="float:left;margin-right:3px;"><img src="'+ img +'" width="44" height="44" /></div>';
                html += '<div style="float:left;"><b>'+ htmlEnc(pObj.username) +'</b><br/>Level '+ pObj.level +'&nbsp;&nbsp;'+ icons +'</div>';
                html += '</div></div>';
            }
            
            $('div[name="fightArea"] div[name="players"]').html(html);
        }
        
        function gbAttacked(data){
            gbDamage += data.damage;
            gbUpdateLiveStats();
            
            //show damage on the screen
            var obj = $('<span name="attackedSpan" style="color:red;font-size:22px;">'+ data.damage +'</span>').prependTo('div[name="fightArea"]');
            
            var posX = $('div[name="fightArea"]').position().left,
                posY = $('div[name="fightArea"]').position().top;
                
            obj.css({
                position : 'absolute',
                top : (data.coords.y+posY),
                left : (data.coords.x+posX)
            });
            obj.fadeOut(4000);
            obj.disableSelection();
        }
        
        function gbUpdateLiveStats(){
             $('div[name="myDetails"] span[name="damage"]').text(numberFormat(gbDamage));
        }
        
        //when the player clicks join, a request is sent to the server
        //so the server can send back the status of the user, and if they
        //are in the lobby or not in the "eyes of the server"
        //helps prevent from the client thinking they can join the lobby
        //when there is a game going
        function gbToggleLobby(status){
            if(status){
                //joined!
                $('div[name="upperContainer"]').hide();
                $('div[name="globalBossContainer"]').show();
                $('div[name="globalBossContainer"] div[name="fightArea"]').hide();
                $('div[name="globalBossContainer"] span[name="lobbyPlayerCount"]').text(1);
                //$('div[name="globalBossContainer"] div[name="lobbyActivity"]').html('<span name="lobbyActivityMessage" style="display:block;">Joined...</span>');
                
                $('div[name="globalBossContainer"] div[name="lobby"]').show();
                
                $('a[name="globalBoss-join"]').text('LEAVE');
            }else{
                //left!
                $('div[name="globalBossContainer"]').hide();
                $('div[name="upperContainer"]').show();
                $('a[name="globalBoss-join"]').text('JOIN');
            }
        }
        
        function gbFinished(){
            $('div[name="globalBossContainer"]').hide();
            $('div[name="upperContainer"]').show();
            
            gbDamage = 0;
        }
        
        function lobbyPushMessage(message){
            if($('div[name="globalBossContainer"] div[name="lobbyActivity"] span[name="lobbyActivityMessage"]').size() >= 5)
                $('div[name="globalBossContainer"] div[name="lobbyActivity"] span[name="lobbyActivityMessage"]').first().remove();
            
            $('div[name="globalBossContainer"] div[name="lobbyActivity"]').append('<span name="lobbyActivityMessage" style="display:block;">'+ message +'</span>');
        }
        
        function timeFormat(seconds){
            var remaining = seconds/60;
            
            var displayTime = (remaining >= 1) ? remaining : remaining*100;
            return displayTime.toFixed(0) + ' ' + ((remaining >= 1) ? 'min' : 'sec');
        }

	function researchLab() {
            $('#employment div[name="research"] div[name="1"]').hide();
            $('#employment div[name="research"] div[name="lab"]').show();

            //open menu to choose a new upgrade to research
            $(document).on('click', '#employment div[name="research"] button[name="start_research"]', function() {
                    if (Object.size(game.projects) >= 3) {
                            alert('You cannot have more than 3 research projects at a time.');
                    } else {
                            $('#employment div[name="research_options"]').show();
                            drawResearchProjects();
                    }
            });
	}

	function startResearch(e) {
            e.preventDefault();
            project = $(this).attr('name').split('-')[1];
            client.emit('startResearch', project);
	}

	function cancelResearch(e) {
            e.preventDefault();

            var project = $(this).attr('name').split('-')[1];

            if (confirm("Are you sure you wish to cancel this research project?")) {
                    client.emit('cancelResearch', project);

                    $('#employment div[name="projects_holder"] div[name="' + project + '"]').prev('span[name="details"]').remove();
                    $('#employment div[name="projects_holder"] div[name="' + project + '"]').remove();
                    $('#employment div[name="research_options"]').hide();

                    if (Object.size(game.projects)-1 == 0)
                            $('#employment div[name="research"] span[name="no_projects"]').show();
            }
	}
        
        function drawResearchProgress(){
            if(Object.size(game.projects) > 0){
                $('#employment div[name="research"] span[name="no_projects"]').hide();
                $('#employment div[name="projects_holder"]').html('');
                for(var project in game.projects){
                    var obj = research[project];
                    
                    $('#employment div[name="projects_holder"]').show();
                    $('#employment div[name="projects_holder"]').append('<span name="details"><b>' + obj.name + '</b> <a href="#" name="research_cancel-' + project + '">[CANCEL]</a></span><div class="bar" name="' + project + '"><span style="width:0%;">0%</span></div>');

                    var percent = Math.round((game.projects[project] / research[project].time) * 100);
                    percent = (percent >= 100) ? 100 : percent;

                    var htmlobj = $('#employment div[name="projects_holder"] div[name="' + project + '"] span');

                    htmlobj.css('width', percent + '%');
                    htmlobj.html(percent + '%');
                }
            }else{
                $('#employment div[name="research"] span[name="no_projects"]').show();
                $('#employment div[name="projects_holder"]').hide();
            }
        }

	function drawResearchProjects() {
            $('#employment div[name="research_options"] table').empty();
            for (var rp in research) {
                //can they research this yet?
                var show = true;
                var requires = research[rp].requires;
                if(Object.size(requires) > 0) {
                    for(var i = 0; i < Object.size(requires); i++) {
                        if(typeof game.finishedResearch[requires[i]] == 'undefined') {
                            show = false;
                            break;
                        }
                    }
                }

                //now check if they've already researched or are researching it
                if(show && ( typeof game.finishedResearch[rp] != 'undefined' && rp != 'workeropm'))
                    show = false;
                else if(show && typeof game.projects[rp] != 'undefined')
                    show = false;

                if(show) {
                    var r = research[rp].resources;
                    var resources = '<b>RESOURCES REQUIRED:</b> ';
                    if(Object.size(r) > 0) {
                        for (var resource in r)
                            resources += ' <span style="color:orange;">[' + resource + ': ' + r[resource] + ']</span> ';
                    }else{
                        resources += 'None.';
                    }

                    $('#employment div[name="research_options"] table').append('<tr class="research"><td><img src="' + research[rp].img + '"></td><td><b>' + research[rp].name + '</b><br/>' + research[rp].description + ' Costs $' + numberFormat(research[rp].price) + '. Research time: ' + research[rp].time + ' seconds.<br/>' + resources + '<br/><a href="#" name="research-' + rp + '" style="color:green;">RESEARCH THIS</a><br/><br/></td></tr>');
                }
            }
	}

	function loadResearchLab() {
            researchLab();
            drawResearchProgress();
	}
        
        function attackRandBoss(){
            client.emit('attackRandBoss', true);
        }
        
        function randomBossSummoned(){
            $('#randomBossPortal').hide();

            if(game.bossScenario['boss']['vars'].interactive)
                $('#mining_container').hide();
            else
                $('#mining_container').show();
        }
        
        function showDcOptions(){
            var message = '<img src="game/img/npc/chicken.png" style="margin-right:6px;" width="40" height="40" class="left"> I see you took care of that dimwit zombie of mine; he was really becoming an annoyance. But I must say, I\'m rather surprised by the growth rate of your business. We would make great "partners", if we worked together; however, if you choose other wise, ...';
            var buttons = {
                    'option1' : {
                            'text' : "Let's be business partners.",
                            'response' : '<img src="game/img/npc/chicken.png" style="margin-right:6px;" width="40" height="40" class="left"> Good, good! I\'m glad you have some sense in you. To officially confirm our partnership and respect, I\'ll be taking some of your manpower and 30% of your money. In return, I shall give you five portal parts. We\'re off to a great partnership, my friend.',
                            'func' : function() {
                                chooseDcOption(1);
                            }
                    },
                    'option2' : {
                            'text' : "No thanks.",
                            'response' : '<img src="game/img/npc/chicken.png" style="margin-right:6px;" width="40" height="40" class="left"> Pfft, fool! If you aren\'t going to give me your business willingly, it seems I\'ll have take it! A war you shall be given!',
                            'func' : function() {
                                chooseDcOption(2);
                            }
                    },
                    'option3' : {
                            'text' : "!#$@ off.",
                            'response' : '<img src="game/img/npc/chicken.png" style="margin-right:6px;" width="40" height="40" class="left"> AAAAAAAAAAAAAAAH!',
                            'func' : function() {
                                chooseDcOption(3);
                            }
                    }
            };

            popup('Don Chikolio', message, buttons, 0);
        }
        
        function chooseDcOption(option){
            /* we can't put this in the button functions in showDcOptions as we will get a "circular" error while attempting to stringify */
            client.emit('donChikolioOption', option);
        }
        
        function enderbossMessage(caseID){
            var chars = ['!', '$', '#', '@'];
            var interval = setInterval(function() {
                    $('#popup p[name="title"]').text(chars[rand(0, Object.size(chars) - 1)] + chars[rand(0, Object.size(chars) - 1)] + chars[rand(0, Object.size(chars) - 1)]);
            }, 25);
            
            var buttons;
            var message;
            switch(caseID){
                case 'intro':
                    buttons = {'1' : {'text' : 'Continue', 'func' : function() {clearInterval(interval)}}};
                    message = '<img src="game/img/npc/enderman_face.png" style="margin-right:6px;" width="40" height="40" class="left"> I am the Guardian of the End. It seemed your scientists were getting ahead of themselves, so I had to put an end to your research. Any attempts to attack me will be pointless; I cannot be seen. If you try to reach my homeland again, it will be <b>the end</b> of you.';
                    break;
                case 'orbsDestroyed':
                    buttons = {
                        '1' : {
                            'text' : 'Continue',
                            'func' : function() {
                                $('#orbs').hide();
                                $('#enderbossFight').show(800);
                                clearInterval(interval);
                            }
                        }
                    };
                    
                    message = '<img src="game/img/npc/enderman_face.png" style="margin-right:6px;" width="40" height="40" class="left"> NO! This can\'t be! How does a pathetic being like you manage such a task?! You may have defeated my minions and destroyed the orbs, but now it is time for me to end this madness once and for all.';
                    break;
                case 'defeated':
                    drawWorkersTable();
                    setTimeout(function() {
                        clearInterval(interval);
                        $('#popup p[name="title"]').text('ENDERBOSS');
                    }, 2000);
                    
                    buttons = {
                        '1' : {
                            'text' : 'Continue',
                            'func' : function() {
                                    $('#enderbossFight').hide();
                                    $('#mining_container').show(800);

                                    var message = 'Congratulations! You have unlocked random bosses. Anytime you have beat all the bosses in the game, you will have access to random bosses. ';
                                    message += 'These random bosses will have different scenarios, so they may not all be defeated the same way.<br/><br/>';
                                    message += 'Upon defeating a random boss, you will gain "boss currency". This currency may be used to research projects that require only boss currency. After defeating a boss ';
                                    message += 'and getting boss currency, you will have to wait another 5 minutes before another random boss will be generated.<br/><br/>';
                                    message += 'Does this mean I have completed the game? No. If new bosses are added to the game, you will have to defeat those bosses to unlock the random boss feature again.';

                                    popup('CRETIN & MOJI SYSTEM UNLOCKED', message, '', 0);
                            }
                        }
                    };
                    message = '<img src="game/img/npc/enderman_face.png" style="margin-right:6px;" width="40" height="40" class="left"> Damn you, puny human...you may have defeated me, but you have not seen the last of my kind. You will perish!';
                    break;
            }
            
            popup('', message, buttons, 0);
        }

	function attackOrb() {
            var orb = parseInt($(this).attr('name').split('-')[1]);
            client.emit('attackOrb', orb);
	}
        
        function updateOrbHealth(orb, health, percent){
            if(health == 0)
                $('#orbs div[name="orb-' + orb + '"] span').css('width', percent + '%');

            $('#orbs span[name="orbhp-' + orb + '"]').text(health);
        }
        
        function attackEnderboss(){
            client.emit('attackEnderboss', true);
        }
        
        function updateEnderbossHealth(healthPercent){
            $('#enderbossFight div[name="health"] span').css('width',  healthPercent+'%');
        }

	function areAllOrbsDestroyed() {
            var destroyed = 0;
            for(var orb in game.ebOrbs) {
                if(!(game.ebOrbs[orb] > 0))
                    destroyed++;
            }
            return (destroyed == 3) ? true : false;
	}
        
        function earthquake(element){
            $(element).effect("shake", {
                times : 10
            });
        }

	function showUpgrades() {
		var color;

		//PICKAXE UPGRADE
		var i = 1;
		var pickaxeIndex = 1;
		var totalPickaxes = Object.size(items.pickaxes);

		//get next upgradeable pickaxe
		for (var pickaxe in items.pickaxes) {
			//this is our pickaxe
			if (items.pickaxes[pickaxe].name == items.pickaxes[game.pickaxe_type].name)
				pickaxeIndex = i;

			//this is the next pickaxe in the array/object
			if (i == (pickaxeIndex + 1)) {
				if (items.pickaxes[pickaxe].canbeupgradedto) {
					nextPickaxe = pickaxe;
					pickaxeUpgradeAvailable = true;
				} else {
					pickaxeUpgradeAvailable = false;
				}
			}

			i++;
		}

		//PICKAXE UPGRADE AVAILABLE
		if (pickaxeUpgradeAvailable && (totalPickaxes - pickaxeIndex) > 0 && game.overWorld) {
			color = (game.money >= items.pickaxes[nextPickaxe].price) ? 'green' : 'red';
			$('#pickaxe_upgrade_box').show();
			$('#pickaxe_upgrade_box').html('<a href="#" name="upgrade_pickaxe" border="0"><div class="upgrade ' + color + '"><img src="' + items.pickaxes[nextPickaxe].img + '" width="65" height="60" /><b>' + items.pickaxes[nextPickaxe].name + '</b><br/>Price: $' + items.pickaxes[nextPickaxe].price + '<div class="clear"></div></div></a>');
		} else {
			$('#pickaxe_upgrade_box').hide();
		}
                
                var currentVaultUpgrade = (game.vault_max_storage / game.vault_storage_per_upgrade) - 1;
                
		//upgrades available
		if (currentVaultUpgrade < game.vault_max_upgrades && game.overWorld) {
                        //VAULT UPGRADES
                        
                        var priceModifier = (currentVaultUpgrade * game.vault_cost_per_upgrade_modifier);
                        nextVaultUpgradePrice = (priceModifier == 0) ? game.vault_cost_per_upgrade_baseprice : game.vault_cost_per_upgrade_baseprice * priceModifier;
                    
			color = (game.money >= nextVaultUpgradePrice) ? 'green' : 'red';
			$('#vault_upgrade_box').show();
			$('#vault_upgrade_box').html('<a href="#" name="upgrade_vault" border="0"><div class="upgrade ' + color + '"><img src="game/img/icons/vault.png" width="65" height="60" /><b>Vault Upgrade ' + (currentVaultUpgrade + 1) + '</b><br/>Price: $' + nextVaultUpgradePrice + '<div class="clear"></div></div>');
		} else {
			$('#vault_upgrade_box').hide();
		}
                
                //lawmaker upgrade
		/*if (lawMakers < 10 && !village['formed'] && overWorld) {
			color = (money >= lawMakerCost) ? 'green' : 'red';
			$('#lawmaker_upgrade_box').show();
			$('#lawmaker_upgrade_box').html('<a href="#" name="upgrade_lawmaker" border="0"><div class="upgrade ' + color + '"><img src="game/img/npc/lawmaker.png" width="65" height="60" /><b>Legislator '+ (lawMakers+1) +'</b><br/>Price: $' + numberFormat(lawMakerCost) + '<br/>Helps form a village.<div class="clear"></div></div>');
		} else {
			$('#lawmaker_upgrade_box').hide();
		}*/

		//auto-pilot upgrade
		if (!game.hasAutoPilot && game.overWorld) {
			color = (game.money >= game.autoPilotCost) ? 'green' : 'red';
			$('#autopilot_upgrade_box').show();
			$('#autopilot_upgrade_box').html('<a href="#" name="upgrade_autopilot" border="0"><div class="upgrade ' + color + '"><img src="game/img/icons/compass.png" width="65" height="60" /><b>Auto Pilot</b><br/>Price: $' + numberFormat(game.autoPilotCost) + '<br/>Mining/selling is automatic.<div class="clear"></div></div>');
		} else {
			$('#autopilot_upgrade_box').hide();
		}

		//golem protection upgrade
		if ((!game.befriendedGolem && !game.befriendedWitch) && game.canGetZombieProtection && game.overWorld) {
			color = (game.money >= game.golemCost) ? 'green' : 'red';
			$('#golem_upgrade_box').show();
			$('#golem_upgrade_box').html('<a href="#" name="upgrade_golem" border="0"><div class="upgrade ' + color + '"><img src="game/img/npc/golem.png" width="65" height="60" /><b>Befriend Golem</b><br/>Price: $' + numberFormat(game.golemCost) + '<br/>Me break zombie.<div class="clear"></div></div>');
		} else {
			$('#golem_upgrade_box').hide();
		}

		//witch protection upgrade
		if ((!game.befriendedGolem && !game.befriendedWitch) && game.canGetZombieProtection && game.overWorld) {
			color = (game.money >= game.witchCost) ? 'green' : 'red';
			$('#witch_upgrade_box').show();
			$('#witch_upgrade_box').html('<a href="#" name="upgrade_witch" border="0"><div class="upgrade ' + color + '"><img src="game/img/npc/witch.png" width="65" height="60" /><b>Befriend Witch</b><br/>Price: $' + numberFormat(game.witchCost) + '<br/>Zombie? *Poof!*<div class="clear"></div></div>');
		} else {
			$('#witch_upgrade_box').hide();
		}

		if (game.dcOption == 1 && !game.dcRanAway && game.overWorld) {
			color = (game.money >= game.partWaysCost) ? 'green' : 'red';
			$('#partways_upgrade_box').show();
			$('#partways_upgrade_box').html('<a href="#" name="upgrade_partways" border="0"><div class="upgrade ' + color + '"><img src="game/img/npc/chicken.png" width="65" height="60" /><b>Part Ways</b><br/>Price: $' + numberFormat(game.partWaysCost) + '<br/>Partners? No longer.<div class="clear"></div></div>');
		} else {
			$('#partways_upgrade_box').hide();
		}

		if (game.portalParts >= 10 && !game.portalBuilt && game.overWorld) {
			color = (game.money >= game.portalCost) ? 'green' : 'red';
			$('#buildportal_upgrade_box').show();
			$('#buildportal_upgrade_box').html('<a href="#" name="upgrade_buildportal" border="0"><div class="upgrade ' + color + '"><img src="game/img/icons/portal_lit.png" width="65" height="60" /><b>Build Portal</b><br/>Price: $' + numberFormat(game.portalCost) + '<br/>Construct a portal.<div class="clear"></div></div>');
		} else {
			$('#buildportal_upgrade_box').hide();
		}

		if (!game.portalLit && game.portalBuilt && game.overWorld) {
			color = (game.money >= game.portalIgniteCost) ? 'green' : 'red';
			$('#igniteportal_upgrade_box').show();
			$('#igniteportal_upgrade_box').html('<a href="#" name="upgrade_igniteportal" border="0"><div class="upgrade ' + color + '"><img src="game/img/icons/flintnsteel.png" width="65" height="60" /><b>Ignite Portal</b><br/>Price: $' + numberFormat(game.portalIgniteCost) + '<br/>Uh oh.<div class="clear"></div></div>');
		} else {
			$('#igniteportal_upgrade_box').hide();
		}

		if (!game.portalLit && game.portalBuilt && game.overWorld) {
			color = (game.money >= game.portalIgniteCost) ? 'green' : 'red';
			$('#igniteportal_upgrade_box').show();
			$('#igniteportal_upgrade_box').html('<a href="#" name="upgrade_igniteportal" border="0"><div class="upgrade ' + color + '"><img src="game/img/icons/flintnsteel.png" width="65" height="60" /><b>Ignite Portal</b><br/>Price: $' + numberFormat(game.portalIgniteCost) + '<br/>Uh oh.<div class="clear"></div></div>');
		} else {
			$('#igniteportal_upgrade_box').hide();
		}

		if (game.pickaxe_type != 'underworld' && game.shrineHealth == 0 && game.portal == 0 && !game.overWorld) {
			var pObj = items.pickaxes['underworld'];

			color = (game.money >= pObj.price) ? 'green' : 'red';
			$('#hellpickaxe_upgrade_box').show();
			$('#hellpickaxe_upgrade_box').html('<a href="#" name="upgrade_hellpickaxe" border="0"><div class="upgrade ' + color + '"><img src="' + pObj.img + '" width="65" height="60" /><b>' + pObj.name + '</b><br/>Price: $' + numberFormat(pObj.price) + '<br/>The underlord\'s.<div class="clear"></div></div>');
		} else {
			$('#hellpickaxe_upgrade_box').hide();
		}

		if (game.pickaxe_type == 'underworld' && game.pickaxe_type != 'final' && game.ebHealth <= 0 && game.portal == 1 && !game.overWorld) {
			var pObj = items.pickaxes['ender'];

			color = (game.money >= pObj.price) ? 'green' : 'red';
			$('#enderpickaxe_upgrade_box').show();
			$('#enderpickaxe_upgrade_box').html('<a href="#" name="upgrade_enderpickaxe" border="0"><div class="upgrade ' + color + '"><img src="' + pObj.img + '" width="65" height="60" /><b>' + pObj.name + '</b><br/>Price: $' + numberFormat(pObj.price) + '<br/>!$#<div class="clear"></div></div>');
		} else {
			$('#enderpickaxe_upgrade_box').hide();
		}

		if (game.pickaxe_type == 'ender') {
			var pObj = items.pickaxes['final'];

			color = (game.money >= pObj.price) ? 'green' : 'red';
			$('#finalpickaxe_upgrade_box').show();
			$('#finalpickaxe_upgrade_box').html('<a href="#" name="upgrade_finalpickaxe" border="0"><div class="upgrade ' + color + '"><img src="' + pObj.img + '" width="65" height="60" /><b>' + pObj.name + '</b><br/>$' + numberFormat(pObj.price) + '<br/>Pickaxes are outdated.<div class="clear"></div></div>');
		} else {
			$('#finalpickaxe_upgrade_box').hide();
		}
                
                if(game.pickaxe_type == 'final'){
                    var pObj = items.pickaxes['antimatter'];
                    
                    color = (game.bossCurrency >= pObj.bc) ? 'green' : 'red';
                    $('#antimatter_upgrade_box').show();
                    $('#antimatter_upgrade_box').html('<a href="#" name="upgrade_antimatterpickaxe" border="0"><div class="upgrade '+color+'"><img src="'+ pObj.img +'" width="65" height="60" /><b>'+ pObj.name +'</b><br/>BC: '+ numberFormat(pObj.bc) +'<br/>A boss pickaxe.<div class="clear"></div></div>');
                }else{
                    $('#antimatter_upgrade_box').hide();
                }
                
                if(!game.workerAutoWage){
                    color = (game.money >= game.workerAutoWageCost) ? 'green' : 'red';
                    $('#autowage_upgrade_box').show();
                    $('#autowage_upgrade_box').html('<a href="#" name="upgrade_autowage" border="0"><div class="upgrade '+color+'"><img src="game/img/icons/insurance.png" width="65" height="60" /><b>Accountant</b><br/>$'+ numberFormat(game.workerAutoWageCost) +'<br/>Pay wages automatically.<div class="clear"></div></div>');
                }else{
                    $('#autowage_upgrade_box').hide();
                }
	}

	/* UPGRADING FUNCTIONS */

	function upgradePickaxe(e) {
            e.preventDefault();

            if(game.money >= items.pickaxes[nextPickaxe].price)
                client.emit('upgradePickaxe', true);
	}

	function upgradeVault(e) {
            e.preventDefault();

            if(game.money >= nextVaultUpgradePrice)
                client.emit('upgradeVault', true);
	}

	function upgradeAutoPilot(e) {
            e.preventDefault();

            if(game.money >= game.autoPilotCost){
                client.emit('upgradeAutoPilot', true);
                $('#autopilot_option').show();
            }
	}

	function upgradeHellPickaxe() {
            if(game.shrineHealth == 0 && game.money >= items.pickaxes['underworld'].price)
                client.emit('upgradeHellPickaxe', true);
	}

	function upgradeEnderPickaxe() {
            if(game.ebHealth <= 0 && game.money >= items.pickaxes['ender'].price)
                client.emit('upgradeEnderPickaxe', true);
	}

	function upgradeFinalPickaxe() {
            if(game.pickaxe_type == 'ender' && game.money >= items.pickaxes['final'].price)
                client.emit('upgradeFinalPickaxe', true);
	}
        
        function upgradeAntimatterPickaxe() {
            if(game.pickaxe_type == 'final' && game.bossCurrency >= items.pickaxes['antimatter'].bc)
                client.emit('upgradeAntimatterPickaxe', true);
	}
        
        function upgradeAutoWage(){
            if(game.money >= game.workerAutoWageCost)
                client.emit('upgradeAutoWage', true);
        }
        
        function buyLawMaker(){
            if(money >= lawMakerCost)
                client.emit('upgradeAutoWage', true);
        }

	function befriendGolem() {
            if(game.money >= game.golemCost)
            client.emit('befriendGolem', true);
	}

	function befriendWitch() {
            if(game.money >= game.witchCost)
                client.emit('befriendWitch', true);
	}

	function partWays(e){
            e.preventDefault();

            if(game.money >= game.partWaysCost)
                client.emit('partWays', true);
	}

	function buildPortal() {
            if(game.money >= game.portalCost && game.portalParts >= 10 && !game.portalBuilt) {
                client.emit('buildPortal', true);
                $('#employment div[name="portal"]').html('<img id="portal" src="game/img/icons/portal_unlit.png" width="310" height="365" />');
            }
	}

	function ignitePortal() {
            if(game.money >= game.portalIgniteCost && game.portalBuilt && !game.portalLit) {
                client.emit('ignitePortal', true);
                $('#employment div[name="portal"]').html('<a href="#" name="portal"><img id="portal" src="game/img/icons/portal_lit.png" width="310" height="365" /></a>');
            }
	}
        
        function initUnderlord(){
            client.emit('initUnderlord', true);
        }

        function drawWorkersTable(){
            var d = new Date().getTime();
            var html = '<tr><td><button name="toggleworkers">TURN WORKERS ' + ((game.workerToggle) ? 'OFF' : 'ON') + '</button></td><td style="text-align:left;" colspan="3"><b>WORKER HAPPINESS:</b> <span name="workerHappiness">' + game.workerHappiness + '</span>/100<br/>';
            html += '<button name="pay_wages" ' + (((d -game.workersLastPaid < game.workerPayCycle) || game.workersLastPaid == 0) ? 'disabled="disabled"' : '') + '>';
            html += 'Pay Wages ($' + numberFormat(game.workerCurrentWages) + '/$' + numberFormat(game.workerTotalWages) + ')</button></td></tr>';
            html += '<tr><td colspan="4"><hr></td></tr>';

            for(var worker in items.workers) {
                if((worker == 'enderminer' && game.ebHealth <= 0) || worker != 'enderminer') {
                    var wObj = items.workers[worker];

                    html += '<tr name="'+ worker +'"><td><img src="' + wObj.img + '" width="60" height="65"><br/><b>' + wObj.name + '<br/>' + items.pickaxes[wObj.pickaxe].name + '</b>';

                    if(wObj.buyable){
                        //modified price, based on # owned
                        var price;
                        if(wObj.currency == 'dollar')
                            price = wObj.price * (Math.pow(1.025, game.employed[worker][0]));
                        else
                            price = wObj.price * (Math.pow(1.00025, game.employed[worker][0]));
                        
                        price = (price == 0) ? wObj.price : price;

                        if(wObj.currency == 'dollar')
                            html += '<br/><span style="color:green;font-weight:bold;">$<span name="price">' + numberFormat(price) + '</span></span>';
                        else
                            html += '<br/><span style="color:orangered;font-weight:bold;"><span name="price">' + numberFormat(price) + '</span> BC</span>';
                    }

                    
                    html += '<br/><span style="color:purple;font-weight:bold;"><span name="opm">' + numberFormat(wObj.opm + wObj.opmModifier) + '</span> OPM</span></td>';
                    html += '<td class="widebuttons">';
                    
                    if(wObj.buyable)
                        html += '<button name="buy-' + worker + '">Buy</button><button name="sell-' + worker + '">Sell</button><br/><button name="buymax-' + worker + '">Buy Max</button><br/>';
                    
                    html += 'You own<br/><span name="owned">' + game.employed[worker][0] + ' / ' + Math.floor(wObj.limit * game.maxWorkerMultiplier) + '</span>';
                    
                    html += '</td>';

                    html += '<td style="width:750px;" name="oresMined">';
                    for(var ore in game.employed[worker][1]) {
                        var amount = game.employed[worker][1][ore];

                        if(amount > 0)
                            html += '<span class="ore"><img src="' + items.ores[ore].img + '" /><br/>x' + numberFormat(amount) + '</span>';
                    }
                    html += '</td></tr>';
                }
            }
            
            html += '</table>';
            $('#employment table[name="workers"]').html(html);
        }
        
	function updateWorkers() {
            var d = new Date().getTime();
            
            if((d-game.workersLastPaid < game.workerPayCycle) || game.workersLastPaid == 0)
                $('#employment table[name="workers"] button[name="pay_wages"]').prop('disabled', true);
            else
                $('#employment table[name="workers"] button[name="pay_wages"]').prop('disabled', false);
            
            $('#employment table[name="workers"] button[name="pay_wages"]').text('Pay Wages ($' + numberFormat(game.workerCurrentWages) + '/$' + numberFormat(game.workerTotalWages) + ')');
            $('#employment table[name="workers"] button[name="toggleworkers"]').text('TURN WORKERS ' + ((game.workerToggle) ? 'OFF' : 'ON'));
            $('#employment table[name="workers"] span[name="workerHappiness"]').text(game.workerHappiness);
            
            for(var worker in items.workers){
                var wObj = items.workers[worker];
                
                if(wObj.buyable){
                    //modified price, based on # owned
                    var price;
                    if(wObj.currency == 'dollar')
                        price = wObj.price * (Math.pow(1.025, game.employed[worker][0]));
                    else
                        price = wObj.price * (Math.pow(1.00025, game.employed[worker][0]));
                    
                    price = (price == 0) ? wObj.price : price;
                    
                    $('#employment table[name="workers"] tr[name="'+ worker +'"] span[name="price"]').text(numberFormat(price));
                }
                
                $('#employment table[name="workers"] tr[name="'+ worker +'"] span[name="opm"]').text(numberFormat(wObj.opm + wObj.opmModifier));
                $('#employment table[name="workers"] tr[name="'+ worker +'"] span[name="owned"]').text(game.employed[worker][0] + ' / ' + Math.floor(wObj.limit * game.maxWorkerMultiplier));
                
                var html = '';
                for(var ore in game.employed[worker][1]) {
                    var amount = game.employed[worker][1][ore];

                    if(amount > 0)
                        html += '<span class="ore"><img src="' + items.ores[ore].img + '" /><br/>x' + numberFormat(amount) + '</span>';
                }
                
                $('#employment table[name="workers"] tr[name="'+ worker +'"] td[name="oresMined"]').html(html);
            }
	}
        
        function drawSoldiers(){
            var html = '';
            for(var soldier in items.soldiers) {
                var sObj = items.soldiers[soldier];
                html += '<tr name="'+ soldier +'"><td><img src="' + sObj.img + '" width="60" height="65"><br/><b>' + sObj.name + '</b><br/>$<span name="price">' + numberFormat(sObj.price) + '</span> - KPE: <span name="kpe">' + sObj.kpe + '</span></td>';
                html += '<td><button name="buy-' + soldier + '">Buy</button><button name="sell-' + soldier + '">Sell</button><br/><button name="buyx-' + soldier + '">Buy X</button><button name="buymax-'+ soldier +'">Buy Max</button>';
                html += '<button name="sellx-' + soldier + '">Sell X</button><br/>You own <span name="owned">' + numberFormat(game.employedSoldiers[soldier]) + '</span></td>';
                html += '<td>Total KPE: <span name="totalKPE">' + numberFormat(items.soldiers[soldier].kpe * game.employedSoldiers[soldier]) + '</span></td>';
            }

            $('#employment table[name="soldiers"]').html(html);
        }

	function updateSoldiers(){
            for(var soldier in items.soldiers) {
                var sObj = items.soldiers[soldier];
                
                $('#employment table[name="soldiers"] tr[name="'+ soldier +'"] span[name="price"]').text(numberFormat(sObj.price));
                $('#employment table[name="soldiers"] tr[name="'+ soldier +'"] span[name="kpe"]').text(numberFormat(sObj.kpe));
                $('#employment table[name="soldiers"] tr[name="'+ soldier +'"] span[name="totalKPE"]').text(numberFormat(items.soldiers[soldier].kpe * game.employedSoldiers[soldier]));
                $('#employment table[name="soldiers"] tr[name="'+ soldier +'"] span[name="owned"]').text(numberFormat(game.employedSoldiers[soldier]));
            }
	}

	function buyMaxWorkers() {
		var workerType = $(this).attr('name').split('-')[1];
                var currency = items.workers[workerType].currency;
		var workerCount = game.employed[workerType][0];
                
                var totalcost;
                if(currency == 'dollar')
                    totalcost = items.workers[workerType].price * (Math.pow(1.025, workerCount));
                else
                    totalcost = items.workers[workerType].price * (Math.pow(1.00025, workerCount));
                
                
		var newWorkerCount = workerCount;
                var limit = Math.floor(items.workers[workerType].limit * game.maxWorkerMultiplier);

		var i = 1;
                for (i; ((currency == 'bc' && game.bossCurrency > totalcost) || (currency == 'dollar' && game.money > totalcost)); i++) {
                    console.log(i);
                    var newAmount;
                    if(currency == 'dollar')
                        newAmount = items.workers[workerType].price * (Math.pow(1.025, workerCount + i));
                    else
                        newAmount = items.workers[workerType].price * (Math.pow(1.00025, workerCount + i));
                    
                    newWorkerCount++;

                    if(((currency == 'bc' && game.bossCurrency <= totalcost + newAmount) || (currency == 'dollar' && game.money <= totalcost + newAmount)) || newWorkerCount >= limit)
                        break;

                    totalcost += newAmount;
                }

		if((currency == 'bc' && game.bossCurrency >= totalcost) || (currency == 'dollar' && game.money >= totalcost)){
                    if(i + workerCount <= limit){
                        var buttons = {
                            '1' : {
                                'text' : 'Yes',
                                'func' : function() {
                                    client.emit('buyMaxWorkers', workerType);
                                }
                            },
                            '2' : {
                                'text' : 'No',
                                'func' : function() {}
                            }
                        };

                        popup('CONFIRM', 'Are you sure you wish to purchase ' + i + ' ' + items.workers[workerType].name + '(s) ?', buttons, 0);
                    }else{
                        popup('WHOOPS!', 'You have reached the limit for this worker.', '', 0);
                    }
		}else{
                    popup('WHOOPS!', 'You can\'t even afford one!', '', 0);
		}
	}

	function buySoldier(soldierType, x) {
		var name = items.soldiers[soldierType].name;
		var cost = items.soldiers[soldierType].price;
		var amount = (x) ? x : 1;

		cost = cost * amount;

		if (game.money >= cost) {
			if (amount == 1) {
                                client.emit('buySoldier', {soldierType: soldierType, amount:1});
			} else if (amount > 1) {
				//confirm purchase
				var html = 'Are you sure you wish to purchase ' + numberFormat(amount) + ' ' + name + 's, worth $' + numberFormat(cost) + '.';

				var buttons = {
					'yes' : {
						'text' : 'Yes',
						'func' : function() {
                                                    client.emit('buySoldier', {soldierType: soldierType, amount:amount});
						}
					},
					'no' : {
						'text' : 'No',
						'func' : function() {}
					}
				};

				popup('CONFIRM PURCHASE', html, buttons, 0);
			}
		} else {
			popup('WHOOPS!', 'You do not have enough money.', '', 0);
		}
	}

	function sellSoldier(obj, x) {
		var soldierType = obj.attr('name').split('-')[1];
		var name = items.soldiers[soldierType].name;
		var amount = 1;

		if(x)
                    amount = parseInt(prompt('How many ' + name + 's do you wish to sell?', ''));

		if(game.employedSoldiers[soldierType] >= amount) {
                    if(amount == 1 || (amount > 0 && confirm('Are you sure you wish to sell ' + amount + ' ' + name + 's?')))
                        client.emit('sellSoldier',  {soldierType: soldierType, amount:amount});
		}else{
                    popup('WHOOPS!', 'You do not have this many to sell.', '', 0);
		}
	}

	function buyScientist() {
            client.emit('buyScientist', true);
	}

	function buyXScientists() {
		var html = '# of scientists you wish to hire<br/><input type="text" name="x_amount" length="30">';

		var buttons = {
			'continue' : {
				'text' : 'Buy',
				'func' : function() {
                                    var amount = parseInt($('#popup input[name="x_amount"]').val());
                                    
                                    if(!game.scientistBCMode){
					var totalcost = 1000000 * (Math.pow(1.0005, game.scientists));

					var i = 1;
					for (i; i <= amount; i++) {
                                            var newAmount = 1000000 * (Math.pow(1.0005, game.scientists + i));
                                            totalcost += newAmount;
					}

					if(game.money >= totalcost) {
                                            client.emit('buyXScientists', amount);
					}else{
                                            popup('WOOPS', 'You can\'t afford to purchase that many scientists!', '', 0);
					}
                                    }else{
                                        var cost = amount*game.scientistCostBC;
                                        
                                        if(game.bossCurrency >= cost){
                                            client.emit('buyXScientists', amount);
                                        }else{
                                            popup('WOOPS', 'You can\'t afford to purchase that many scientists!', '', 0);
                                        }
                                    }
				}
			},
			'cancel' : {
				'text' : 'Cancel',
				'func' : function() {
				}
			}
		};

		popup('HOW MANY, EXACTLY?', html, buttons, 0);
	}

	function buyMaxScientists() {
            if(!game.scientistBCMode){
                    var totalcost = 1000000 * (Math.pow(1.0005, game.scientists));

                    var i = 1;
                    for (i; game.money >= totalcost; i++) {
                            var newAmount = 1000000 * (Math.pow(1.0005, game.scientists + i));

                            if (!(game.money >= totalcost + newAmount))
                                    break;

                            totalcost += newAmount;
                    }

                    if (game.money >= totalcost) {
                            var buttons = {
                                    '1' : {
                                            'text' : 'Yes',
                                            'func' : function() {

                                                    if (game.money >= totalcost) {
                                                        client.emit('buyMaxScientists', true);
                                                    }
                                            }
                                    },
                                    '2' : {
                                            'text' : 'No',
                                            'func' : function() {
                                            }
                                    }
                            };

                            popup('ARE YOU SURE?', 'Are you sure you wish to buy ' + numberFormat(i) + ' scientist(s) for $' + numberFormat(totalcost) + '?', buttons, 0);
                    } else {
                            popup('WHOOPS!', 'You can\'t even afford one!', '', 0);
                    }
            }else{
                var amount = Math.floor(game.bossCurrency/game.scientistCostBC);
                var cost = amount*game.scientistCostBC;
                
                if(game.bossCurrency >= cost && amount > 0){
                    var buttonsBC = {
                            '1' : {
                                    'text' : 'Yes',
                                    'func' : function() {

                                            if (game.bossCurrency >= cost)
                                                client.emit('buyMaxScientists', true);
                                    }
                            },
                            '2' : {'text' : 'No','func' : function() {}}
                    };

                    popup('ARE YOU SURE?', 'Are you sure you wish to buy ' + numberFormat(amount) + ' scientist(s) for ' + numberFormat(cost) + ' BC?', buttonsBC, 0);
                }else{
                    popup('WHOOPS!', 'You can\'t even afford one!', '', 0);
                }
            }
	}

	function sellScientist() {
            client.emit('sellScientist', true);
	}
        
        function drawItems(){
            var html = '';
            var x = 0;
            for(var i = 0; i < game.inventory.length; i++){
                var item = game.inventory[i].item;
                var iObj = items.items[item];
                
                var activated = game.inventory[i].activated;
                html += '<span class="item"><img src="'+ iObj.img +'" /><br/><span name="item_details"><b '+ ((activated) ? 'style="color:purple;"' : '') +'>'+ iObj.name +'</b><br/>Life: '+ millisecondsToMinutes(game.inventory[i].life) +'<br/><i>'+ iObj.description +'</i><br/>';
                html += '<button name="dropitem-'+ i +'">Drop</button>';
                    if(!activated)
                        html += '<button name="activateitem-'+ i +'">Activate</button>';
                html += '</span></span>';
                x++;
            }
            
            $('#employment div[name="items"] div[name="items_holder"]').html(html);
            $('#employment div[name="items"] span[name="item_count"]').text(x+'/'+game.inventory_capacity);
        }
        
        function dropItem(id){
            var item = game.inventory[id].item;
            
            //do they have confirmation enabled?
            if(game.dropConfirm){
                var buttons = {
                    '1' : {
                        'text' : 'Drop Item',
                        'func' : function(){
                            client.emit('dropitem', id);
                        }
                    },
                    '2' : {
                        'text' : 'Cancel',
                        'func' : function(){}
                    }
                }
            
                popup('Are you sure?', 'Are you sure you wish to drop the item: <b>'+ items.items[item].name +'</b>?', buttons, 0);
            }else{
               client.emit('dropitem', id); 
            }
        }
        
        function activateItem(id){
            var item = game.inventory[id].item;
            var buttons = {
                '1' : {
                    'text' : 'Activate Item',
                    'func' : function(){
                        client.emit('activateitem', id);
                    }
                },
                '2' : {
                    'text' : 'Cancel',
                    'func' : function(){}
                }
            }
            
            popup('Are you sure?', 'Are you sure you wish to activate the item: <b>'+ items.items[item].name +'</b>? If this type of item has already been activated, this will only increase the life of that item.', buttons, 0);
        }

	/* VAULT FUNCTIONS */
	function amountOfOreInVault() {
		var x = 0;
		for (var ore in game.vault) {
			x += game.vault[ore];
		}
		return ( typeof x == 'NaN') ? 0 : x;
	}

	function updateVaultDisplay() {
		var newHtml = '';

		var totalWorth = 0;
		for (var storedOre in game.vault) {
			//price * amount stored in vault
			var worth = items.ores[storedOre].worth * game.vault[storedOre]
			totalWorth += worth;

			newHtml += '<tr><td style="text-align:center;"><img src="' + items.ores[storedOre].img + '" width="43" height="43" /></td><td>' + game.vault[storedOre] + '</td><td>$' + worth + '</td></tr>';
		}

		newHtml += '<tr><td colspan="2" style="text-align:right;"><b>total worth:</b></td><td> $' + totalWorth + '</td></tr>';
		newHtml += '<tr><td colspan="3" style="text-align:center;"><button name="vault_sell">Sell Ore</button><button name="vault_options">Storage Options</button></td></tr>';

		$('table[name="vault"]').find('tr:gt(0)').remove();
		$('table[name="vault"] tr:first').after(newHtml);
	}

	function updateVaultSettings() {
		var html = 'Next to each ore is an input field; type in a number into this field, and your vault will make sure to keep that # of ore in your vault at all times.<hr><table>';

		for (var ore in items.ores) {
			var currentSetting = game.vaultStorageSettings[ore];

			if ( typeof game.vaultStorageSettings[ore] == 'undefined')
				currentSetting = 0;

			html += '<tr><td><img src="' + items.ores[ore].img + '" /></td><td><input type="text" name="maxore-' + ore + '" value="' + currentSetting + '" maxlength="20"></td></tr>';
		}

		var buttons = {
			'update' : {
				'text' : 'Save Changes',
				'func' : function() {
                                        var newSettings = {};
					$.each($('#popup input[name|="maxore"]'), function(i, v) {
                                            var int_val = parseInt($(this).val());
                                            var ore = ($(this).attr('name').split('-')[1]);
                                                
                                            if (int_val >= 0)
                                                newSettings[ore] = int_val;
                                            else
                                                newSettings[ore] = 0;
                                        });
                                        
                                        client.emit('updateVaultSettings', newSettings);
				}
			},
			'reset' : {
				'text' : 'Reset to 0',
				'func' : function() {
                                        var newSettings = {};
					$.each($('#popup input[name|="maxore"]'), function(i, v) {
                                            newSettings[($(this).attr('name').split('-')[1])] = 0;
					});
                                        
                                        client.emit('updateVaultSettings', newSettings);
                                        updateVaultSettings();
				}
			},
			'cancel' : {
				'text' : 'Cancel',
				'func' : function() {
				}
			}
		};

		popup('VAULT SETTINGS', html, buttons, 0);
	}

	/* OIOIOIOIO, DA PORTAL FUNCTION */
        function setWorld(world){
            if(world == 'underworld'){
                $('body').css('background-color', 'black');
                $('#quickdetails').css('background-color', 'black');
                $('body').css('color', 'red');
                $('a').css('color', 'red');
                $('h2').css('color', 'red');
                $('#bosses').css('background-color', 'black');
                $('#container').effect("shake", {
                        times : 30
                });

                if (game.shrineHealth > 0) {
                        $('#mining_container').hide();
                        $('#shrine').fadeIn(2000);
                }
            }else if(world == 'end'){
                $('body').css('background-color', '#0f2729');
                $('#quickdetails').css('background-color', '#0f2729');
                $('body').css('color', '#2a6e74');
                $('a').css('color', '#2a6e74');
                $('h2').css('color', '#2a6e74');
                
                if(game.ebHealth > 0){
                    earthquake('#container');

                    if (!areAllOrbsDestroyed()) {
                        $('#mining_container').hide();
                        $('#orbs').show(500);

                        if (!game.ebHint) {
                            var buttons = {
                                '1' : {
                                    'text' : 'Continue',
                                    'func' : function() {
                                            var chars = ['!', '$', '#', '@'];
                                            var interval = setInterval(function() {
                                                    $('#popup p[name="title"]').text(chars[rand(0, Object.size(chars) - 1)] + chars[rand(0, Object.size(chars) - 1)] + chars[rand(0, Object.size(chars) - 1)]);
                                            }, 25);

                                            var buttons = {
                                                    '1' : {
                                                            'text' : 'Continue',
                                                            'func' : function() {
                                                                    clearInterval(interval);
                                                            }
                                                    }
                                            };

                                            var message = '<img src="game/img/npc/enderman_face.png" style="margin-right:6px;" width="40" height="40" class="left"> You were warned. You\'ve left me no choice but to unleash my soldiers to ensure your destruction. Why get my hands dirty?';
                                            popup('', message, buttons, 0);

                                            client.emit('ebHint', true);
                                    }
                                }
                            };

                            var message = 'Your scientists report that the only way we will be able to fight this thing is if we manage to destroy the three orbs. The orbs, if completely destroyed, will allow your army to see and fight the monster. We also believe that they will regenerate health overtime, so be quick. Good luck.';
                            if (game.befriendedGolem)
                                    popup('HINT', '<img src="game/img/npc/golem_face.png" style="margin-right:6px;" width="40" height="40" class="left">' + message, buttons, 0);
                            else
                                    popup('HINT', '<img src="game/img/npc/witch_face.png" style="margin-right:6px;" width="40" height="40" class="left">' + message, buttons, 0);
                        }
                    }else if(game.ebHealth > 0) {
                        $('#mining_container').hide();
                        $('#enderbossFight').show(800);
                    }
                }else{
                    //boss area
                    $('#mining_container').hide();
                    if(!game.bossScenario['active']){
                        $('#randomBossPortal').show();
                    }else{
                        if(game.bossScenario['scenario_ID'] == 2)
                            $('#randomBossArea').show();
                        else
                            $('#mining_container').show();
                    }
                }
            }else{
                $('body').css('background-color', 'white');
                $('#quickdetails').css('background-color', 'white');
                $('body').css('color', 'black');
                $('a').css('color', 'black');
                $('h2').css('color', 'black');
                $('#bosses').css('background-color', 'white');

                if(game.ebHealth > 0)
                    earthquake('#container');

                $('#mining_container').fadeIn(2000);
                $('#randomBossPortal').hide();
                $('#randomBossArea').hide();
                $('#shrine').hide();
                $('#orbs').hide();
            }
            
            showUpgrades();
        }

	/* GAME LOADING/SAVING FUNCTIONS */
	function load() {
            $('#loading_screen span[name="connecting"]').hide();
            $('#loading_screen span[name="resources"]').show();

            //set our portal image to the appropriate image
            if (game.portalBuilt)
                    $('#employment div[name="portal"]').html('<img id="portal" src="game/img/icons/portal_unlit.png" width="310" height="365" />');

            if (game.portalLit)
                    $('#employment div[name="portal"]').html('<a href="#" name="portal"><img id="portal" src="game/img/icons/portal_lit.png" width="310" height="365" /></a>');

            if (game.shrineHealth > 0)
                    $('#shrine span[name="health"]').text(game.shrineHealth);

            if (game.ownsResearchLab)
                    loadResearchLab();

            /*if (game.village['formed']){
                $('#employment div[name="village"] div[name="constructvillage"]').hide();
                $('#employment div[name="village"] div[name="overview"]').show();
            }*/

            //load orb health
            if (game.ebHint && game.ebHealth > 0) {
                    if (!areAllOrbsDestroyed()) {
                            for (var orb in game.ebOrbs) {
                                    var health = (game.ebOrbs[orb] < 0) ? 0 : game.ebOrbs[orb];
                                    var percent = (health == 0) ? 100 : 100 - Math.round((health / 1000000) * 100);

                                    $('#orbs span[name="orbhp-' + orb + '"]').text(health);
                                    $('#orbs div[name="orb-' + orb + '"] span').css('width', percent + '%');
                            }
                    } else if (game.ebHealth > 0) {
                            $('#enderbossFight div[name="health"] span').css('width', 100 - Math.round((game.ebHealth / 5000000) * 100) + '%');
                    }
            }

            //load pictures, prevent flashing
            var loaded_pictures = 0;
            var pictures = [
                'game/img/items/pickaxeWood.png', 'game/img/items/pickaxeStone.png',
                'game/img/items/pickaxeIron.png','game/img/items/pickaxeGold.png', 
                'game/img/items/pickaxeDiamond.png', 'game/img/items/pickaxeHeavenly.png', 
                'game/img/items/pickaxeHell.png', 'game/img/items/pickaxeEnder.png', 
                'game/img/blocks/endore.png', 'game/img/blocks/netherquartz.png', 
                'game/img/blocks/glowstone.png', 'game/img/blocks/diamond.png', 
                'game/img/blocks/gold.png', 'game/img/blocks/iron.png', 
                'game/img/blocks/mossycobble.png', 'game/img/blocks/coal.png', 
                'game/img/blocks/stone.png', 'game/img/npc/steve.png', 
                'game/img/npc/miner.png', 'game/img/npc/morris.png', 
                'game/img/npc/heavenlyminer.png', 'game/img/npc/hellMiner.png', 
                'game/img/npc/soldier1.png', 'game/img/npc/soldier2.png', 
                'game/img/npc/soldier3.png', 'game/img/npc/witch.png', 
                'game/img/npc/golemfull.png', 'game/img/icons/vault.png', 
                'game/img/icons/compass.png', 'game/img/npc/golem.png', 
                'game/img/npc/chicken.png', 'game/img/items/swordWooden.png', 
                'game/img/icons/portal_lit.png', 'game/img/icons/flintnsteel.png', 
                'game/img/icons/insurance.png', 'game/img/icons/portal_unlit.png', 
                'game/img/icons/portal_lit.png', 'game/img/icons/attack1.png', 
                'game/img/icons/attack2.png', 'game/img/icons/storage1.png', 
                'game/img/icons/storage2.png', 'game/img/icons/workeropm1.png', 
                'game/img/icons/efficiency1.png', 'game/img/icons/efficiency2.png', 
                'game/img/icons/refinery1.png', 'game/img/icons/refinery2.png', 
                'game/img/icons/insurance.png', 'game/img/items/pickaxeAntimatter.png', 
                'game/img/icons/bossCurrency.png', 'game/img/icons/madscientist.png', 
                'game/img/icons/bosscapupgrade.png', 'game/img/items/item_rare1.png',
                'game/img/items/item_rare2.png', 'game/img/items/item_rare3.png',
                'game/img/items/item_rare4.png', 'game/img/npc/capturedminion.png',
                'game/img/items/ringofefficiency.png', 'game/img/items/godspear.png',
                'game/img/items/cheaplabor.png', 'game/img/npc/gb_capturedminion.png',
                'game/img/icons/general.png'];

            for (var i = 0; i < Object.size(pictures); i++) {
                    $('<img src="' + pictures[i] + '" name="load-' + pictures[i] + '" style="display:none;">').appendTo('body').load(function() {
                            loaded_pictures++;

                            if (loaded_pictures == Object.size(pictures)) {
                                    $('#loading_screen').hide();
                                    updateValues();
                                    drawWorkersTable();
                                    showUpgrades();
                                    drawSoldiers();
                                    updateVaultDisplay();
                                    displayCurrentBoss();
                                    donationGoal();
                                    
                                    if(!game.pickaxeAnimation)
                                        $('#mine_box input[name="animation_toggle"]').prop('checked', false);
                                    
                                    if(!game.dropConfirm)
                                        $('#employment div[name="character"] input[name="dropConfirm"]').prop('checked', false);
                                    
                                    if(game.hasAutoPilot){
                                        $('#autopilot_option').show();
                                        
                                        if(game.autoPilotEnabled)
                                            mine();
                                        else
                                            $('#autopilot_option input').prop('checked', false);
                                    }
                            }
                    });
            }
            
            gameLoaded = true;
	}
        
        function updateCountdown(seconds){
            if(seconds == 60){
                $('#updatebar').fadeIn(500);
            }else{
                if(seconds == 0){
                    $('#updatebar').html('<p>Automatic refresh in two minutes ...</p>');
                    setTimeout(function(){
                        $('#updatebar').html('<p>Refreshing now ...</p>');
                        location.reload();
                    },120000);
                }else{
                    $('#updatebar span[name="seconds"]').text(seconds);
                }
            }
        }

	function updateValues() {
		var d = new Date().getTime();
                
		$('#pickaxe').attr('src', items.pickaxes[game.pickaxe_type].img);
		$('#pickaxe_var_name').text(items.pickaxes[game.pickaxe_type].name);
		$('#pickaxe_var_max').text(items.pickaxes[game.pickaxe_type].max);
		$('#pickaxe_var_sharpness').text(items.pickaxes[game.pickaxe_type].sharpness);
		$('#pickaxe_var_speed').text((items.pickaxes[game.pickaxe_type].speed / 1000) + ' second(s)');
		$('#pickaxe_var_dropchance').text(items.pickaxes[game.pickaxe_type].dropchance + '%');
                $('#money_display').text('$' + moneyFormat(game.money));
                $('#bc_display').text(numberFormat(game.bossCurrency));
		$('#vault_display').text(amountOfOreInVault() + '/' + game.vault_max_storage);
		$('#portalparts_display').text(game.portalParts + '/10');
		if (!game.dcUnlocked)
			$('#zombie_display').text(Math.round((game.zbChance >= 100) ? 100 : game.zbChance) + '%');
		if (game.dcUnlocked && !game.dcRanAway)
			$('#chikolio_display').text(numberFormat(game.dcSoldiers));
		if (game.ulUnlocked && game.shrineHealth > 0){
			$('#underlord_display').text(numberFormat(game.ulSoldiers));
                        $('#shrine span[name="health"]').text(game.shrineHealth);
                }
		if (game.ebUnlocked)
			$('#enderboss_display').text(numberFormat(game.ebSoldiers));

		//update stats table
		$('#employment table[name="stats"] td[name="totalmoney"]').text('$' + numberFormat(game.totalMoneyEarned));
                $('#employment table[name="stats"] td[name="moneypertick"]').text('$' + numberFormat(game.moneyPerTick));
		$('#employment table[name="stats"] td[name="totallootmoney"]').text('$' + numberFormat(game.statLootMoney));
		$('#employment table[name="stats"] td[name="totalworkeropm"]').text(numberFormat(getWorkerTotalOPM()));
		$('#employment table[name="stats"] td[name="wopmtimesresearch"]').text(numberFormat(game.workerOPMResearch));
		$('#employment table[name="stats"] td[name="armystrength"]').text(numberFormat(getArmyStrength()));
		$('#employment table[name="stats"] td[name="totalenemieskilled"]').text(numberFormat(game.statEnemiesKilled));
		$('#employment table[name="stats"] td[name="totaldefenderskilled"]').text(numberFormat(game.statDefendersKilled));
		$('#employment table[name="stats"] td[name="totalbattleswon"]').text(numberFormat(game.statBattlesWon));
		$('#employment table[name="stats"] td[name="totalbattleslost"]').text(numberFormat(game.statBattlesLost));
                $('#employment table[name="stats"] td[name="lasthssubmit"]').text(timeToString(d - game.lastSubmit, ''));

		//update scientists variables here
		if(game.scientistBCMode){
                    $('#employment span[name="scientists_price"]').text(game.scientistCostBC +' BC');
                    $('#employment a[name="scientistBuyMode"]').text('Change currency type to money');
                }else{
                    $('#employment span[name="scientists_price"]').text('$'+numberFormat(1000000 * (Math.pow(1.0005, game.scientists))));
                    $('#employment a[name="scientistBuyMode"]').text('Change currency type to BC');
                }
                
                $('#employment span[name="scientistTime"]').text(game.scientistTime);
                $('#employment span[name="scientists_owned"]').html(game.scientists+game.scientistsBC);
                
                //update boss portal timer
                if((serverTime-game.bossLastGenerated) >= game.bossCooldown){
                    $('#randomBossPortal span[name="timer"]').hide();
                }else{
                    $('#randomBossPortal span[name="timer"]').show();
                    $('#randomBossPortal span[name="timer"]').text('You can summon a new boss in '+timeToString(game.bossCooldown - (serverTime-game.bossLastGenerated), ''));
                }

		//update attack tab
		var attHtml = '';
		if(game.ulIntro) {
                    if(game.ulSoldiers > 0)
                        attHtml += '<button name="launchattack-underlord">Launch attack against the Underlord\'s soldiers.</button>';

                    if(!game.overWorld && game.portal == 0 && game.ulSoldiers == 0 && game.shrineHealth > 0) {
                        if((d - game.shrineLastAttack) >= 30000)
                            attHtml += '<button name="attackshrine">Attack the shrine!</button>';
                        else
                            attHtml += '<button name="attackshrine">Attack the shrine! (' + Math.round(30 - ((d - game.shrineLastAttack) / 1000)) + ')</button>';
                    }
		}

		if(game.ebIntro) {
                    if(!game.overWorld && game.portal == 1 && game.ebSoldiers > 0)
                        attHtml += '<button name="launchattack-enderboss">Launch attack against the Enderboss\'s soldiers.</button>';

                    if(game.ebHealth > 0 && areAllOrbsDestroyed() && (d - game.ebLastAttack) < 30000)
                        $('#enderbossFight button[name="attack_enderboss"]').text('Attack (' + Math.round(30 - ((d - game.ebLastAttack) / 1000)) + ')');
                    else
                        $('#enderbossFight button[name="attack_enderboss"]').text('Attack');
		}

		$('#employment div[name="attack"]').html(attHtml);
                
                //character stats st00f
                var lvl = expLvl(game.exp, true);
                
                var curLvlExp = lvlExp(lvl); //THE XP REQUIRED FOR THEIR CURRNET LEVEL
                var requiredExp = lvlExp(lvl+1); //THE XP REQUIRED FOR THE NEXT LEVEL
                var expProgress = game.exp-curLvlExp; //THE CURRENT EXP THEY HAVE GAINED WHILE ON THIS LEVEL
                var expNeeded = requiredExp-curLvlExp; //THE XP NEEDED FROM CURRNET LEVEL TO NEXT LEVEL
                var lvlPercent = (expProgress/expNeeded)*100; //THE PERCENTAGE COMPLETE TO NEXT LEVEL
                    lvlPercent = (lvlPercent >= 100) ? 100 : lvlPercent;
                var lvlPercentString = (lvlPercent < 100) ? (lvlPercent.toFixed(1)) : 100;
                
                $('div[name="characterStats"] span[name="currentLevel"]').text(numberFormat(lvl));
                $('div[name="characterStats"] span[name="currentEXP"]').text(numberFormat(expProgress));
                $('div[name="characterStats"] span[name="requiredEXP"]').text(numberFormat(requiredExp-curLvlExp));
                $('div[name="characterStats"] div[name="levelBar"] span').css('width', lvlPercent+'%');
                $('div[name="characterStats"] div[name="levelBar"] span').text(lvlPercentString+'%');

		//save time
		if(game.lastSave == 0)
                    $('#save span[name="time"]').text('never');
		else
                    $('#save span[name="time"]').text(timeToString(d - game.lastSave, 'ago'));
                    
                if(amountOfOreInVault() >= game.vault_max_storage)
                    $('#vault p[name="full"]').show();
                else
                    $('#vault p[name="full"]').hide();
                    
                updateVaultDisplay();
                updateWorkers();
                showUpgrades();
                drawItems();
                updateSoldiers();
                displayCurrentBoss();
	}
        
        /* VILLAGE STUFF */
        function constructVillage(){
            if(money >= villageCost && lawMakers >= 10){
                var buttons = {
                    '1' : {
                        'text' : 'Create',
                        'func' : function(){
                            money -= villageCost;
                            lawMakers -= 10;
                            
                            //formed!
                            village['formed'] = true;
                            
                            var democracyHtml = '<ul><li>Start off with 100% happiness</li>';
                            democracyHtml += '<li>Buildings cost less $$$</li><li>Higher chance of random worker recruitment</li></ul>';
                            
                            var dictatorshipHtml = '<ul><li>Start of with lowered happiness</li><li>More loot from pillaging</li>';
                            dictatorshipHtml += '<li>Military Bonuses</li><li>High chance of groups of soldiers being recruited</li></ul>';
                            
                            popup('Democracy', democracyHtml, '', 0);
                            popup('Dictatorship', dictatorshipHtml, '', 0);
                            
                            //lets name our village
                            var buttons = {
                                '1' : {
                                    'text' : 'Form Democracy',
                                    'func' : function(){
                                        var setup = {
                                            'happiness' : 100,
                                            'loyalty' : 100,
                                            'gov' : 'democracy',
                                            'name' : 'My Village'
                                        }
                                        
                                        village['setup'] = setup;
                                    }
                                },
                                '2' : {
                                    'text' : 'Form Dictatorship',
                                    'func' : function(){
                                        var setup = {
                                            'happiness' : 100,
                                            'loyalty' : 100,
                                            'gov' : 'democracy',
                                            'name' : 'My Village'
                                        }
                                        
                                        village['setup'] = setup;
                                    }
                                }
                            }
                            
                            popup('Government Type', 'Which type of government do you wish to form?', buttons, 0);
                            
                            var buttons = {
                                '1' : {
                                    'text' : 'Continue',
                                    'func' : function(){
                                        var name = $('#poup input[name="villagename"]').val();
                                        village['setup']['name'] = name;
                                    }
                                }
                            }
                            
                            popup('Name your village', '<input type="text" name="villagename" size="40">', buttons, 0);
                            
                            var successText = 'Your village has successfully been formed! You can now manage it through the "village" tab.';
                            
                            popup('Village formed!', successText, '', 0);
                        }
                    },
                    '2' : {
                        'text' : 'Cancel',
                        'func' : function(){}
                    }
                }
                
                popup('CONFIRM', 'Are you sure you wish to construct a village at the price of $'+ numberFormat(villageCost) +' and 10 legislators?', buttons, 0);
            }else{
                popup('WHOOPS!', 'Constructing a village requires $'+ numberFormat(villageCost)+ ' and 10 legislators.', '', 0);
            }
        }

	/*
	 * var  img     [XMLObject]
	 * var  type    text
	 */
	function swingPickaxeAnimation(img, type) {
		img.rotate({
			duration : (items.pickaxes[type].speed) / 2,
			animateTo : 45,
			callback : function() {
				img.rotate({
					duration : (items.pickaxes[type].speed) / 2,
					animateTo : 0
				});
			}
		});
	}

	function displayFriends() {
		if (befriendedWitch || befriendedGolem) {
			$('div[name="friends"] div[name="nofriends"]').hide();
			if (befriendedWitch) {
				$('div[name="friends"] div[name="witch"]').show();
			} else if (befriendedGolem) {
				$('div[name="friends"] div[name="golem"]').show();
			}
		}
	}

	function displayCurrentBoss() {
            //probably easiest to hide all bosses
            //then show the current one, right?
            $('#zombie_icon').hide();
            $('#zombie_display').hide();
            $('#chikolio_icon').hide();
            $('#chikolio_display').hide();
            $('#underlord_icon').hide();
            $('#underlord_display').hide();
            $('#enderboss_icon').hide();
            $('#enderboss_display').hide();

            if(game.ebHealth <= 0){
                $('#bc_icon').show();
                $('#bc_display').show();
                $('#quickdetails td[name="boss_separation"]').hide();
            }else if (game.ebUnlocked && game.ebHealth > 0) {
                    $('#enderboss_icon').show();
                    $('#enderboss_display').show();
            } else if (game.ulUnlocked && game.shrineHealth > 0) {
                    $('#underlord_icon').show();
                    $('#underlord_display').show();
            } else if (game.dcUnlocked && !game.dcRanAway) {
                    $('#chikolio_icon').show();
                    $('#chikolio_display').show();
            } else if(!game.befriendedGolem && !game.befriendedWitch) {
                    $('#zombie_icon').show();
                    $('#zombie_display').show();
            }
	}

	//buttons can be an object or string
	//if string (any string), will use default button
	function popup(title, message, buttons, time) {
            if(!popupActive) {
                popupActive = true;

                $('#popup p[name="title"]').text(title);
                $('#popup div[name="content"]').html(message);
                $('#popup span[name="buttons"] :not(button[name="continue"])').remove();
                $('#popup span[name="buttons"] button').hide();

                if(time == 0) {
                    if(typeof buttons == 'object') {
                            //"listen" for a choice
                            var rand = new Date().getTime();
                            //need a unique variable for identifiers
                            for(var button in buttons) {
                                rand = rand + '-' + button;
                                
                                $('#popup span[name="buttons"]').append('<button name="' + rand +'">' + buttons[button].text + '</button> ');
                                $(document).one('click', '#popup button[name="' + rand + '"]', function(button) {
                                        buttons[button].func();

                                        //close message screen or show defined response
                                        if(typeof buttons[button].response != 'undefined') {
                                            popupActive = false;
                                            popup(title, buttons[button].response, '', 0);
                                        }else{
                                            if(typeof buttons[button].delay != 'undefined')
                                                popupHandleStack(true);
                                            else
                                                popupHandleStack();
                                        }

                                }.bind(this, button));
                            }
                    } else {
                            $('#popup button[name="continue"]').show();
                    }
                } else {
                    setTimeout(function() {
                        popupHandleStack();
                    }, (time + 750));
                }

                $('#popup').show();
                $('html, body').animate({scrollTop : 0}, 'slow');
            } else {
                //push to stack
                var args = [0, title, message, buttons, time];
                popupStack.push(args);
            }
	}

	function popupHandleStack(delay) {
            //0 = waiting
            //1 = active
            popupActive = false;

            //remove this popup from stack
            if(typeof popupStack[0] != 'undefined' && popupStack[0][0] == 1)
                popupStack.shift();

            //show next popup in stack
            if(typeof popupStack[0] != 'undefined') {
                popupStack[0][0] = 1;
                popup(popupStack[0][1], popupStack[0][2], popupStack[0][3], popupStack[0][4]);
            }else{
                if(!delay) $('#popup').hide(0);
            }
	}
        
        function lvlExp(lvl){
            return (25*lvl*(1+lvl));
        }
        
        //second parameter decides if we will return a capped level
        function expLvl(exp, limit){
            var lvl = Math.floor((Math.sqrt(625+100*exp)-25)/50);

            if(lvl >= 100 && !limit)
                return 100;
            else
                return lvl;
        }
        
        function getArmyStrength(){
            var strength = 0;
            for (var soldier in game.employedSoldiers) {
                    var kpe = items.soldiers[soldier].kpe;

                    strength += game.employedSoldiers[soldier] * kpe;
            }

            return strength;
        }

        function getWorkerTotalOPM(){
            var opm = 0;
            for (var worker in game.employed) {
                    opm += (game.employed[worker][0] * (items.workers[worker].opm + items.workers[worker].opmModifier));
            }
            return opm;
        }

	rand = function (min, max) {
		return Math.floor((Math.random() * max) + min);
	}

	//credits: http://stackoverflow.com/a/2901298/1748664
	numberFormat = function(x) {
		return Math.round(x).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
        
        moneyFormat = function(x){
//            if(x >= 1000000000000)
//                return ((x/1000000000000).toFixed(0)+'q');
//            else if(x >= 1000000000000)
//                return ((x/1000000000000).toFixed(0)+'t');
//            else if(x >= 1000000000)
//                return ((x/1000000000).toFixed(0)+'b');
//            else if(x >= 1000000)
//                return ((x/1000000).toFixed(0)+'m');
//            else
//                return numberFormat(x);

            return numberFormat(x);
        }

	function removeUndefined(array) {
		var newArray = {};

		if (Object.size(array) > 0) {
			for (var x in array) {
				if ( typeof array[x] != 'undefined')
					newArray[x] = array[x];
			}
		}

		return newArray;
	}

	//credits: http://stackoverflow.com/questions/4627899/how-to-find-length-of-literal-array
	Object.size = function(obj) {
            var size = 0, key;
            for(key in obj) {
                if(obj.hasOwnProperty(key))
                    size++;
            }
            return size;
	};

	//http://stackoverflow.com/questions/10730362/javascript-get-cookie-by-name
	getCookie = function(name){
            var parts = document.cookie.split(name + "=");
            if(parts.length == 2) {
                return parts.pop().split(";").shift();
            }
            return false;
	}
        
        millisecondsToMinutes = function(milliseconds){
            return ((milliseconds/1000)/60).toFixed(0)+' minutes';
        }

	timeToString = function (time,append) {
		time = (time / 1000);
		var timestr = '';

		if (time >= 3600) {
			timestr = Math.round(time / 3600) + ' hours(s)';
		} else if (time >= 60) {
			timestr = Math.round(time / 60) + ' minutes(s)';
		} else {
			timestr = Math.round(time) + ' second(s)';
		}

		return timestr + ' '+ append;
	}

	randomObjectElement = function(obj) {
		var keys = Object.keys(obj)
		return keys[keys.length * Math.random() << 0];
	}

	//credits to: http://stackoverflow.com/questions/210717/using-jquery-to-center-a-div-on-the-screen
	jQuery.fn.center = function() {
		this.css("position", "absolute");
		this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + $(window).scrollTop()) + "px");
		this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + $(window).scrollLeft()) + "px");
		return this;
	};
        
        function htmlEnc(s) {
            return s.replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/'/g, '&#39;')
                .replace(/"/g, '&#34;');
        }
        
        function fileExists(){
            var http = new XMLHttpRequest();
            http.open('HEAD', url, false);
            http.send();
            return http.status!=404;
        }

	function donationGoal(){
	 //get the latest donation goal info
           $.ajax({
            url:'donation.php?get=goal',
            success : function(data){
                    if(data.length>0){
                        data = data.split('-');
                        var amount = parseInt(data[0]);
                        var goal = parseInt(data[1]);
                        var percent = Math.round((amount/goal)*100);

                        $('#donation_goal').html('<hr><b>Monthly Goal ($'+ goal +')</b><br/><div class="bar" name="donation_goal" style="width:200px;"><span style="width:0%;">0%</span></div>');

                        var x = 0;
                        var interval = setInterval(function(){
                            x++;
                            if(x <= percent){
                                if(x <= 100)
                                    $('#donation_goal div[name="donation_goal"] span').css('width', x+'%');

                                $('#donation_goal div[name="donation_goal"] span').html(x+'%');
                            }else{
                                clearInterval(interval);
                            }
                        },25);
                    }else{
                        $('#donation_goal').html('<font size="2" color="red">(failed to retrieve donation goal)</font>');
                    }
                }
            });
	 }
         
         function createAccount(){
            var html = '<table>';
                html += '<tr><td>Username</td><td><input type="text" name="username"></td></tr>';
                html += '<tr><td>Password</td><td><input type="password" name="password"></td></tr>';
                html += '<tr><td>Confirm Password</td><td><input type="password" name="password2"></td></tr>';
            
            var buttons = {
                '1' : {
                    'text' : 'Create Account',
                    'response' : 'Please wait...',
                    'func' : function(){
                        var username = $('#popup input[name="username"]').val();
                        var password = $('#popup input[name="password"]').val();
                        var password2 = $('#popup input[name="password2"]').val();
                        
                        $.ajax({
                           url: 'account/ajax_register.php',
                           dataType: 'JSON',
                           type: 'POST',
                           data : {username:username,password:password,password2:password2},
                           success: function(r){
                               $('#popup div[name="content"]').html(r.result);
                               
                               if(r.success == true){
                                   $('span[name="notLoggedIn"]').hide();
                                   client.emit('quickLogin', getCookie('session'));
                               }
                           }
                        });
                    }
                },
                '2' : {
                    'text' : 'Cancel',
                    'func' : function(){}
                }
            }
            
            popup('Create Account', html, buttons, 0);
         }

	/* listeners/handlers */
	$('button[name="mine"]').click(function() {
		mine();

		if (!game.achievements['downunder'] && !game.overWorld) {
			game.achievements['downunder'] = true;
			popup('ACHIEVEMENT UNLOCKED!', '<table><tr><td><img src="game/img/icons/icon_1.png"></td><td style="font-size:20px;">MINING DOWN UNDER...</td></tr></table>', false, 4000);
		}
	});
	$('#popup button[name="continue"]').click(function() {
		popupHandleStack();
	});

	$(document).on('click', 'button[name="vault_sell"]', function(){
            client.emit('sellVaultOres', true);
        });
	$(document).on('click', 'button[name="vault_options"]', updateVaultSettings);
	$(document).on('click', 'a[name="upgrade_pickaxe"]', upgradePickaxe);
        $(document).on('click', 'a[name="upgrade_lawmaker"]', buyLawMaker);
	$(document).on('click', 'a[name="upgrade_vault"]', upgradeVault);
	$(document).on('click', 'a[name="upgrade_autopilot"]', upgradeAutoPilot);
	$(document).on('click', 'a[name="upgrade_partways"]', partWays);
	$(document).on('click', 'a[name="upgrade_golem"]', befriendGolem);
	$(document).on('click', 'a[name="upgrade_witch"]', befriendWitch);
	$(document).on('click', 'a[name="upgrade_buildportal"]', buildPortal);
	$(document).on('click', 'a[name="upgrade_igniteportal"]', ignitePortal);
        $(document).on('click', 'a[name="upgrade_autowage"]', upgradeAutoWage);
	$(document).on('click', 'a[name="portal"]', function(){
            client.emit('enterPortal', true);
        });
        $(document).on('click', 'a[name="bossPortal"]', function(){
            var buttons = {
                '1' : {
                    'text' : 'Summon Boss',
                    'func' : function(){
                        if(!game.bossScenario['active']){
                            client.emit('summonBoss', true);
                        }else{
                            popup('WHOOPS!', 'You are already fighting a boss.', '', 0);
                        }
                    },
                    'delay' : true
                },
                '2' : {
                    'text' : 'Cancel',
                    'func' : function(){}
                }
            }
            
            popup('SUMMON BOSS', 'Are you sure you wish to summon a boss?', buttons, 0);
        });

	//worker & soldier listeners
	$(document).on('click', 'table[name="workers"] button[name="pay_wages"]', function(){
            client.emit('payWages', true);
        });
	$(document).on('click', 'table[name="workers"] button[name|="buy"]', function(){
            client.emit('buyWorker', $(this).attr('name').split('-')[1]);
        });
	$(document).on('click', 'table[name="workers"] button[name|="buymax"]', buyMaxWorkers);
	$(document).on('click', 'table[name="workers"] button[name|="sell"]', function(){
            client.emit('sellWorker', $(this).attr('name').split('-')[1]);
        });

	$(document).on('click', 'table[name="soldiers"] button[name|="buy"]', function() {
		buySoldier($(this).attr('name').split('-')[1], false)
	});
	$(document).on('click', 'table[name="soldiers"] button[name|="sell"]', function() {
		sellSoldier($(this), false)
	});
	$(document).on('click', 'table[name="soldiers"] button[name|="buyx"]', function() {
            var html = '# of soldiers you wish to purchase<br/><input type="text" name="x_amount" length="30">';
            var soldierType = $(this).attr('name').split('-')[1];

            var buttons = {
                    'continue' : {
                            'text' : 'Buy',
                            'func' : function() {
                                    buySoldier(soldierType, parseInt($('#popup input[name="x_amount"]').val()));
                            }
                    },
                    'cancel' : {
                            'text' : 'Cancel',
                            'func' : function() {
                            }
                    }
            };

            popup('HOW MUCH, EXACTLY?', html, buttons, 0);
	});
        $(document).on('click', 'table[name="soldiers"] button[name|="buymax"]', function() {
            var soldierType = $(this).attr('name').split('-')[1];
            var amount = Math.floor(game.money/items.soldiers[soldierType].price);
            buySoldier(soldierType, amount);
	});
	$(document).on('click', 'table[name="soldiers"] button[name|="sellx"]', function() {
            sellSoldier($(this), true)
	});

	$(document).on('click', '#employment button[name="toggleworkers"]', function() {
            client.emit('toggleWorkers', true);
	});
        $(document).on('click', '#employment div[name="character"] input[name="dropConfirm"]', function(){
            client.emit('toggleDropConfirm');
        });

	//launching attacks
	$(document).on('click', '#employment div[name="attack"] button[name|="launchattack"]', function(){
            var boss = $(this).attr('name').split('-')[1];
            client.emit('launchAttack', boss);
        });
	$(document).on('click', '#employment div[name="attack"] button[name="attackshrine"]', function(){
            client.emit('attackShrine', true);
        });
	$(document).on('click', '#orbs button[name|="attackorb"]', attackOrb);
	$(document).on('click', '#enderbossFight button[name="attack_enderboss"]', attackEnderboss);
        $(document).on('click', '#randomBossArea button[name="attack_randomboss"]', attackRandBoss);

	//special pickaxes
	$(document).on('click', 'a[name="upgrade_hellpickaxe"]', upgradeHellPickaxe);
	$(document).on('click', 'a[name="upgrade_enderpickaxe"]', upgradeEnderPickaxe);
	$(document).on('click', 'a[name="upgrade_finalpickaxe"]', upgradeFinalPickaxe);
        $(document).on('click', 'a[name="upgrade_antimatterpickaxe"]', upgradeAntimatterPickaxe);

	//research!
	$(document).on('click', '#employment div[name="research_options"] a[name|="research"]', startResearch);
	$(document).on('click', '#employment div[name="projects_holder"] a[name|="research_cancel"]', cancelResearch);
	$(document).on('click', '#employment button[name="hire_scientist"]', buyScientist);
	$(document).on('click', '#employment button[name="hiremax_scientists"]', buyMaxScientists);
	$(document).on('click', '#employment button[name="hirex_scientists"]', buyXScientists);
	$(document).on('click', '#employment button[name="fire_scientist"]', sellScientist);
        $(document).on('click', '#employment a[name="scientistBuyMode"]', function(e){
            e.preventDefault();
            client.emit('toggleBuyMode', true);
        });
	$(document).on('click', '#employment div[name="research"] button[name="construct_lab"]', function() {
            if (game.money >= 100000000) {
                client.emit('constructLab', true);
                researchLab();
            }
	});
        $(document).on('click', '#employment div[name="village"] button[name="construct_village"]', constructVillage);
        
        //items
        $(document).on('click', 'button[name|="dropitem"]', function(){
            var item = $(this).attr('name').split('-')[1];
            dropItem(item);
        });
        $(document).on('click', 'button[name|="activateitem"]', function(){
            var item = $(this).attr('name').split('-')[1];
            activateItem(item);
        });

	//tab system
	$(document).on('click', '#employment a[name|="tab"]', function(e) {
		e.preventDefault();
		var tabSplit = ($(this).attr('name').split('-')[1]).split('/');
		var selectedTab = tabSplit[0];
		var elementType = tabSplit[1];

		if (tab != selectedTab) {
			if (selectedTab == 'soldiers' && !game.dcUnlocked) {
				popup('ARMY', 'You have not unlocked this feature yet.', '', 0);
			} else if (selectedTab == 'attack' && !game.ulIntro) {
				popup('ATTACK', 'You have not unlocked this feature yet.', '', 0);
			} else {
				$('#employment a[name|="tab"]').removeClass('selected');
				$('#employment table[type="tab"],div[type="tab"]').hide();
				$('#employment ' + elementType + '[name="' + selectedTab + '"]').fadeIn(750);
				$('#employment a[name="tab-' + selectedTab + '/' + elementType + '"]').addClass('selected');
				tab = selectedTab;
			}
		}
	});

	//enabling/disabling autopilot
	$(document).on('click', '#autopilot_option input[name="autopilot_enabled"]', function() {
            if(game.hasAutoPilot && !game.autoPilotEnabled)
                mine();
            
            client.emit('toggleAutoPilot');
	});
        
        //enabling/disabling pickaxe animation
    	$(document).on('click', '#mine_box input[name="animation_toggle"]', function() {
            if(game.pickaxeAnimation)
                $('#mine_box input[name="animation_toggle"]').prop('checked', false);
            
            client.emit('togglePickaxeAnimation');
	});
        
        $(document).on('change', '#employment div[name="admin"] select[name="users"]', function(){
            $('#employment div[name="admin"] div[name="global"]').hide();
            $('#employment div[name="admin"] div[name="user"]').show();
            client.emit('admin_selectuser', $(this).val());
        });
        $(document).on('click', '#employment div[name="admin"] button[name|="admin"]', function(){
            var userid = ($(this).attr('name').split('-')[1]).split('/')[1];
            var action = ($(this).attr('name').split('-')[1]).split('/')[0];
            
            if(action == 'kick'){
                console.log('Kicking '+ userid +' ...');
                client.emit('kick', userid);
            }else if(action == 'initUpdate'){
                console.log('Update countdown initiated ...');
                client.emit('initUpdate', true);
            }
        });
        
        $(document).on('click', 'a[name="globalBoss-join"]', function(){
            client.emit('globalbossToggleLobby', true);
        });
        
        $(document).on('dragstart', 'img[name="globalBossImg"]', function(e){ e.preventDefault(); });
        
        $(document).on('click', 'img[name="globalBossImg"]', function(e){
            var posX = $('div[name="fightArea"]').position().left,
                posY = $('div[name="fightArea"]').position().top;
                client.emit('globalbossAttack', { x : (e.pageX - posX), y : (e.pageY - posY) });
        });

	//faq
	$('#faq').click(function() {
		var html = '<p><b>Why is this game called "a game?"</b><br/>I haven\'t named it yet.</p>';
                html += '<p><b>I can\'t defeat the Underlord!</b><br/>Only heavenly templars work in the underworld!</p>';
                html += '<p><b>My workers aren\'t doing anything?</b><br/>Workers automatically sell their ore. If you are sure they aren\'t doing anything, make sure they are turned on.</p>';
                html += '<p><b>Worker Efficiency research is broke!</b><br/>No, it\'s not! It researches itself over and over unless you stop it.</p>';
		html += '<p><b>Is it normal for the screen to shake?</b><br/>Yes, after you reach the Underlord, screen shaking is suppose to resemble an earthquake.</p>';
		html += '<p><b>Can soldiers other than templars go into the end?</b><br/>Yes. Templars are only required in the underworld.</p>';
		html += '<p><b>What are worker wages?</b><br/>Every 20 minutes your workers will want to be paid. If you pay them, their happiness either increases/stays the same. If they are not paid, they get angry causing their productivity to go down; they may even quit!</p>';
		html += '<p><b>Do I always get one BC (boss currency)?</b><br/>No. The boss currency you get is determined by the boss difficulty level. Boss difficulty levels go up by one every two bosses defeated; the max boss difficulty level can be increased through research.</p>';
		html += '<p><b>Will there be new bosses?</b><br/>Yep!</p>';

		popup('F.A.Q.', html, '', 0);
	});

	//ore prices
	$('#ore_prices').click(function(){
		var html = 'Please note the prices in this list do include any research that may affect the base price. <hr><table>';

		for(var ore in items.ores)
                    html += '<tr><td><img src="' + items.ores[ore].img + '" /></td><td>' + ore + '<br/>$' + numberFormat(items.ores[ore].worth) + '</td></tr>';

		html += '</table>';

		popup('ORE PRICES', html, '', 0);
	});
        
        $('#create_account').click(createAccount);

	//close updatebar
        $(document).on('click', '#updatebar a[name="closemessage"]', function(){
            $('#updatebar').fadeOut(1000);
        });
        
        //close warning bar
        $(document).on('click', '#warningbar a', function(){
            $('#warningbar').fadeOut(1000);
        });
});