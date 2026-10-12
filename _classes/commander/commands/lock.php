<?php
/*
 *  command - lock
 *
 *  locks something that is lockable
 *  
 */
require_once(BASEPATH."/_classes/commander/action.php");

class lock extends action{
    
    public function _execute($args){
        if (count($args)==0){
            $result['action']="OUTPUT";
            $result['data']="What do you want to lock?";
            return $result;
        }
        $can = $this->can($args);
        if ($can->result==true){
            $this->lock_it($can->item);

            $action_responses = json_decode($can->item->action_responses);

            $response = $action_responses->lock;
            $result['action']="OUTPUT";
            $result['data']=$response;
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $user_loc = $user_loc->id;
            $this->register_event('lock',$user_id,$user_loc);            
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
        elseif (array_search("lock",json_decode($item_type->actions))===FALSE){
            $return->reason="That is not a lockable thing.";
        }
        else{
            $found = $this->resolve_item($item_type, $args);
            $item = $found->item;
            if ($item===null){
                $return->reason=$found->reason;
            }
            elseif(!$this->has_lock($item)){
                $return->reason="There is no lock on this door.";
            }
            elseif($this->is_it_locked($item)){
                $return->reason="That is already locked";
            }
            elseif(!$this->user_has_key($item)){
                $return->reason="You don't have the key";
            }
            else{
                $return->result=true;
                $return->item=$item;
            }
        }
        
        return $return;
    }
    
    private function has_lock($item){
        $attributes = json_decode($item->attributes);
        // a door is lockable only if it was authored with a lock: it carries a
        // key id (and/or an explicit locked flag). A lockless door
        // ({"state":"closed"}) must not be lockable on demand.
        return (isset($attributes->key) && $attributes->key !== null && $attributes->key !== "")
            || property_exists($attributes, 'locked');
    }

    private function is_it_locked($item){
        $attributes = json_decode($item->attributes);
        return $attributes->locked;
    }
    
    private function user_has_key($item){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        
        $sql = "SELECT * from item_users JOIN items ON item_users.item_id=items.id JOIN item_types ON items.type_id=item_types.id WHERE item_users.user_id='".$user_id."' AND item_types.name='key'";
        $keys = $this->db->query($sql);
        foreach($keys as $key){
            $attributes = json_decode($key->attributes);
            $opens = $attributes->opens;
            if ($opens==0 || $opens===null){
                // skeleton key: opens anything
                return true;
            }
            if (!is_array($opens)){
                $opens = array($opens);
            }
            if (in_array((string)$item->item_id, array_map("strval", $opens))){
                return true;
            }
        }
        return false;
    }
    private function lock_it($item){
        $attributes = json_decode($item->attributes);
        $attributes->locked=true;
        $item->attributes=json_encode($attributes);
        $sql = "UPDATE items SET attributes='".$this->db->escape($item->attributes)."' WHERE id='".intval($item->item_id)."'";
        
        $result = $this->db->query($sql);
        return true;
        
    }
}
?>
