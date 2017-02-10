<?php
require_once("command.php");

class action extends command{
    
    // name of the command
    protected $item_types;
    
    // id of the user that is issuing the command.
    protected $user_id;
    
    public function find_item(){
        
    }
    
    public function item_can($item_id){
        
    }
    
    public function can(){
        
    }
    
    protected function requires($args){
        
    }
    
}
?>