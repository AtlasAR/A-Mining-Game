var module_wildcard = require('socket.io-wildcard');
var module_resources = require('resources');
var module_funcs = require('funcs');
var module_control = require('control');
var module_player = require('player');
var module_game = require('game');
var module_bosses = require('bosses');
var module_globalboss = require('globalboss');
var module_fs = require('fs');

//SERVER VARS
var servVars = {
    testing : false
};

var mysql = require('mysql');
var mysql_pool = mysql.createPool({
    host : 'localhost',
    user : 'root',
    password : '',
    database : 'agame'
});

//start server
var server = require('socket.io');
server = module_wildcard(server).listen(466);

server.set('log level', 1);

//players
var players = {};

//let's extract all the donators, and add their names to the
//globalboss & random boss lists
var donors = [];

mysql_pool.getConnection(function(err, conn){
    if(!err){
        var sql = 'SELECT `username` FROM `users` WHERE `donations` >= 5';
        conn.query(sql, function(err, results){
            conn.destroy();

            if(!err){
                for(var i = 0; i < results.length; i++)
                    donors.push(results[i].username);
            }else{
                console.log(err);
            }
        });
    }else{
        console.log(err);
    }
});

//global modules (all players share these)
var funcs = new module_funcs.Funcs;
var control = new module_control.Control(server.sockets);
var globalboss = new module_globalboss.globalBoss(funcs, server.sockets, players, donors);

server.sockets.on('connection', function(client){
    var resources = new module_resources.Resources();
    var player = new module_player.Player(mysql_pool, resources, players, client, server.sockets, servVars);
    var game = new module_game.Game(player, funcs, control, globalboss);
    var bosses = new module_bosses.Bosses(game, donors);
    
    var saveInterval;
    var submitInterval;
    
    if(globalboss.lobbyActive)
        globalboss.sendTime();
    
    client.on('login', function(session){
        player.login(session);
    });
    client.on('disconnect', function(){
        client.removeAllListeners();
        clearInterval(saveInterval);
        clearInterval(submitInterval);
        
        if(player.loggedIn && (typeof players[player.userid] != 'undefined' && players[player.userid].active))
            players[player.userid].active = false;
        
        
        //make sure they are in order to clear references from
        //eachother!
        game.denit();
       
        bosses = null;
        game = null;
        player = null;
        resources = null;
    });

    //save user data every minute
    saveInterval = setInterval(function(){
        if(player.loggedIn && (typeof players[player.userid] != 'undefined' && players[player.userid].active)){
            player.save();
        }
    },60000);
    //submit user data every minute
    submitInterval = setInterval(function(){
        if(player.loggedIn && (typeof players[player.userid] != 'undefined' && players[player.userid].active)){
            player.submit();
        }
    },600000);
    
    //flood prevention
//    client.on('*', function(){
//        //have they sent an event within the last 30 milliseconds?
//        var d = new Date().getTime();
//
//        var timeDiff = d-player.lastEvent;
//        if(timeDiff < 30){
//            player.floodflags++;
//            
//            //they have three chances, or "warnings"
//            if(player.floodflags > 3){
//                module_fs.appendFile('floodlog.txt', player.ip+' flagged for flooding ('+ timeDiff +' milliseconds).\n', function (err) {
//                    if(err) console.log(err);
//                });
//
//                game.popup('You have been kicked', 'You have been kicked for a possible flood attempt.', '', 0);
//                console.log(player.ip+' was disconnected due to possible flooding.');
//                client.disconnect();
//            }
//        }else{
//            player.lastEvent = d;
//        }
//    });
});

//setInterval(function(){
//    control.updatePlayerCount(funcs.objSize(players));
//}, 300000);