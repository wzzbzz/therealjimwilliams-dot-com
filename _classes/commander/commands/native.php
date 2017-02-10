<?php
require_once(BASEPATH."/_classes/commander/command.php");

class native extends command{

    public function _execute($args){
        
        $js = $this->get_js();
        $js = str_replace("[[ACTION]]",$args[0],$js);
        $result = array();
        $result['action'] = "EXECUTE";
        $result['data'] = $js;
        return $result;
    }    
}
?>