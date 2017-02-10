<?php

class events{
    
    private $db;
    public function __construct(){
        $this->db = _db();    
    }
    
    public function get_location_events_for_user($user_id, $location_id = 0){
        $sql = "SELECT * FROM events WHERE processed NOT LIKE '%".$user_id."' AND location='".$location_id."'";
        $events = $this->db->query($sql);
        return $events;
    }
    
    public function get_scheduled_user_events($user_id){
        $sql = "SELECT *, NOW() from event_user_sched join events ON event_user_sched.event_id = events.id WHERE event_user_sched.user_id='".$user_id."';";
        $events = $this->db->query($sql);

        return $events;
    }
    
    public function process_events(){
        $sql = "SELECT * from events WHERE event_time<NOW() AND processed='0' AND callback IS NOT null";

        $events = $this->db->query($sql);
        foreach($events as $event){
            if ($event->callback != ""){

                $callback = json_decode($event->callback);
                _items();
                $items = new items();
                $class = $items->get_subclass($callback->obj."s");
                $obj = $callback->obj;
                $obj = new $obj();
                $method = $callback->method;
                $args = $callback->args;
                $result = $obj->$method($args);
                
                $sql = "UPDATE events SET processed='1' WHERE id='".$event->id."'";
                $this->db->query($sql);
            }
        }
    }
    
}

class event{
    
    private $db;
    private $id;
    private $originator;
    private $event_script;
    private $event_time;
    private $location;
    private $name;
    private $callback;
    
    public function __construct($args){
        
        $this->db = _db();

        extract($args);
        
        if (isset($id)){
            $this->load($id);
        }
        
        else{
            $this->id = "new";
            $this->name = $name;
            $this->originator = $originator;
            $this->event_script = $event_script;
            $this->event_time = $event_time;
            $this->location = $location;
            $this->callback = $callback;
        }
        
    }
    
    public function save(){
        if ($this->id == 'new'){
            $this->db->insert(
                              array('table'=>'events',
                                    'keyvals'=>array(
                                                     'name'=>$this->name,
                                                     'originator'=>$this->originator,
                                                     'event_script'=>$this->event_script,
                                                     'event_time'=>$this->event_time, 
                                                     'location'=>$this->location,
                                                     'callback'=>$this->callback
                                                     
                                                     )
                                    )
                              );
            $this->id = mysql_insert_id();
        }
        else{
            $sql = "UPDATE events SET (name='".$this->name."', originator='".$this->originator."', event_script='".$this->event_script."', event_time='".$this->event_time."', location='".$this->location."' WHERE id='".$this->id."'";
            $this->db->query($sql);
        }

        $this->queue_users();
        
        return $this->id;
    }
    
    public function schedule_user_event($user_id, $event_id){
        $sql = "INSERT INTO event_user_sched (event_id, user_id) VALUES ('".$event_id."', '".$user_id."')";
        $result = $this->db->query($sql);
        return $result;
    }
    
    public function delete_user_event($user_id, $event_id){
        $sql = "DELETE from event_user_sched WHERE event_id='".$event_id."' AND user_id='".$user_id."'";
        $result = $this->db->query($sql);
        return $result;
    }
    
    
    public function queue_users(){
        $users = $this->get_event_users();


        $u = _user();
        foreach($users as $user){
                if ($u->is_logged_in_user($user->user_id)){
                    $this->schedule_user_event($user->user_id, $this->id);
                }
        }
    }
    
    public function get_event_users(){
        $location = _location();

        $location->set_location($this->location);
        $users = $location->get_users();
        return $users;
        
    }
    
    public function load($id){
        
        $sql = "SELECT * FROM events WHERE id='$id'";
        $result = $this->db->query($sql);
        $result = $result[0];
        
        $this->id = $id;
        $this->name=$result->name;
        $this->originator = $result->originator;
        $this->event_time = $result->event_time;
        $this->event_script = $result->event_script;
        $this->location = $result->location;
        
    }
    
    public function get_event_script(){
        return $this->event_script;
    }
    
    public function process_event($callback){
        $processes = json_decode($callback);
        foreach($processes as $process){
            $obj = new $process->obj();
            $result = $obj->$process->method($process->args);
        }
    }
}
?>
