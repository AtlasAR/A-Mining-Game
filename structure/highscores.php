<?php

class highscores {
    private $db;
    
    function __construct(database $database) {
        $this->db = $database;
    }
    
    public function groupTotalMoneyEarned($id){
        $query = $this->db->processQuery("SELECT 
                                        SUM(hs.`money_earned`) as moneyEarned
                                        FROM `highscores` as hs
                                        LEFT JOIN `users` ON hs.`userid` = users.`id`
                                        LEFT JOIN `groups` ON groups.`id` = ?
                                        WHERE users.`group` = ?", array($id, $id), true);
        return $query[0]['moneyEarned'];
    }
    
    public function getRank($id){
        $this->db->processQuery("SET @pos=0;", array());
        $query = $this->db->processQuery("
            SELECT pos FROM
            (
                SELECT h.`userid`,@pos:=(@pos+1) AS pos FROM `highscores` AS h ORDER BY `money_earned` DESC
            ) as x WHERE x.`userid` = ?
        ", array($id), true, 2);
        
        return $query[0]['pos'];
    }
    
    public function getGroupColor($id){
        $query = $this->db->processQuery("SELECT `color` FROM `groups` WHERE `id` = ? LIMIT 1", array($id), true);
        return $query[0]['color'];
    }
    public function groupIdByName($groupname){
        $query = $this->db->processQuery("SELECT `id` FROM `groups` WHERE `groupname` = ? LIMIT 1", array($groupname), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['id'] : false;
    }
    
    public function groupNameByID($id){
        $query = $this->db->processQuery("SELECT `groupname` FROM `groups` WHERE `id` = ? LIMIT 1", array($id), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['groupname'] : false;
    }
    
    public function addMember($userid, $groupID){
        $this->db->processQuery("UPDATE `users` SET `group` = ? WHERE `id` = ? LIMIT 1", array($groupID, $userid));
    }
    
    public function groupExists($group){
        $this->db->processQuery("SELECT * FROM `groups` WHERE `groupname` = ? LIMIT 1", array($group));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function groupExistsByID($id){
        $this->db->processQuery("SELECT * FROM `groups` WHERE `id` = ? LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function isOwner($id,$groupID) {
        $this->db->processQuery("SELECT * FROM `groups` WHERE `owner` = ? AND `id` = ? LIMIT 1", array($id, $groupID));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function createGroup($owner, $groupname){
        $this->db->processQuery("INSERT INTO `groups` VALUES (null, ?, ?, NOW(), 0, '')", array($owner,$groupname));
        $this->db->processQuery("UPDATE `users` SET `group` = ? WHERE `id` = ? LIMIT 1", array($this->db->getInsertId(), $owner));
        return true;
    }
}

?>
