<?php
require_once(BASEPATH."/_classes/commander/action.php");

class shit extends action{
    
    public function _execute($args){
        $can = $this->can($args);

        if ($can["result"]==true){
            $result = $this->take_a_shit();
        
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user_loc = $user->get_location($user_id);
            $user_loc = $user_loc->id;
            $this->register_event('shit',$user_id,$user_loc);

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
        $attributes = $user->get_attributes();
        $position = $attributes->position;

        if ($position=="standing"){
            $result=array();
            $result['result']=false;
            $result['reason']="This is a civilized place.  We shit in toilets here.";
        }
        else{
            if ($position=="seated"){
                $seat = $user->get_attribute("seat");
                _items();
                $item = new item();
                $item->set($seat);
                if ($item->get_type()!="toilet"){
                    $result=array();
                    $result['result']=false;
                    $result['reason']="This is a civilized place.  We shit in toilets here.";
                }
                else{
                    $shit = $user->get_attribute("shit");
                    if ($shit<=20){
                        $result = array();
                        $result['result']=false;
                        $result['reason']="You push and push, but nothing happens.";
                    }
                    else{
                        $result['result']=true;
                    }
                }
                
            }
            
        }
        return $result;
    }
    
    public function take_a_shit(){
        $user=_user();
        $user->set($user->get_logged_in_user_id());
        $shit = $user->get_attribute("shit");
        if ($shit<40){
            $shit_msg = "you take a teeny tiny poop.  Poop!";
        }
        elseif ($shit<60){
            $shit_msg = "You make a poo. ";
        }
        elseif($shit<80){
            $shit_msg = "Awwwww yea...you feel amazing as you take a giant shit.";
        }
        else{
            $shit_msg = "Time stands still as you release a humongous turd...it smells, it reeks, it makes angry noises...and it feels like heaven.";
        }
        
        $result['result']=true;
        $result['action']="OUTPUT";
        $result['data']=$shit_msg;
        $user->set_attribute("shit","0");
        $user->save_attributes();
        return $result;
    }
}
?>