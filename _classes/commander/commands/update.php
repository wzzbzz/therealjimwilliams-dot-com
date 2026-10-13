<?php
require_once(BASEPATH."/_classes/commander/action.php");

class update extends action{
    
    public function __construct(){
        
    }
    
    public function _execute($args){
        $user = _user();
        if (!$user->user_has_permission('manage_locations')){
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
        if ($obj=="item"){
            return $this->update_item($user,$args);
        }
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

    // update item <door phrase> <alias|title> <words...>
    // sets the alias or title of a matching item in the current room, so a door
    // (or other item) built bare can be named in-game without raw SQL. The door
    // phrase picks one item the same way open/close/lock do: "door",
    // "office door", "door north".
    private function update_item($user,$args){
        if (!$user->user_has_permission('manage_items')){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="who do you think you are, the real jim williams?";
            return $result;
        }

        $selector = array();
        while (count($args)>0 && !in_array($args[0], array("alias","title"))){
            $selector[] = array_shift($args);
        }
        $field = array_shift($args);

        if (count($selector)==0 || !$field){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Usage: update item <type> [which] <alias|title> <words>";
            return $result;
        }
        if (count($args)==0){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Please enter a value : update item ".implode(" ",$selector)." $field <words>";
            return $result;
        }
        $value = implode(" ", $args);

        $user->set($user->get_logged_in_user_id());
        if (!$user->get_location_id()){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="You have no location yet - run 'begin' first.";
            return $result;
        }

        if (!($item_type = $this->find_item_type($selector))){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="I don't know what that is.";
            return $result;
        }
        $found = $this->resolve_item($item_type, $selector);
        if ($found->item===null){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']=$found->reason;
            return $result;
        }
        $target = $found->item;

        _items();
        $item = new item();
        $item->set($target->item_id);
        if ($field=="alias"){
            $item->set_attribute("alias", strtolower($value));
            $item->save_attributes();
        }
        else{
            $item->set_title($value);
        }

        $result=array();
        $result['action']="OUTPUT";
        $result['data']="You've updated the ".$field;
        return $result;
    }
    
}

?>
