<?php
require_once(BASEPATH."/_classes/commander/command.php");

class logout extends command{

    public function _execute($args){
        
        $sql = "DELETE FROM live_sessions WHERE session_id='".$this->session_id."'";
        $mysql_result = $this->db->query($sql);
        session_destroy();

        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "SESSION LOGGED OUT";
        return $result;
    }    
}
?>