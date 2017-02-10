<?php

class say extends command{
    
    public function _execute($args){
        
        $statement = implode(" ", $args);
        _event();
        
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $the_event = array();
        $the_event['type']="EXECUTE";
        $the_event['speaker_id']=$user_id;
        $the_event['speaker_name']=$user->get_username($user_id);
        $the_event['js'] = $this->get_js();
        $the_event['js'] = str_replace(chr(10),' ',$the_event['js']);

        $the_event['message'] = $statement;
        
        $the_event = json_encode($the_event);

        $location = $user->get_location($user_id);
        
        $location_id = $location->id;

        $event = new event(
            array(
                'name'=>$_SESSION['userid']."_say",
                'event_time'=>date( 'Y-m-d H:i:s', time()),
                'originator'=>$user_id,
                'location'=>$location_id,
                'event_script'=>$the_event
            )
        );
        
        $event->save();
        
        $result['type']="OUTPUT";
        $result['data']="";
        
    }
}

?>