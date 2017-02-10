<?php
require_once("lights.php");
class flashlights extends lights{
    
    public function __construct(){
        parent::__construct();
    }
}

class flashlight extends light{
    
    private $type;
    protected $state;
    
}
?>