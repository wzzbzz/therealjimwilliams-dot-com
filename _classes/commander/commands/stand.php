<?php
require_once(BASEPATH."/_classes/commander/action.php");

class stand extends action{
    
    public function _execute($args){
        $can = $this->can($args);

        if ($can["result"]==true){
            
            $this->stand_up();
            
            $result['action']="OUTPUT";
            $result['data']="you stand up";
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $user_loc = $user_loc->id;
            $this->register_event('stand',$user_id,$user_loc);

        }
        else{
            $result = array();
            $result['action']="OUTPUT";
            $result['data'] = $can["reason"];
        }
        
        return $result;
    }
    
    public function can($args){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $user->set($user_id);
        $attributes = $user->get_attributes();
        $position = $attributes->position;

        if ($position=="standing"){
            $result=array();
            $result['result']=false;
            $result['reason']="You're already standing.";
        }
        else{
            $attributes->position="standing";
            $result['result']=true;
        }
        return $result;
    }
    
    private function stand_up(){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $user->set($user_id);
        $user->set_attribute("position","standing");
        $seat = $user->get_attribute("seat");
        if ($seat>0){
            _items();
            $item = new item();
            $item->set($seat);
            $occupant_count = $item->get_attribute("occupant_count");
            $occupant_count--;
            $item->set_attribute("occupant_count",$occupant_count--);
            $item->set_attribute("occupant_id",null);
            $item->save_attributes();
        }
        $user->set_attribute("seat",null);
        $user->save_attributes();
        return true;
        
    }
}
?>