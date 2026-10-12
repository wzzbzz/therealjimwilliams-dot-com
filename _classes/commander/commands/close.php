<?php
/*
 *  command - close
 *
 *  closes something open
 *  
 */
require_once(BASEPATH."/_classes/commander/action.php");

class close extends action{
    
    public function _execute($args){
        if (count($args)==0){
            $result['action']="OUTPUT";
            $result['data']="What do you want to close?";
            return $result;
        }
        $can = $this->can($args);
        if ($can->result==true){
            $this->close_it($can->item);

            $action_responses = json_decode($can->item->action_responses);

            $response = $action_responses->close;
            $result['action']="OUTPUT";
            $result['data']=$response;
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $sql = "SELECT dest FROM location_connections WHERE restrictions='{\"door\":".$can->item->item_id."}' AND current_loc='".$user_loc->id."'";
            $con = $this->db->query($sql);
            $user_loc = $user_loc->id;
            $this->register_event('close',$user_id,$user_loc);
            if (count($con) > 0){ $this->register_event('close',$user_id,$con[0]->dest); }
        }
        else{
            $result = array();
            $result['action']="OUTPUT";
            $result['data'] = $can->reason;
        }
        
        return $result;
    }
    
    public function can($args = null){
        $return = new stdClass();
        $return->result=false;
       
        if (!($item_type = $this->find_item_type($args))){
            $return->reason="I don't know what that is.";
        }
        elseif (array_search("open",json_decode($item_type->actions))===FALSE){
            $return->reason="That is unclosable.";
        }
        else{
            $found = $this->resolve_item($item_type, $args);
            $item = $found->item;
            if ($item===null){
                $return->reason=$found->reason;
            }
            elseif(!$this->is_it_open($item)){
                $return->reason="That is already closed";
            }
            else{
                $return->result=true;
                $return->item=$item;
            }
        }
        
        return $return;
    }
    
    private function is_it_open($item){
        $attributes = json_decode($item->attributes);
        return ($attributes->state=='open');
    }
    
    private function close_it($item){
        $attributes = json_decode($item->attributes);
        $attributes->state="closed";
        $item->attributes=json_encode($attributes);
        $sql = "UPDATE items SET attributes='".$this->db->escape($item->attributes)."' WHERE id='".intval($item->item_id)."'";
        
        $result = $this->db->query($sql);
        return true;   
    }
}
?>