<?php
require_once(BASEPATH."/_classes/commander/action.php");

class eat extends action{

    private $actor;
    
    public function _execute($args){
        $user = _user();
        _items();
        $user->set($user->get_logged_in_user_id());
        if ($user->get_attribute("status")=="eating"){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="you're already eating";
            return $result;
        }
        elseif($user->get_attribute("hunger")==0){
                        $result=array();
            $result['action']="OUTPUT";
            $result['data']="you're full.";
            return $result;

        }
        $this->actor = $user;
        $can = $this->actor_can($args);
        if ($can==true){
            if (count($args)==0)
            {
                $foodies = $this->actor_has_food();
                if ($foodies){
                    $item = new item();
                    $output = "<div>You can eat:</div>";
                    foreach($foodies as $food){
                        $item->set($food->item_id);
                        $output .= "<div>".$item->get_title()."</div>";
                    }
 
                }
                else
                    $output = "You eat air.";
                
                $result=array();
                $result['action']="OUTPUT";
                $result['data']=$output;
                return $result;
            }
            
            else{
                $item = new item();
                if (!$item_type=$item->is_item($args)){
                    $result=array();
                    $result['action']="OUTPUT";
                    $result['data']="What exactly are you trying to eat?";
                }
                else{
                    
                    $items = new items();
                    $hierarchy = $item->get_type_hierarchy($items->get_type_id("food"));
                    if (array_search($item_type->id,$hierarchy)===FALSE){
                        $result=array();
                        $result['action']="OUTPUT";
                        $result['data']="You can't eat a ".$item_type->name;
                    }
                    else{
                        //now get user items of the type...do we have more than one type?
                        $user_items = $user->get_user_items($item_type->name);
                        if ($user_items == false){
                            $result = array();
                            $result['action']="OUTPUT";
                            $result['data']="You don't have a ".$item_type->name.".";
                            return $result;
                        }
                        $item = new item();
                        $item->set($user_items[0]->item_id);
                        if ($item->get_attribute("nourishment")==0){
                            $result = array();
                            $result['action']="OUTPUT";
                            $result['data']="You already demolished that, mate.";
                            return $result;
                        }
                        //register events
                        //bites!
                        $hunger = $item->get_attribute("nourishment");
                        $user->set_attribute("status","eating");
                        $user->update_attributes();
                        
                        // for now it's 5 hunger points per bite.
                        $time = time();
                        for($i=0;$i<($hunger/5);$i++){
                            $callback = array();
                            $callback["obj"]="food";
                            $callback["method"]="bite";
                            $callback["args"]=array("user_id"=>$user->get_id(), "item_id"=>$item->get_id());
                            $this->register_event("no_report",$user->get_id(),$user->get_location_id(),json_encode($callback),$time+(5*$i));
                        }
                        $result = array();
                        $result['action']="OUTPUT";
                        $result['data']="You start eating a burrito.";
                    }
                }
            }
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
            if (array_search("get",$actions)===FALSE){
                $return->result=false;
                $return->reason="That is ungettable.";
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
    
    private function get_it($item_id){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $sql = "DELETE FROM item_locations WHERE item_id='".$item_id."'";
        $r = $this->db->query($sql);
        $sql = "INSERT INTO item_users (item_id, user_id) VALUES ('".$item_id."','".$user_id."')";
        $r = $this->db->query($sql);
        
        return true;
        
    }
    
    private function actor_can(){
        return true;
    }
    
    private function actor_has_food(){
        $user_items = $this->actor->get_user_items("food");
        if (count($user_items)==0)
            return false;
        return $user_items;
        
    }
}
?>