<?php

class lights extends items{
    
    public function __construct(){
        parent::__construct();
    }
}

class light extends item{
    
    private $type;
    protected $state;
        
    public function turn_on(){

        $result = array();

        if ($this->state=="off"){
            $result['result']=true;
            $attributes = json_decode($this->attributes);
            $attributes->state="on";
            $this->state="on";
            $this->update_attributes(json_encode($attributes));
            $result['data']="You turn on the flashlight.";
        }
        elseif($this->state=="on"){

            $result['result']=false;
            $result['data']="That light is already on.";
        }
        return $result;
    }
    
    public function set($id){
        parent::set($id);
        $attributes = json_decode($this->attributes);
        $this->state = $attributes->state;
        return;
    }
    
    public function turn_off(){
        $result = array();
        
        if ($this->state=="on"){
            $result['result']=true;
            $attributes = json_decode($this->attributes);
            $attributes->state="off";
            $this->state="off";
            $this->update_attributes(json_encode($attributes));
            $result['data']="You turn off the light.";
            
        }
        elseif($this->state="off"){
            $result['result']=false;
            $result['data']="That light is already off.";
        }
        
        return $result;
    }
    
    public function get_item_location(){
        $sql = "SELECT * from item_locations WHERE item_id='".$this->id."'";
        $locations = $this->db->query($sql);
        return $locations;
    }
}
?>