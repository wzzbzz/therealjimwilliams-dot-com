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
        
        $command = array_shift($statement);
        
        if (!$this->is_command($command)){
            
            //failure.  illegal command.
            
        }
        
        $command_struct = new stdClass();
        
        $command_struct->command = $command;
        
        $command_struct['modifiers']=array();
        
        foreach($words as $word){
            
            $parts_of_speech = $this->get_parts_of_speech($word);
            
            if (!$parts_of_speech){
                
                //failure.  word not in dictionary. bail on whole procedure or skip.
                
            }
            
            $command_struct['modifiers']['word']=$word;
            
            $command_struct['parts_of_speech']=$parts_of_speech;
            
        }
        
    }
}
?>