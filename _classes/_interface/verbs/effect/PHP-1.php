<?php

class parser{
    private $statement;
    
    public function __construct(){}
    
    public function parse($statement){
        //
    }
    
    public function parse_command($statement){
        
        //break up into words
        
        $words = explode(trim($statement));
    }
}
?>