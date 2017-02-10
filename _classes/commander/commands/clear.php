<?php
require_once(BASEPATH."/_classes/commander/command.php");

class clear extends command{
    
    private $script = "
    my_console.clear();  
    ";
    
    public function _execute($args){
        $result = array();
        $result['action'] = "EXECUTE";
        
        $result['data'] = $this->script;
        
        return $result;
    
    }
}
?>