<?php

require_once("buttons.php");
require_once("elevators.php");
class elevator_summon_buttons extends buttons{
 
    private $elevator;
    private $state;
    
    public function __contruct(){
        parent::construct();
    }

}

class elevator_summon_button extends button{
    private $elevator_id;
    
    public function press(){
        $result = parent::press();

        if ($result['result']==true){
            $location = _location();
            $elevator = new elevator();
            $elevator->set($this->elevator_id);
            $floor = $this->get_attribute("floor");
            $result = $elevator->summon($floor);
            if ($result['result']==false){
                $door = $elevator->get_door();
                $attributes = json_decode($door->get_attributes());

                if ($attributes->state=='closed'){
                    _command();
                    $command = new command(null,null);
                    $callback = array();
                    $callback["obj"]="door";
                    $callback["method"]="open_elevator_door";
                    $callback["args"]=array(
                                            "id"=>$door->id
                                        );
                    $command->register_event("open_elevator_door",0,$this->get_item_location(),json_encode($callback));
                }
                
            }
            return $result;
        }
        
        else{
            $result['data']="Please be patient.  The elevator is on its way.";
            return $result;
        }
    }
    
    public function set($id){
        parent::set($id);
        $attributes = json_decode($this->attributes);
        $this->elevator_id = $attributes->elevator_id;
    }
    
}

?>