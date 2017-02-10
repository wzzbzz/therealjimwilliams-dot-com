<?php
require_once(BASEPATH."/_classes/commander/command.php");

class login extends command{

    public function _execute($args){
        
        $js = $this->get_js();
        
        $result = array();
        $result['action'] = "EXECUTE";
        $result['data'] = $js;
        return $result;
    }    
}
?>