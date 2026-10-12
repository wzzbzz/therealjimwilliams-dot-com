<?php

require_once(BASEPATH."/_classes/commander/command.php");

class begin extends command{
    
    public function _execute($args = null){
        $location = _location();
        $user = _user();
        $location_id = $location->get_location_by_name('welcome_center');
        $user_id = $user->get_logged_in_user_id();

        $location_id = $location_id->id;
        
        $result = array();
        $result['action'] = "OUTPUT";
        
        $user->set_location($user_id, $location_id);
        
        $result['data'] = "With a sound of breaking wind, you feel yourself materialize into someplace else."; 
        return $result;
    }
    
}
?>