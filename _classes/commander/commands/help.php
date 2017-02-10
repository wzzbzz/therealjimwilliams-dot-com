<?php

require_once(BASEPATH."/_classes/commander/command.php");

class help extends command{
    
    public function _execute($args){
        
        if (count($args) == 0){
            $commands = $this->get_commands();
            
            $output = "<br>AVAILABLE HELP TOPICS : <br>";
    
            foreach($commands as $command){
                $output .= $command;
                $output .= "&nbsp;";
            }
        }
        
        else{
            $command = $args[0];
            $sql = "SELECT helptext FROM help INNER JOIN commands on commands.id = help.command_id WHERE commands.command = '".strtolower($command)."'";
            $result = $this->db->query($sql);
            if (count($result)==0){
                $output = "NO HELP AVAILABLE FOR '".strtoupper($command)."'.  TYPE 'HELP' FOR A LIST OF COMMANDS.";
            }
            else{
                $output = $result[0]->helptext;
            }
        }
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = $output;
        return $result;
    }
    
    
    private function get_commands(){
        $commands = array();
        $commands = $this->get_general_commands();
        $user = _user();
            if ($user->session_logged_in()){
            $commands = array_merge($commands, $this->get_logged_in_commands());
            
            $userid = $user->get_logged_in_user_id();
            $user_location = $user->get_location($userid);
            $location_id = $user_location->id;
            $commands = array_merge($commands, $this->get_location_commands($location_id));
        }
        return $commands;
    }
    private function get_general_commands(){
        $sql = "SELECT command FROM commands WHERE listed=1";
        $results = $this->db->query($sql);
        $commands = array();
        foreach ($results as $result){
            $commands[]=$result->command;
        }
        return $commands;
    }
    
    private function get_logged_in_commands(){
        
        $commands = array();
        
        $sql = "SELECT command FROM commands WHERE listed=2";
        $results = $this->db->query($sql);
        $commands = array();
        foreach ($results as $result){
            $commands[]=$result->command;
        }
        
        return $commands;
    }
    
    private function get_location_commands($location_id){
        $sql = "SELECT command FROM commands JOIN location_commands ON commands.id = location_commands.command_id WHERE listed=3 AND location_commands.location_id = '".$location_id."';";

        $results = $this->db->query($sql);
        $commands = array();
        foreach ($results as $result){
            $commands[]=$result->command;
        }
        return $commands;
    }
    
}
?>