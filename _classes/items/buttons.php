<?php

class buttons extends items{
    
    public function __construct(){
        parent::__construct();
    }

}

class button extends item{
    
    private $type;
    protected $state;
        
    public function press(){
        $result = array();

        if ($this->state=="ready"){
            $result['result']=true;
            $attributes = json_decode($this->attributes);
            $attributes->state="ready";
            $this->state="ready";
            $this->update_attributes(json_encode($attributes));
            $result['data']="You press the button.";
        }
        elseif($this->state=="pressed"){

            $result['result']=false;
            $result['data']="That button has already been pressed.";
        }
        return $result;
    }
    
    public function set($id){
        parent::set($id);
        $attributes = json_decode($this->attributes);
        $this->state = $attributes->state;
        return;
    }
}
?>