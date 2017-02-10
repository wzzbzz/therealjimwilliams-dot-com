<?php

class doors extends items{
    
    public function __construct(){
        parent::__construct();
    }
    
    public function get_location_door_by_type($type){
        
    }

}

class door extends item{
    
    private $type;
    protected $state;
        
    public function open(){
        $result = array();

        if ($this->state=="closed"){
            $result['result']=true;
            $attributes = json_decode($this->attributes);
            $attributes->state="open";
            $this->state="open";
            $this->update_attributes(json_encode($attributes));
            $result['data']="You open the door.";
        }
        elseif($this->state=="open"){

            $result['result']=false;
            $result['data']="That door is already open.";
        }
        return $result;
    }
    
    public function set($id){
        parent::set($id);
        $attributes = json_decode($this->attributes);
        $this->state = $attributes->state;
        return;
    }
    
    public function close(){
        $result = array();
        
        if ($this->state=="open"){
            $result['result']=true;
            $attributes = json_decode($this->attributes);
            $attributes->state="closed";
            $this->state="closed";
            $this->update_attributes(json_encode($attributes));
            $result['data']="You close the door.";
            
        }
        elseif($this->state="closed"){
            $result['result']=false;
            $result['data']="That door is already closed.";
        }
        
        return $result;
    }
    
    public function open_elevator_door($args){

        
        $this->set($args->id);
        $this->open();
        _command();
        $command = new command(null,null);
        $locations = $this->get_item_location();

        foreach($locations as $location){
            $command->register_event("elevator_door_opens",0,$location->location_id);
        }
        $callback = array("obj"=>"door","method"=>"close_elevator_door","args"=>array("id"=>$this->id));
        $time = time() + 8;
        $command->register_event("no_report",0,0,json_encode($callback),$time);
    }
    
    public function close_elevator_door($args){
        
        $this->set($args->id);
        $this->close();
        _command();
        $command = new command(null,null);
        $locations = $this->get_item_location();
        foreach($locations as $location){
            $command->register_event("elevator_door_closes",0,$location->location_id);
        }
    }
    
    public function get_item_location(){
        $sql = "SELECT * from item_locations WHERE item_id='".$this->id."'";
        $locations = $this->db->query($sql);
        return $locations;
    }
}
?>