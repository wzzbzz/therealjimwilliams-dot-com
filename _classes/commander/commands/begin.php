<?php

require_once(BASEPATH."/_classes/commander/command.php");

class begin extends command{
    
    public function _execute(){
        $location = _location();
        $user = _user();
        $location_id = $location->get_location_by_name('welcome_center');
        $user_id = $user->get_logged_in_user_id();

        $location_id = $location_id->id;
        $sql = "UPDATE user_locations SET location_id = '".$location_id."' WHERE user_id ='".$user_id."'";
        
        $result = array();
        $result['action'] = "OUTPUT";
        
        $begin = $this->db->query($sql);
        
        $result['data'] = "With a sound of breaking wind, you feel yourself materialize into someplace else."; 
        return $result;
    }
    
}
?>