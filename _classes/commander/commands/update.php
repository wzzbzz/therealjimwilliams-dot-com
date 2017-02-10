<?php
require_once(BASEPATH."/_classes/commander/action.php");

class update extends action{
    
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
            $result['data']="Incomplete command. ";
            return $result;
        }
        
        $obj = array_shift($args);
        if($obj!="location"){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Not setting that any time soon. Maybe later?";
            return $result;
        }
    
        $loc = _location();
        
        $field = array_shift($args);
        
        if (!method_exists("location","set_".$field)){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="that's not part of a location that we can set.";
            return $result;
        }
        
        if ($field == "attribute"){
            if (count($args)==0){
                $result=array();
                $result['action']="OUTPUT";
                $result['data']="please enter your attributes :  type set location attribute <attr> <value> ";
                
                return $result;
            }
            $attr = array_shift($args);
            if (count($args)==0){
                $result = array();
                $result['action']="OUTPUT";
                $result['data']="Please enter a value for this.  :  set location attribute $attr <value>";
                return $result;
            }
            $user->set($user->get_logged_in_user_id());            
            $loc->set($user->get_location_id());
            $loc->set_attribute($attr,implode(" ",$args));
          
        }
        else{
            $user->set($user->get_logged_in_user_id());
            $loc->set($user->get_location_id());
            $method = "set_".$field;
            $loc->$method(implode(" ",$args));
        }
        
        $loc->save();
        
        $result = array();
        $result['action']="OUTPUT";
        $result['data']="You've updated the $field";
        return $result;
    }
    
}

?>