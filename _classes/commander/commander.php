<?php
require_once(BASEPATH."/_lib/includer.php");
require_once(BASEPATH."/_classes/commander/command.php");

class commander{
    
    public $db;
    private $sessid;
    
    public function __construct($s){
        $this->db = _db();
        $this->sessid = $s;
    }
    
    public function parse_command($s){

        
        $s = trim($s);
        
        $s = $this->swap_alias($s);
        
        if (strpos($s, "'")>-1){
            $result = array();
            $result['action'] = "OUTPUT";
            $result['data'] = "INVALID COMMAND : $s";
            
            return $result;
        }
        $commands = explode(" ",$s);
        $command = strtolower(array_shift($commands));
        
        if ($the_command = $this->is_command($command)){
            
            $this->load_command($command);
            $c = new $command($command,$this->sessid);
                
            if ($this->command_is_permitted($the_command))
            {
                $result = $c->_execute($commands);
            }
            else{
                $result = $c->error_message();
            }
        }
        else{
            $result = array();
            $result['action'] = "OUTPUT";
            $result['data'] = "Invalid command - typing 'HELP' might...help.";
            
        }
        
        return $result;
    }
    
    private function is_command($s){
        
        $sql = "SELECT id, listed FROM commands WHERE command='".strtoupper($s)."'";
        $command = $this->db->query($sql);
        
    
        return (count($command)>0)?$command[0]:false;
    }
    
    private function command_is_permitted($command){

            $locale = $command->listed;
            
            /*if ($locale == 3){
                $user = _user();
                $user_id = $user->get_logged_in_user_id();
                $location = $user->get_location($user_id);
                $location_id = $location->id;
                $sql = "SELECT location_id FROM location_commands WHERE command_id = '".$command->id."'";
                
                $command_locations = $this->db->query($sql);
    
                $found = false;
                foreach ($command_locations as $command_location){

                    if ($location_id == $command_location->location_id){
                        return true;
                    }

                }
                
                return $found;
            }*/
            
            return true;

    }
    
    private function load_command($s){
        require_once(BASEPATH."/_classes/commander/commands/".strtolower($s).".php");
    }
    
    private function swap_alias($c){
        
        $sql = "SELECT alias FROM command_alias WHERE command='".strtoupper($c)."'";

        $result = $this->db->query($sql);

        if (count($result)>0){
            $c = $result[0]->alias;
        }

        return $c;
    }
}

?>