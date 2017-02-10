<?php
require_once(BASEPATH."/_classes/commander/command.php");

class play extends command{
    
    public function _execute($args){
        
        // check for item that is an instrument
        $user = _user();
        $user->set($user->get_logged_in_user_id());
        $user_items = $user->get_user_items();
        _items();
        $items = new items();
        $item = new item();
        $has_instrument = false;
        foreach ($user_items as $user_item){
            $item->set($user_item->item_id);
            if ($item->is_type($items->get_type_id("instrument")))
            {
                $has_instrument=true;
                $theInstrument = $item;
                break;
            }
        }
        if ($has_instrument==false){
            $result = array();
            $result['action']="OUTPUT";
            $result['data']="You don't have an instrument.";
            return $result;
        }

        $sql = "SELECT COUNT(*) as count FROM riffs WHERE type_id='".$theInstrument->get_type_id()."'";
        $count = $this->db->query($sql);
        
        $count = $count[0]->count;
        $offset = rand(0,($count-1));
        
        $sql = "SELECT url from riffs WHERE type_id='".$theInstrument->get_type_id()."' LIMIT 1 OFFSET $offset";
        
        $fart = $this->db->query($sql);
        $fart = $fart[0]->url;
        
        $event = _event();
        $filename=BASEPATH."/_classes/commander/commands/js/play.js";
        $handle = fopen($filename,'r');
        $data = fread($handle,filesize($filename));
        $data = str_replace("{url}","\\\"".$fart."\\\"",$data);

        
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
        $the_fart = array();
        $the_fart['type']="EXECUTE";
        $the_fart['farter_id']=$user_id;
        $the_fart['farter_name']=$user->get_username($user_id);
        $the_fart['js'] = $data;
        $the_fart['js'] = str_replace(chr(10),' ',$the_fart['js']);
        $the_fart=json_encode($the_fart);
        
        $location = $user->get_location($user_id);
        
        $location_id = $location->id;
        
        $event = new event(
            array(
                'name'=>$_SESSION['userid']."_play",
                'event_time'=>date( 'Y-m-d H:i:s', time()),
                'originator'=>$user_id,
                'location'=>$location_id,
                'event_script'=>$the_fart
            )
        );
        
        $event->save();
        
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = "";
        return $result;
    }    
}
?>