<?php
/*
 *  command - open
 *
 *  opens something closed
 *  
 */
require_once(BASEPATH."/_classes/commander/action.php");

class turn extends action{
    
    public function _execute($args){
        if (count($args)==0){
            $result['action']="OUTPUT";
            $result['data']="What do you want to turn?";
        }
        $can = $this->can($args);

        if ($can->result==true){
            $user = _user();
            $user->set($user->get_logged_in_user_id());
            $location = _location();
            $location->set($user->get_location_id());
            $items = $location->get_items();
            $theItem = null;
            foreach($items as $item){
                if ($item->get_type_id()==$can->item_type->get_id()){
                    $theItem = $item;
                    break;
                }
            }
            if ($theItem==null){
                //check for user items
                $items = $user->get_user_items($can->item_type->name);
                
                if (count($items)>0){
                    $theItem = $items[0];
                }
            
            }
            if ($theItem==null){

                $result = array();
                $result['action']="OUTPUT";
                $result['data'] = "You don't see or have a ".$can->item_type->name;
                return $result;

            }
            
            $subclass = $can->item_type->name;
            $items = new items();
            $items->get_subclass($subclass."s");
            $theSubclass = new $subclass();
            $theSubclass->set($theItem->item_id);
            if (!method_exists($subclass,"turn_".$can->args[0])){
                $result= array();
                $result['action']="OUTPUT";
                $result['data']="Incorrect syntax for that object.";
            };
            
            $method = "turn_".$can->args[0];
            $response = $theSubclass->$method();

            $result['action']="OUTPUT";
            $result['data']=$response['data'];
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $user_loc = $user_loc->id;
            $event_name = $subclass."_".$can->args[0];
            $this->register_event($event_name,$user_id,$user_loc);

        }
        else{
            $result = array();
            $result['action']="OUTPUT";
            $result['data'] = "something went wrong.";
        }
        
        return $result;
    }
    
    public function can($args){
        $words = array();
        foreach($args as $arg){
            $words[]="'".$arg."'";
        }
        $return = new stdClass();
       
        if (!($item_type = $this->is_item($words))){
            $return->result=false;
            $return->reason="I don't know what that is.";
        }
        else{
            $actions = $item_type->actions;
            $actions = json_decode($actions);
            
            if (array_search("turn",$actions)===FALSE){
                $return->result=false;
                $return->reason="That is not something you can turn.";
            }
            else{
                $return->result=true;
                $return->item_type = $item_type;
                $return->args = array();
                foreach($args as $arg){
                    if ($arg != $item_type->name){
                        $return->args[]=$arg;
                    }
                }
            }
            
        }
        
        return $return;
    }
    
    private function is_item($words){
        $words = implode(",",$words);
        $sql = "SELECT * from item_types WHERE name IN ($words)";
        $item_type = $this->db->query($sql);
        if (count($item_type)==0)
            return false;
        return $item_type[0];
    }
    
    private function is_item_in_location($item_type){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $location = $user->get_location($user_id);
        $sql = "SELECT * FROM item_locations JOIN items ON items.ID = item_locations.item_id JOIN item_types ON items.type_id = item_types.id WHERE item_types.id = '".$item_type->id."' AND item_locations.location_id='".$location->id."'";
        $items = $this->db->query($sql);
        if (count($items)==0)
            return false;
        return $items;
    }
    
    private function is_it_locked($item){
        $attributes = json_decode($item->attributes);
        return ($attributes->locked==true);
    }
    
    private function is_it_open($item){
        $attributes = json_decode($item->attributes);
        return ($attributes->state=='open');
    }
    
    private function open_it($item){
        $attributes = json_decode($item->attributes);
        $attributes->state="open";
        $item->attributes=json_encode($attributes);
        $sql = "UPDATE items SET attributes='".$item->attributes."' WHERE id='".$item->item_id."'";
        
        $result = $this->db->query($sql);
        return true;
        
    }
}
?>