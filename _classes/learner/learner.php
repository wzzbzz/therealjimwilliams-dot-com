<?php

require_once(BASEPATH."/_lib/includer.php");

class Learner{
    
    public $db;
    
    public function __construct(){
        $this->db = _db();
    }
    
    public function storeInput($d,$s){
        
        //raw dump
        $this->storeRaw($d,$s);
        
        //word dump
        $this->storeWords($d);
        
    }
    
    public function analyze(){
        
    }
    
    private function storeRaw($d,$s){
        $i = array(
            'table'=>'raw',
            'keyvals'=>array(
                'input'=>$d,
                'session_id'=>$s
            )
        );
        
        return $this->db->insert($i);
    }
    
    private function storeWords($d){
        
        //split on spaces
        $words = explode(" ",$d);
        //loop through all words
        foreach($words as $word){
            $this->storeWord($word);
        }
    }
    
    private function storePhrases(){
        return true;
    }
    
    private function storeWord($word){
        if (!$this->wordExists($word)){
            $this->newWord($word);
        }
        else{
            $this->incrementWord($word);
        }
    }
    
    private function wordExists($word){
        
        $sql = "SELECT COUNT(id) as count FROM words WHERE word='$word'";
        $result = $this->db->query($sql);
        $count = $result[0];
    
        $count = $count->count;

        return ($count>0);
        
    }
    
    private function newWord($word){
        $i = array(
            'table'=>'words',
            'keyvals'=>array(
                'word'=>$word
                )
            );
        $this->db->insert($i);
    }
    
    private function incrementWord($word){
        
        $sql = "SELECT usage_count FROM words WHERE word='$word'";
        $result = $this->db->query($sql);
        $count = $result[0]->usage_count;
        $count++;
        $sql = "UPDATE words set usage_count = '$count' WHERE word='$word'";
        $result = $this->db->query($sql);
        
        return $count;
    }
}
?>