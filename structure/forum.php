<?php

class forum {
    private $db;
    public $imid = 9;
    
    function __construct(database $database) {
        $this->db = $database;
    }
    
    public function postClass($rights){
        switch($rights){
            case 1:
                return 'mod';
                break;
            case 2:
                return 'admin';
                break;
        }
    }
    
    public function extractCats(){
        return $this->db->processQuery("SELECT `id`,`name` FROM `forums_cats` ORDER BY `level` ASC", array(), true);
    }
    
    public function extractForums($parent){
        return $this->db->processQuery("SELECT `id`,`forum`,`description` FROM `forums` WHERE `parent` = ? ORDER BY `level` ASC", array($parent), true);
    }
    
    public function extractThreads($id){
        return $this->db->processQuery("SELECT * FROM `forums_threads` WHERE `parent` = ? ORDER BY `lastreply` DESC", array($id), true);
    }
    
    public function forumPostCount($id){
        $query = $this->db->processQuery("
            SELECT COUNT(*) as tCount, SUM(x.pCount) AS pCount FROM
            (
                            (
                                            SELECT COUNT( p.`id` ) AS pCount
                                            FROM  `forums`
                                            LEFT JOIN `forums_threads` AS t on t.`parent` = ?
                                            LEFT JOIN `forums_posts` AS p ON t.`id` = p.`thread` 
                                            WHERE  `forums`.`id` = ?
                                            GROUP BY t.`id`
                            ) as x
            )
        ", array($id,$id), true);
        
        return array('threads' => $query[0]['tCount'], 'posts' => $query[0]['pCount']);
    }
    
    public function threadPostCount($id){
        $query = $this->db->processQuery("SELECT COUNT(*) as postCount FROM `forums_posts` WHERE `thread` = ?", array($id), true);
        return $query[0]['postCount'];
    }
    
    public function forumExists($id){
        $this->db->processQuery("SELECT * FROM `forums` WHERE `id` = ? LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function threadExists($id){
        $this->db->processQuery("SELECT * FROM `forums_threads` WHERE `id` = ? LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function forumNameByID($id){
        $query = $this->db->processQuery("SELECT `forum` FROM `forums` WHERE `id` = ? LIMIT 1", array($id), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['forum'] : false;
    }
    
    public function threadNameByID($id){
        $query = $this->db->processQuery("SELECT `title` FROM `forums_threads` WHERE `id` = ? LIMIT 1", array($id), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['title'] : false;
    }
    
    public function postHidden($id){
        $this->db->processQuery("SELECT * FROM `forums_posts` WHERE `id` = ? AND `hidden` = 1 LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function threadLocked($id){
        $this->db->processQuery("SELECT * FROM `forums_threads` WHERE `id` = ? AND `locked` = 1 LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function threadStickied($id){
        $this->db->processQuery("SELECT * FROM `forums_threads` WHERE `id` = ? AND `pinned` = 1 LIMIT 1", array($id));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function canMakeThread($id, $rights){
        $query = $this->db->processQuery("SELECT `type` FROM `forums` WHERE `id` = ? LIMIT 1", array($id), true);
        
        if($query[0]['type'] == 1 && !($rights >= 1))
            return false;
        else
            return true;
    }
    
    public function toggleHidePost($id){
        if($this->postHidden($id)){
            $this->db->processQuery("UPDATE `forums_posts` SET `hidden` = 0 WHERE `id` = ? LIMIT 1", array($id));
            return false;
        }else{
            $this->db->processQuery("UPDATE `forums_posts` SET `hidden` = 1 WHERE `id` = ? LIMIT 1", array($id));
            return true;
        }
    }
    
    public function toggleSticky($id){
        if($this->threadStickied($id)){
            $this->db->processQuery("UPDATE `forums_threads` SET `pinned` = 0 WHERE `id` = ? LIMIT 1", array($id));
            return false;
        }else{
            $this->db->processQuery("UPDATE `forums_threads` SET `pinned` = 1 WHERE `id` = ? LIMIT 1", array($id));
            return true;
        }
    }
    
    public function toggleLock($id){
        if($this->threadLocked($id)){
            $this->db->processQuery("UPDATE `forums_threads` SET `locked` = 0 WHERE `id` = ? LIMIT 1", array($id));
            return false;
        }else{
            $this->db->processQuery("UPDATE `forums_threads` SET `locked` = 1 WHERE `id` = ? LIMIT 1", array($id));
            return true;
        }
    }
    
    public function replyBelongsToThread($id, $thread){
        $this->db->processQuery("SELECT * FROM `forums_posts` WHERE `id` = ? AND `thread` = ? LIMIT 1", array($id, $thread));
        return ($this->db->getRowCount() > 0) ? true : false;
    }
    
    public function getThreadOwner($id){
        $query = $this->db->processQuery("SELECT `userid` FROM `forums_threads` WHERE `id` = ? LIMIT 1", array($id), true);
        return $query[0]['userid'];
    }
    
    public function getThreadParent($id){
        $query = $this->db->processQuery("SELECT `parent` FROM `forums_threads` WHERE `id` = ? LIMIT 1", array($id), true);
        return $query[0]['parent'];
    }
    
    public function threadLastPoster($id){
        $query = $this->db->processQuery("SELECT `userid` FROM `forums_posts` WHERE `thread` = ? ORDER BY `id` DESC LIMIT 1", array($id), true);
        return ($this->db->getRowCount() > 0) ? $query[0]['userid'] : $this->getThreadOwner($id);
    }
    
    public function forumLastPoster($id){
        $query = $this->db->processQuery("SELECT t.`id` as threadID, t.`lastreply` as lastreply FROM `forums_threads` as t WHERE t.`parent` = ? ORDER BY `lastreply` DESC LIMIT 1", array($id), true);
        return array(
            'userid' => $this->threadLastPoster($query[0]['threadID']),
            'time' => $query[0]['lastreply']
        );
    }
    
    public function deleteThread($id){
        $this->db->processQuery('DELETE FROM `forums_posts` WHERE `thread` = ?', array($id));
        $this->db->processQuery('DELETE FROM `forums_threads` WHERE `id` = ? LIMIT 1', array($id));
    }
}

?>