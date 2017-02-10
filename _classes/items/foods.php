<?php

class foods extends items{
    
    public function __construct(){
        parent::__construct();
    }
    
    public function get_location_door_by_type($type){
        
    }

}

class food extends item{
    
    private $type;
    protected $state;
        
    public function bite($args){
        $user = _user();
        $user->set($args->user_id);
        _command();
        $command = new command(null,null);
        
        if ($user->get_attribute("status")!="eating"){
            return;
        }
        
        $user_hunger = $user->get_attribute("hunger");
        if ($user_hunger==0){
            $command->register_event("user_full",$args->user_id,$user->get_location_id());
            $user->set_attribute("status","ready");
            $user->update_attributes();
            return;
        }
        
        _items();
        $item = new item();
        $item->set($args->item_id);
        $item_nourishment = $item->get_attribute("nourishment");
        $item_base_attributes = json_decode($item->get_type_field("base_attributes"));
        $item_base_nourishment = $item_base_attributes->nourishment;
        $bite_size = 5; // HARD-CODED BITE SIZE
        $this_bite = min($bite_size, $item_nourishment);
        $this_bite = min($this_bite, $user_hunger);
        
        if ($item_nourishment == $item_base_nourishment){
            $damaged_desc = $item->get_type_field("damaged_title");
            $item->update_field("title",$damaged_desc);
            $item->update_field("description",$damaged_desc);
        }
        $user_hunger = $user_hunger - $bite_size;
        $user->set_attribute("hunger",$user_hunger);
        $belly = $user->get_attribute("belly");
        if ($belly==null){
            $belly = 0;
        }
        $shitfactor = $item->get_attribute("shit-factor");
        $belly = $belly+$bite_size*$shitfactor;
        $user->set_attribute("belly",$belly);
        $user->update_attributes();
        // now make shit happen
        

        $item_nourishment = $item_nourishment - $bite_size;
        $item->set_attribute("nourishment",$item_nourishment);
        $item->update_attributes();
        if($item_nourishment == 0){
            $destroyed_desc = $item->get_type_field("destroyed_title");
            $item->update_field("title",$destroyed_desc);
            $item->update_field("description",$destroyed_desc);
            $item->update_field("type_id",$item->get_attribute("trash_id"));
            $item->set_attribute("alias","wrapper");
            $item->update_attributes();
            $user->set_attribute("status","ready");
            $user->update_attributes();
            
            $command->register_event("all_gone",$args->user_id,$user->get_location_id());
        }        
        
        $command->register_event("bite",$args->user_id,$user->get_location_id());
        
        
    }
    
}
?>