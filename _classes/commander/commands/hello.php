<?php

class hello extends command{
    public function _execute($args){
        $replies = array("Oh, hello there. type 'HELP' for a list of commands.","what's up. type 'HELP' for a list of commands.", "What ya want? type 'HELP' for a list of commands.", "Hey!  type 'HELP' for a list of commands.");
        shuffle($replies);
        $data['action']="OUTPUT";
        $data['data']=$replies[0];
        
        return $data;
    }
}
?>