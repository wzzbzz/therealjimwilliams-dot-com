<?php
require_once(BASEPATH."/_classes/commander/action.php");

class sit extends action{
    
    public function _execute($args){
        $can = $this->can($args);

        if ($can["result"]==true){
            
            $user = _user();
            $user->set($user->get_logged_in_user_id());
            _items();
            $items = new items();
            $item = new item();
            $items_here = $items->get_items_in_location($user->get_location_id());
            
            // straight up sit.
            if (count($args)==0){
                $this->sit_down();
                $result['action']="OUTPUT";
                $result['data']="you sit down on the floor";
                $user = _user();
                $user_id = $user->get_logged_in_user_id();
                $user_loc = $user->get_location($user_id);
                $user_loc = $user_loc->id;
                $this->register_event('sit',$user_id,$user_loc);
                
                return $result;
            }
            
            $have_item = false;
            foreach($args as $arg){
                if (strtoupper($arg)=="ON")
                    // check if it's something you can sit ON
                    continue;
                
                //
                foreach($items_here as $item_here){
                    $item->set($item_here->item_id);
                    $type = $item->get_type();
                    
                    if ($arg == $type){
                        $have_item = true;
                        break;
                    }
                }
                
                if ($have_item==false){
                    $result = array();
                    $result['action']="OUTPUT";
                    $result['data']="You can't see a $arg here.";
                    return $result;
                    break;
                }
                
                else{
                    if ($item->get_attribute("occupant_count")==$item->get_attribute("max_occupants")){
                        $result = array();
                        $result['action']="OUTPUT";
                        $result['data']="Seat's taken.";
                    }
                    else{
                        $this->sit_on_it($item->get_id());
                        $result = array();
                        $result['action']="OUTPUT";
                        $result['data']="You sit down on ".$item->get_title();
                        $user = _user();
                        $user_id = $user->get_logged_in_user_id();
                        $user_loc = $user->get_location($user_id);
                        $user_loc = $user_loc->id;
                        $this->register_event("sit",$user_id,$user_loc);
                        return $result;
                    }
                }
                
                
            }
            
            

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
        $position = $user->get_attribute("position");
        if ($position=="seated"){
            $result=array();
            $result['result']=false;
            $result['reason']="You're already sitting.";
        }
        else{

            $result['result']=true;
        }
        return $result;
    }
    
    private function sit_down($item_id=null){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $user->set($user_id);
        $user->set_attribute("position","seated");
        $user->save_attributes();
        return true;
        
    }
    
    private function sit_on_it($item_id){
        
        $user = _user();
        $user->set($user->get_logged_in_user_id());
        _items();
        $item = new item();
        $item->set($item_id);
        
        $user_attributes = $user->get_attributes();
        $user->set_attribute("position","seated");
        $user->set_attribute("seat",$item_id);
        $user->save_attributes();
        
        $item_attributes = $item->get_attributes();
        $occupant_count = $item->get_attribute("occupant_count");
        $occupant_count++;
        $item->set_attribute("occupant_count",$occupant_count);
        $item->set_attribute("occupant_id", $user->get_id());
        $item->save_attributes();
        
    }
}
?>