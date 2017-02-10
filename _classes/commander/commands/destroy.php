<?php
require_once(BASEPATH."/_classes/commander/action.php");

class destroy extends action{

    private $actor;
    
    public function _execute($args){
        $user = _user();
        if ($user_id = $user->get_logged_in_user_id() != 7)
        {
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="who do you think you are, the real jim williams?";
        }
        
        _items();
        $user->set($user->get_logged_in_user_id());
        
        $can = $this->actor_can($args);
        if ($can==true){
            if (count($args)==0)
            {
                $output = "You destroyed it.";
                
                $result=array();
                $result['action']="OUTPUT";
                $result['data']=$output;
                return $result;
            }
            
            else{
                $item = new item();
                $items = new items();
                $items_in_location = $items->get_items_in_location($user->get_location_id(),$args[0]);
                if (count($items_in_location)>1){
                    //disambiguate
                }
                    $item->delete_item($items_in_location[0]->item_id);

                    $this->register_event("destroys",$user->get_id(),$user->get_location_id());
                    $result = array();
                    $result['action']="OUTPUT";
                    $result['data']="You destroyed item #".$items_in_location[0]->item_id;
    
            }
        }
        return $result;
    }
    
    
       
    private function actor_can(){
        return true;
    }
  
}
?>