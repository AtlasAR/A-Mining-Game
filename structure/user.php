<?php

class user {
        
    public $id;
    public $username;
    public $donations;
    public $groupID = false;
    public $isLoggedIn = false;
    
    function __construct(database $database) {
        $this->db = $database;
        
        if(isset($_COOKIE['session'])){
            $query = $database->processQuery("SELECT `username`,`id`,`donations`,`group`,`disabled` FROM `users` WHERE `session` = ? LIMIT 1", array($_COOKIE['session']), true);
            
            if($database->getRowCount() == 1 && $query[0]['disabled'] == 0){
                $this->id = $query[0]['id'];
                $this->username = $query[0]['username'];
                $this->donations = $query[0]['donations'];
                $this->groupID = $query[0]['group'];
                $this->isLoggedIn = true;
            }
        }
    }
    
    public function accountsConnectedToIP($ip){
        $query = $this->db->processQuery("SELECT COUNT(*) AS num FROM `users` WHERE `ip` = ? LIMIT 3", array($ip), true);
        return $query[0]['num'];
    }
    
    public function ideaMaker($id = null){
        $userid = ($id !== null) ? $id : $this->id;
        $this->db->processQuery("SELECT * FROM `users` WHERE `id` = ? AND `ideamaker` = 1 LIMIT 1", array($userid), false);
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function muted(){
        $query = $this->db->processQuery("
                SELECT `forum_mute`,`forum_mute_length`
                FROM `users`
                WHERE (`id` = ? OR `ip` = ?)
                AND
                DATE_SUB(NOW(), INTERVAL `forum_mute_length` HOUR) < `forum_mute`
                LIMIT 1", array($this->id, $_SERVER['REMOTE_ADDR']), true);
        return ($this->db->getRowCount() > 0) ? $query : false;
    }
    
    public function postingTooSoon(){
        $this->db->processQuery("
            SELECT users.`id` as userID FROM `users`
            LEFT JOIN `forums_posts` as p ON p.userid = users.`id`
            LEFT JOIN `forums_threads` as t ON t.userid = users.`id`
            WHERE (users.`id` = ? OR users.`ip` = ?)
            AND
            (
                    (DATE_SUB(NOW(), INTERVAL 30 SECOND) < t.`datetime`)
                    OR
                    (DATE_SUB(NOW(), INTERVAL 30 SECOND) < p.`datetime`)
            )
        ", array($this->id, $_SERVER['REMOTE_ADDR']));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function forumBanned(){
        $this->db->processQuery("SELECT * FROM `users` WHERE (`id` = ? OR `ip` = ?) AND `forum_ban` = 1 LIMIT 1", array($this->id, $_SERVER['REMOTE_ADDR']));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function ownsGroup(){
        $query = $this->db->processQuery("SELECT `id` FROM `groups` WHERE `owner` = ? LIMIT 1", array($this->id), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['id'] : false;
    }
    
    public function hasGroup($id){
        $query = $this->db->processQuery("SELECT `group` FROM `users` WHERE `id` = ? LIMIT 1", array($id), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['group'] : false;
    }
    
    public function groupName($id){
        $query = $this->db->processQuery("SELECT `groupname` FROM `groups` WHERE `id` = ? LIMIT 1", array($this->hasGroup($id)), true);
        return $query[0]['groupname'];
    }
    
    public function rights($id){
        $query = $this->db->processQuery("SELECT `rights` FROM `users` WHERE `id` = ? LIMIT 1", array($id), true);
        return ($query[0]['rights'] > 0) ? $query[0]['rights'] : 0;
    }
    
    public function rankTitle($id){
        $rank = $this->rights($id);
        
        if(!$this->ideaMaker($id) || $rank > 0){
            switch($rank){
                case 0:
                    return 'User';
                    break;
                case 1:
                    return 'Moderator';
                    break;
                case 2:
                    return 'Administrator';
                    break;
            }
        }else{
            return 'Idea Maker';
        }
    }
    
    public function userIDExists($id){
        $this->db->processQuery("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function usernameExists($username){
        $this->db->processQuery("SELECT * FROM `users` WHERE `username` = ? LIMIT 1", array($username));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function getUsernameById($id){
        $query = $this->db->processQuery("SELECT `username` FROM `users` WHERE `id` = ? LIMIT 1", array($id), true);
        return $query[0]['username'];
    }
    
    public function inGroup($userid, $groupid){
        $this->db->processQuery("SELECT * FROM `users` WHERE `id` = ? AND `group` = ? LIMIT 1", array($userid, $groupid));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function hashandsalt($password){
        $salt = substr(hash(sha256, sha1(time())), 10);
        $password = $salt.hash(sha256, md5(sha1($password))).substr($salt, 0, -51);
        
        return $password;
    }
    
    public function generateSession($userid = null){
        include_once('base.php');
        $base = new base();
        
        //generate new hash
        $session_hash = $base->randomString(35);

        //update old hash to new one (after checking the hahs doesn't exist)
        $this->db->processQuery("SELECT * FROM `users` WHERE `session` = ?", array($session_hash), false);

        if($this->db->getRowCount() == 0){
            
            if(!is_null($userid))
                $this->db->processQuery("UPDATE `users` SET `session` = ? WHERE `id` = ? LIMIT 1", array($session_hash, $userid));
            
            return $session_hash;
        }else{
            $this->generateSession($userid);
        }
    }
    
    public function createUser($username, $password){
        $password = $this->hashandsalt($password);
        $session = $this->generateSession();
        
        $this->db->processQuery("INSERT INTO `users` VALUES (null, ?, ?, ?, 0, NOW(), ?, ?, 0, 0, 0, 0, 0, 0, 0, '', 0)", array(
            $username,
            $password,
            $session,
            '',
            $_SERVER['REMOTE_ADDR']
        ));
        
        //log them in
        setcookie('session', $session, time()+7200, '/');
        
        return ($this->db->getRowCount() > 0) ? true : false;
    }
}

?>