<?php
/*
 *  command - knock
 *
 *  knock on something that is knockable
 *  
 */
require_once(BASEPATH."/_classes/commander/action.php");

class knock extends action{
    
    public function _execute($args){

        if (count($args)==0){
            $result['action']="OUTPUT";
            $result['data']="What do you want to knock on?";
            return $result;
        }
        $can = $this->can($args);

        if ($can->result==true){
            $this->knock_it($can->item);

            $action_responses = json_decode($can->item->action_responses);
            $response = $action_responses->knock;
            $result['action']="OUTPUT";
            $result['data']=$response;
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $sql = "SELECT * from location_connections WHERE restrictions='{\"door\":".$can->item->item_id."}' AND current_loc='".$user_loc->id."'";
            $con = $this->db->query($sql);
            $con = $con[0];
            $dest = $con->dest;
            $user_loc = $user_loc->id;
            
            $this->register_event('knock',$user_id,$user_loc);
            $this->register_event('knock_outside',$user_id,$dest);

            $result = array();
            $result['action']="OUTPUT";
            $result['data'] = "";
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
            $return->reason="You knocked on it.";
        }
        else{
            $found = $this->resolve_item($item_type, $args);
            $item = $found->item;
            if ($item===null){
                $return->reason=$found->reason;
            }
            else{
                $return->result=true;
                $return->item=$item;
            }
        }
        
        return $return;
    }
    
    
    private function knock_it($item){
        return true;
    }
}
?>