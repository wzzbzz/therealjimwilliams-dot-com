<?php
require_once(BASEPATH."/_classes/commander/action.php");

class connect extends action{
    
    public function __construct(){
        
    }
    
    public function _execute($args){
        $user = _user();
        if ($user_id = $user->get_logged_in_user_id() != 7)
        {
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="who do you think you are, the real jim williams?";
            return $result;
        }

        if (count($args)==0){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Enter a location #ID";
            return $result;
        }
        
        if(!is_numeric($args[0])){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Please enter a location #ID";
            return $result;
        }
        
        if (!isset($args[1])){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Please enter a direction for the connection from this point.";
            return $result;
        }
        
        $user->set($user->get_logged_in_user_id());
        $loc = _location();
        
        $loc->set($user->get_location_id());

        $loc_conn = new location_connections();
        $id = $loc_conn->create();
        
        $loc_conn->set_current_loc($loc->get_id());

        $loc_conn->set_dest($args[0]);
        $loc_conn->set_direction($args[1]);
        $loc_conn->save();

        $result = array();
        $result['action']="OUTPUT";
        $result['data']="Connection created.  Go to the room, and Type \"Connect ".$user->get_location_id()."\" &lt;dir&gt;to make a reverse connection";
        return $result;
    }
    
}

?>