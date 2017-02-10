<?php

require_once(getcwd()."/_classes/items.php");

class elevators extends items{
    
    public function __construct(){
        parent::construct();
    }
}

class elevator extends item{
    
    public function __construct(){
        parent::__construct();
    }
    
    public function summon($floor){
        $result = array();
        $elevator_loc = $this->get_elevator_location();
        $dest_loc = $this->get_elevator_floor_location($floor);

        if ($dest_loc == $elevator_loc){
            $result['result']=false;
            $result['data']="The elevator is already here.";
        }
        else{
            $this->queue_destination($dest_loc);
            $result['result']=true;
            $result['data']="The elevator is on its way.";
            $this->add_process_event();
        }
        return $result;
    }
    
    public function move_to($floor){
        
        $elevator_loc = $this->get_elevator_location();
        $dest_loc = $this->get_elevator_floor_location($floor);
        
        if ($dest_loc == $elevator_loc){
            $result['result']=false;
            $result['data']="The elevator is already here.";
        }
        else{
            $this->queue_destination($dest_loc);
            $this->add_process_event();
            $result['result']=true;
            $result['data']="Elevator en route.";
        }
        
        return $result;
    }
    
    public function process_queue($args){
         $this->set($args->id);
         $state = $this->get_attribute("state");
         if ($state=="ready"){
            $queue = $this->get_attribute("queue");
            if (count($queue)>0){
                $dest = array_shift($queue);
                $this->move_dest($dest);
                $this->set_attribute("queue",$queue);
                $this->save_attributes();
            }
         }
    }
    
    private function queue_destination($loc){
        $attributes = json_decode($this->attributes);
        $queue = $attributes->queue;
        if ($queue==null)
            $queue = array();
        $queue[]=$loc;
        $attributes->queue = $queue;
        $this->attributes = json_encode($attributes);
        $this->save_attributes();
        return;
    }
    
    public function get_door(){
        $loc = $this->get_elevator_location();
        $sql = "SELECT items.id from items JOIN item_locations ON items.id = item_locations.item_id JOIN item_types ON items.type_id = item_types.id WHERE item_types.name='door' and item_locations.location_id='$loc'";
        $result = $this->db->query($sql);
        $items = new items();
        $items->get_subclass("doors");
        $door = new door();
        $door->set($result[0]->id);
        return $door;
    }
    
    public function move_dest($dest){
        
        //move items to "moving elevator (id 16)
        _command();
        $command = new command(NULL,NULL);
        $loc = $this->get_item_location();
        $command->register_event("elevator_moving",0,$loc);
        
        
        _items();
        $items = new items();

        $items_here = $items->get_items_in_location($loc);
        
        $item=new item();
        foreach($items_here as $item_here){
            $item = new item();
            $item->set($item_here->item_id);
            if ($item->get_type($item->id)=="door" || $item->get_type($item->id)=="elevator_summon_button"){
                continue;
            }

        $item->set_location(16);
        }
        //move users to "moving elevator;
        
        $sql = "SELECT * FROM user_locations WHERE location_id = '".$loc."'";
        $users = $this->db->query($sql);
        
        foreach($users as $user){
            $sql = "UPDATE user_locations SET location_id='16' WHERE user_id='".$user->user_id."'";
            $this->db->query($sql);
        }
        
        $this->set_location(16); // moving elevator.
        $this->set_attribute("state","moving");
        $this->save_attributes();
        $time = time() + 10;
        $callback = array("obj"=>"elevator","method"=>"arrive_destination","args"=>array("id"=>$this->id, "dest"=>$dest));
        _command();
        $command = new command(NULL,NULL);
        $command->register_event("no_report",0,0,json_encode($callback),$time);
    }
    
    public function arrive_destination($args){

        $this->set($args->id);
        $loc = $this->get_item_location();
        _command();
        $command = new command(NULL,NULL);
        $command->register_event("elevator_arrived",0,$loc);
        
        
        _items();
        $items = new items();
        
        $items_here = $items->get_items_in_location($loc);
        $item=new item();

        foreach($items_here as $item_here){
            $item = new item();
            $item->set($item_here->item_id);
            if ($item->get_type($item->id)=="door" || $item->get_type($item->id)=="elevator_summon_button"){
                continue;
            }
            $item->set_location($args->dest);
        }
        //move users to "moving elevator;

        $sql = "SELECT * FROM user_locations WHERE location_id = '".$loc."'";
        $users = $this->db->query($sql);        
        foreach($users as $user){
            $sql = "UPDATE user_locations SET location_id='".$args->dest."' WHERE user_id='".$user->user_id."'";
            $this->db->query($sql);
        }
        
        $this->set_location($args->dest); // moving elevator.
        $this->set_attribute("state","waiting");
        $this->save_attributes();
        
        $door = $this->get_door();
        
        $callback = array();
        $callback["obj"]="door";
        $callback["method"]="open_elevator_door";
        $callback["args"]=array(
                                "id"=>$door->id
                            );
        $command->register_event("open_elevator_door",0,$this->get_item_location(),json_encode($callback));
        
        $time = time() + 5;
        
        $callback = array("obj"=>"elevator","method"=>"ready","args"=>array("id"=>$this->id));
        $command->register_event("no_report",0,0,json_encode($callback),$time);
        
        
    }
    
    public function get_elevator_location(){
        $sql = "SELECT location_id from item_locations WHERE item_id='".$this->id."'";
        $loc = $this->db->query($sql);
        return $loc[0]->location_id;
    }
    
    public function ready($args){
        
        $this->set($args->id);
        _command();
        $command = new command(null,null);
        $callback = array();
        $callback["obj"]="door";
        $callback["method"]="open_elevator_door";
        $callback["args"]=array(
                                "id"=>$door->id
                            );
        $command->register_event("close_elevator_door",0,$this->get_item_location(),json_encode($callback));
        
        $attributes = json_decode($this->attributes);
        $attributes->state="ready";
        $this->attributes = json_encode($attributes);
        $this->save_attributes();
        $this->add_process_event();
    }
    
    public function get_elevator_floor_location($floor){
        $sql = "SELECT location_id FROM elevator_locations WHERE floor='$floor' AND elevator_id = '".$this->id."'";
        $location = $this->db->query($sql);
        return $location[0]->location_id;
    }
    
    public function add_process_event(){
        _command();
        $command = new command(null,null);
        
        $callback = array("obj"=>"elevator","method"=>"process_queue","args"=>array("id"=>$this->id));
        $command->register_event("no_report",0,0,json_encode($callback));
    }
}
?>