<?php

require_once(BASEPATH."/_classes/commander/command.php");
require_once(BASEPATH."/_classes/commander/commands/go.php");

class press extends command{
    
    
    public function _execute($args){
     
        if (count($args)==0){
            $result['action']='OUTPUT';
            $result['data']='You press nothing in particular.';
        }
        else{
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $location = _location();
            $user_loc = $location->get_user_location($user_id);
            $buttons = $this->is_button_location($user_loc->id);
            if (!$buttons){
                return $this->error_message();
            }
            if (count($buttons)>1){
                //disambiguate
                $found = false;
                foreach($buttons as $button){
                    $attr = json_decode($button->attributes);
                    if($attr->alias==$args[0]){
                        $found=true;
                        break;
                    }
                }
                if ($found==false){
                    return $this->disambiguate_message();
                }
            }
            else{
                $button = $buttons[0];
            }
                        
            _items();
            $item = new item();

            $button_type = $item->get_type($button->id);
            
            $items = new items();
            $items->get_subclass($button_type."s");
            
            $theButton = new $button_type();
            $theButton->set($button->id);
            $result = $theButton->press();

            $result['action']="OUTPUT";
            
        }
        
        return $result;
    }
    
    public function error_message(){
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "Where do you think you are?  An elevator?";
        
        return $result;
    }
    
    public function disambiguate_message(){
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "Which button are you pushing?";
        return $result;
    }
    
    private function is_button_location($user_id){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $location = $user->get_location($user_id);
        _items();
        $items = new items();
        $button_type = $items->get_type_id('button');
        $buttons = $items->get_items_in_location_by_type($button_type,$location->id);
        return $buttons;
        
    }
}
?>