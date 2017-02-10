<?php
require_once(BASEPATH."/_classes/commander/action.php");

class make extends action{

    private $actor;
    
    public function _execute($args){
        $user = _user();
       if ($user_id = $user->get_logged_in_user_id() != 7)
        {
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="who do you think you are, the real jim williams?";
            return $result;
        }
        
        _items();
        $user->set($user->get_logged_in_user_id());
        
        $can = $this->actor_can($args);
        if ($can==true){
            if (count($args)==0)
            {
                $output = "You made it.";
                
                $result=array();
                $result['action']="OUTPUT";
                $result['data']=$output;
                return $result;
            }
            
            else{
                $item = new item();
    
                if (!$item_type=$item->is_item($args)){
                    //is the command a location?
                    if ($args[0]=="location"){
                        $location = _location();
                        $new_id = $location->create();
                        if (!$new_id){
                            $result = array();
                            $result['action']="OUTPUT";
                            $result['data']="Failed";
                            return $result;
                        }
                        
                        else{
                            $result = array();
                            $result['action']="OUTPUT";
                            $result['data']="<div>Successfully created location #$new_id.</div><div>Type 'connect $new_id' &lt;dir&gt; to connect to it.</div> ";
                            return $result;
                        }
                        
                    }
                    $result=array();
                    $result['action']="OUTPUT";
                    $result['data']="don't know what one of those is.";
                }
                else{
                    
                    //is it a door
                    if ($args[0]=="door"){
                        //require a connection in a direction
                        if (!$args[1]){
                            $result=array();
                            $result['action']="OUTPUT";
                            $result['data']="Direction required.  Format : make door &lt;N,S,E,W&gt; &lt;optional:locked&gt;";
                            return $result;
                            
                        }
                        
                        $location=_location();
                        $location->set($user->get_location_id());
                        $connections = $location->get_connections_by_direction($args[1]);
                        if (!$connections){
                            $result=array();
                            $result['action']="OUTPUT";
                            $result['data']="No connection that direction.";
                            return $result;
                        }
                        else{
                            $connection = $connections[0];
                            $id = $item->create_item($args[0]);
                            $item->set($id);
                            $item->set_location($user->get_location_id());
                            $item->add_location($connection->get_dest());
                            if ($args[2]=="locked"){
                                $item->set_attribute("locked",true);
                                $key = new item();
                                $key_id = $key->create_item("key");
                                $key->set($key_id);
                                $key->set_location($user->get_location_id());
                                $item->set_attribute("key",$key_id);
                                $item->save_attributes();
                            }
                            $restriction = array();
                            $restriction['door']=$id;
                            $connection->set_restriction("door",$id);
                            $connection->save();
                            $rev_connection = $connection->get_reverse_connection();
                            $rev_connection->set_restriction("door",$id);
                            $rev_connection->save();
                        }
                    }
                    else{
                        $id = $item->create_item($args[0]);
                        $item->set($id);
                        $item->set_location($user->get_location_id());
                        $this->register_event("makes",$user->get_id(),$user->get_location_id());
                    }
                    
                    $result = array();
                    $result['action']="OUTPUT";
                    $result['data']="You made a ".$args[0];
                }
            }
        }
        return $result;
    }
    
    
       
    private function actor_can(){
        return true;
    }
  
}
?>