<?php
require_once(BASEPATH."/_lib/includer.php");

class command{
    
    protected $type;
    protected $data;
    protected $db;
    protected $session_id;
    protected $command_name;
    
    public function __construct($command_name, $session_id){
        $this->db = _db();
        $this->command_name = $command_name;
        $this->session_id = $session_id;
    }
    
    public function _execute($args){

    }
    
    public function get_js(){
        $filename = BASEPATH."/_classes/commander/commands/js/".$this->command_name.".js";
        $handle = fopen($filename,"r");
        $js = fread($handle, filesize($filename));
        return $js;
    }
    
    public function register_event($name, $user_id, $user_loc, $callback=null,$time=null){
        $filename = BASEPATH."/_classes/commander/commands/js/$name.js";
        $handle = fopen($filename,"r");
        $js = fread($handle, filesize($filename));
        _event();
        $user = _user();
        $the_event = array();
        $the_event['type']="EXECUTE";
        $the_event['user_id']=$user_id;
        $the_event['user_name']=$user->get_username($user_id);
        $the_event['js'] = $js;
        $the_event['js'] = str_replace(chr(10),' ',$the_event['js']);
        $the_event=json_encode($the_event);
        
        if ($time==null){
            $time=time();
        }

        $event = new event(
            array(
                'name'=>$_SESSION['userid']."_$name",
                'event_time'=>date( 'Y-m-d H:i:s', $time),
                'originator'=>$user_id,
                'location'=>$user_loc,
                'event_script'=>$the_event,
                'callback'=>$callback
            )
        );
        
        $event->save();
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "";

        return $result;
    }
    
    public function error_message(){
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "You can't do that...here.";
        
        return $result;
    }
    
    public function disambiguate_message(){
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "Which one do you mean?";
    }
    
    public function disambiguate(){
        
    }
}
?>