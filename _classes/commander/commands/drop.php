<?php
require_once(BASEPATH."/_classes/commander/action.php");

class drop extends action{
    
    public function _execute($args){
        $can = $this->can($args);
        if ($can->result==true){

            $this->drop_it($can->item->item_id);

            $action_responses = json_decode($can->item->action_responses);

            $response = $action_responses->drop;
            $result['action']="OUTPUT";
            $result['data']=$response;
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $user_loc = $user_loc->id;
            $this->register_event('drop',$user_id,$user_loc);            
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
            if (array_search("drop",$actions)===FALSE){
                $return->result=false;
                $return->reason="Exactly how were you intending to drop that?";
            }
            else{
                if (!($items=$this->user_has($item_type))){
                    $return->result=false;
                    $return->reason="You don't have a ".$item_type->name.".";
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
                                $return->result=true;
                                $return->item=$items[0];
                            }
                            
                        }
                    }
                    
                    else{
                        $return->result=true;
                        $return->item=$items[0];
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
    
    private function user_has($item_type){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $sql = "SELECT * FROM item_users JOIN items ON items.ID = item_users.item_id JOIN item_types ON items.type_id = item_types.id WHERE item_types.id = '".$item_type->id."' AND item_users.user_id='".$user_id."'";

        $items = $this->db->query($sql);
        if (count($items)==0)
            return false;
        return $items;
    }
    
    private function drop_it($item_id){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $location = $user->get_location($user_id);
        $sql = "DELETE FROM item_users WHERE item_id='".$item_id."'";
        $r = $this->db->query($sql);
        $sql = "INSERT INTO item_locations (item_id, location_id) VALUES ('".$item_id."','".$location->id."')";
        $r = $this->db->query($sql);
        return true;
        
    }
}
?>