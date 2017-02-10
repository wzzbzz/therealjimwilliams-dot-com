<?php
/*
 *  command - open
 *
 *  opens something closed
 *  
 */
require_once(BASEPATH."/_classes/commander/action.php");

class open extends action{
    
    public function _execute($args){
        if (count($args)==0){
            $result['action']="OUTPUT";
            $result['data']="What do you want to open?";
        }
        $can = $this->can($args);
        if ($can->result==true){
            $this->open_it($can->item);

            $action_responses = json_decode($can->item->action_responses);

            $response = $action_responses->open;
            $result['action']="OUTPUT";
            $result['data']=$response;
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $user_loc = $user_loc->id;
            $this->register_event('open',$user_id,$user_loc);            
        }
        else{
            $result = array();
            $result['action']="OUTPUT";
            $result['data'] = $can->reason;
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
            if (array_search("open",$actions)===FALSE){
                $return->result=false;
                $return->reason="That is unopenable.";
            }
            else{
                if (!($items=$this->is_item_in_location($item_type))){
                    $return->result=false;
                    $return->reason="You can't see a ".$item_type->name." here";
                }
                else{
                    
                    if (count($items)>1){
                        
                        $phrase = implode(" ", $args);
                        $phrase = strtolower(str_replace("the ","",$phrase));
                        
                        foreach($items as $item){
                            $attributes = json_decode($item->attributes);
                            $alias = strtolower($attributes->alias);
 
                            if ($alias==$phrase){
                                $return->result=true;
                                $return->item = $item;
                                break;
                            }
                            else{
                                $return->result=false;
                                $return->reason="Which ".$item_type->name." did you mean?";
                            }
                            
                        }
                    }
                    
                    else{
                       
                        $item=$items[0];
                        if ($this->is_it_open($item)){
                            $return->result=false;
                            $return->reason="That is already open";
                        }
                        elseif($this->is_it_locked($item)){
                            
                            $return->result=false;
                            $return->reason="It's locked";
                        }
                        else{
                            $return->result=true;
                            $return->item=$item;
                        }
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