<?php
require_once(BASEPATH."/_lib/includer.php");
// Location class for giving a logged in user a sense of location.

class Location{
    private $id;
    private $db;
    public $message;
    private $name;
    private $title;
    private $short_desc;
    private $long_desc;
    private $owner;
    private $restrictions;
    private $attributes;
    
    public function __construct($id=0){
        $this->db = _db();
        $this->set_location($id);
    }
    
    public function get_users(){
        $sql = "SELECT * from user_locations WHERE location_id = $this->id";
        $users = $this->db->query($sql);
        $return = array();
        foreach ($users as $user){
            $return[] = $user;
        }
        return $return;
    }
    
    public function newloc(){
        $this->id=0;
    }
    
    public function set_location($id){
        $this->id = $id;
    }
    
   // public function get_long_desc(){
   //     $sql = "SELECT long_desc FROM locations WHERE id = '".$this->id."'";
   //     $longdesc = $this->db->query($sql);
   //     return $longdesc[0]->long_desc;
   // }
    
   // public function get_short_desc(){
   //     $sql = "SELECT short_desc FROM locations WHERE id = '".$this->id."'";
   //     $shortdesc = $this->db->query($sql);
   //     return $shortdesc[0]->short_desc;
   // }
    
    public function get_location_by_name($name){
        $sql = "SELECT * from locations WHERE name='$name'";
        $location = $this->db->query($sql);
        return $location[0];
    }
    
    public function move($current_loc, $direction){
        //check if move in direction is possible
        $dest = $this->check_move($current_loc, $direction);
        if ($dest['result'] == true)
        {
            // update user_locations with new location.
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $newloc =  $this->update_user_location($user_id, $dest['dest']);
            return $dest;
            
        }
        else
            return $dest;
        
    }
    
    public function check_move($current_loc, $direction){
        
        $sql = "SELECT * from location_connections WHERE current_loc = '".$current_loc."' AND direction='".strtoupper($direction)."'";

        $connections = $this->db->query($sql);
        $return = array();
        if (count($connections)==0){
            $return['result']=false;
            $return['message']="You can't go there.";
            return $return;
        }
        else{
            // now check restrictions
            $restrictions = json_decode($connections[0]->restrictions);
            if (!$restrictions){
                $return['result']=true;
                $return['dest']=$connections[0]->dest;
                return $return;
            }
            else{
                $pass = true;
                foreach($restrictions as $key=>$val){
                    $func = "restriction_".$key;
                    $result = $this->$func($val);
                    if (!$result){
                        $return['result']=false;
                        $return['message']=$connections[0]->failure_message;
                        return $return;
                    }
                    
                }
                $return['result']=true;
                $return['dest']=$connections[0]->dest;

                return $return;
            }
           /* if ($this->check_location_restrictions($result[0]->dest)){
                return $result[0]->dest;
            }
            else{
                return false;
            }*/
        }
    }
    
    private function restriction_door($door_id){
                $return = array();
                _items();
                $item = new item();
                $door = $item->get_item($door_id);

                $attributes = json_decode($door->attributes);

                if ($attributes->state=="closed"){
                    return false;
                }
                else
                    return true;
        
    }
    
    public function check_location_restrictions($loc){
        $user = _user();
        $user_id = $user->get_logged_in_user_id();
            
        $sql = "SELECT restrictions FROM locations WHERE id='$loc'";
        $restrictions = $this->db->query($sql);
        $restrictions=$restrictions[0]->restrictions;
        $restriction = json_decode($restrictions);
        
        $result = true;
        if ($restriction->level=="group"){
            
            $result = $this->group_restriction($user_id, $restriction->id);
        }
        if ($restriction->level == "owner"){
            $result =  ($restriction->id==$user_id);
        }
        if ($restriction->level == "item"){
            
        }
        return $result;
    }
    
    public function group_restriction($user_id, $group_id){
        $sql = "SELECT * from group_users WHERE user_id='".$user_id."' AND group_id='".$group_id."'";
        $result = $this->db->query($sql);
        return(count($result)>0);
    }
    
    public function owner_restriction($loc, $user_id){
        
    }
    public function update_user_location($user_id, $location){
        $sql = "UPDATE user_locations SET location_id = '$location' WHERE user_id='".$user_id."'";
        $result = $this->db->query($sql);

        if ($result==false)
            return false;
        else
            return $location;
    }
    
    public function get_user_location($user_id){
        $sql = "SELECT location_id FROM user_locations WHERE user_id='".$user_id."'";
        $loc = $this->db->query($sql);
        $loc = $loc[0]->location_id;
        return $loc;
    }
    
    public function set($location_id){
        $sql = "SELECT * FROM locations WHERE id='$location_id'";

        $loc = $this->db->query($sql);
        if (count($loc)==0){
            return false;
        }
        $loc = $loc[0];
        
        $this->id = $loc->id;
        $this->name = $loc->name;
        $this->title = $loc->title;
        $this->attributes = $loc->attributes;
        $this->restrictions = $loc->restrictions;
        $this->long_desc = $loc->long_desc;
        $this->short_desc = $loc->short_desc;
        $this->owner = $loc->owner;
        return true;
    }
    
    public function get_id(){ return $this->id; }
    public function get_name(){ return $this->name; }
    public function set_name($val){ diebug($val);$this->name = $val; return true; }
    
    public function get_title(){ return $this->title; }
    public function set_title($val){ $this->title = $val; return true; }
    
    public function get_attributes(){ return json_decode($this->attributes);}
    public function set_attributes($attrs){
        if (!is_array($attrs))
            return false;
        elseif($attr_string = json_encode($attrs)){
            return false;
        }
        $this->attributes=$attr_string;
        return true;
    }
    public function get_attribute($attr){
        $attrs = json_decode($this->attributes);
        return $attrs->$attr;
    }
    public function set_attribute($attr,$val){
        $attrs = json_decode($this->attributes);
        $attrs->$attr=$val;
        $this->attributes = json_encode($attrs);
    }
    
    public function get_long_desc(){ return $this->long_desc; }
    public function set_long_desc($val){ $this->long_desc=$val; return true;}
    
    public function get_short_desc(){ return $this->short_desc; }
    public function set_short_desc($val){ $this->short_desc=$val; return true;}
    public function get_owner(){ return $this->owner; }
    public function set_owner($val){ $this->owner = $val; return true;}
    
    public function create(){
        //create a limbo
        $this->set(1);
        $sql = "INSERT INTO locations (name,title,short_desc,long_desc,owner,restrictions,attributes)
                VALUES ('".$this->name."','".$this->title."','".$this->short_desc."','".$this->long_desc."','".$this->owner."','".$this->restrictions."','".$this->attributes."');";
        $this->db->query($sql);
        $id = mysql_insert_id();
        return $id;
    }
    
    public function save(){
        if ($this->id==null){
            return false;
        }
        $sql = "UPDATE locations SET name='".$this->name."', title='".$this->title."', long_desc='".$this->long_desc."', short_desc='".$this->short_desc."', owner='".$this->owner."', restrictions='".$this->restrictions."', attributes='".$this->attributes."' WHERE id='".$this->id."'";
        $this->db->query($sql);
        return true;
    }
    
    public function get_connections(){
        $sql = "SELECT * FROM location_connections WHERE current_loc = '".$this->id."'";
        $lcs = $this->db->query($sql);
        if (count($lcs)==0){
            return false;
        }
        $connections = array();
        foreach($lcs as $lc){
            $location_connection = new location_connections();
            $location_connection->set($lc->id);
            $connections[]= $location_connection;
        }
        return  $connections;
    }
    
    public function get_connections_by_direction($direction){
        $sql = "SELECT * FROM location_connections WHERE current_loc = '".$this->id."' AND direction='$direction'";
        $lcs = $this->db->query($sql);
        if (count($lcs)==0){
            return false;
        }
        $connections = array();
        foreach($lcs as $lc){
            $location_connection = new location_connections();
            $location_connection->set($lc->id);
            $connections[]= $location_connection;
        }
        return  $connections;
    }
    
    public function get_items(){
        $sql = "SELECT * FROM item_locations WHERE location_id='".$this->id."'";
        $loc_items = $this->db->query($sql);
        if (count($items)==0){
            return array();
        }
        $items = array();
        _items();
        foreach ($loc_items as $loc_item){
            $item = new item();
            $item->set($loc_item->item_id);
            $items[]=$item;
        }
        
        return $items;
    }
    
    public function get_lighting(){
        $lit = $this->get_attribute("lighting");
        if (!($lit=="lit")){
            //check for item in room that has lighting power.
            $items = $this->get_items();
            foreach($items as $item){
                if($item->is_item("light")){
                    if ($item->get_attribute("status")=="on"){
                        $lit="lit";
                        break;
                    }
                }
            }
            if (!($lit=="lit")){
                $loc_users = $this->get_users();
                $user = _user();
                foreach($loc_users as $loc_user){
                    $user->set($loc_user->user_id);
                     //check for item in local users posession that has lighting power.
                    $user_items = $user->get_user_items("light");
                    _items();
                    $item = new item();
                    foreach($user_items as $user_item){
                        $item->set($user_item->item_id);
                        if ($item->get_attribute('state')=="on"){
                            $lit="lit";
                            break;
                        }
                    }
                    
                }
                
            }
            
        }
        return $lit;   
    }
}


class location_connections{
    
    private $id;
    private $current_loc;
    private $dest;
    private $direction;
    private $restrictions;
    private $failure_message;
    private $db;
    
    public function __construct(){
        $this->db = _db();
    }
    
    public function set($id){
        
        $sql = "SELECT * from location_connections WHERE id='$id'";
        $loc = $this->db->query($sql);
        
        if (count($loc)==0){
            return false;
        }
        $loc = $loc[0];
        
        $this->id = $loc->id;
        $this->current_loc = $loc->current_loc;
        $this->dest = $loc->dest;
        $this->direction = $loc->direction;
        $this->restrictions = $loc->restrictions;
        $this->failure_message = $loc->failure_message;
        
        return true;
    }
    
    public function get_current_loc(){ return $this->current_loc; }
    public function set_current_loc($loc){ $this->current_loc = $loc; return true; }
    public function get_dest(){ return $this->dest; }
    public function set_dest($dest){ $this->dest = $dest; return true;}
    public function get_direction(){ return $this->direction; }
    public function set_direction($dir){ $this->direction = $dir; return true; }
    public function get_restrictions(){ return json_decode($this->restrictions);}
    public function set_restrictions($restrictions){
        $this->restrictions = json_encode($restrictions);
        return true;
    }
    public function get_restriction($restriction){
        $r = json_decode($this->restrictions);
        return $r->$restriction;
    }
    public function set_restriction($restriction, $val){
        $r = json_decode($this->restrictions);
        $r->$restriction=$val;
        $this->restrictions = json_encode($r);
        return $true;
    }
    public function get_failure_message(){ return $this->failure_message;}
    public function set_failure_message($message){ $this->failure_message = $message; return true;}
    
    public function save() {
        $sql= "UPDATE location_connections SET
                    current_loc='".$this->current_loc."',
                    dest='".$this->dest."',
                    direction='".$this->direction."',
                    restrictions='".$this->restrictions."',
                    failure_message='".$this->failure_message."'
                WHERE id='".$this->id."'";
        $this->db->query($sql);
        return true;
    }
    
    public function create() {
        $sql = "INSERT INTO location_connections (current_loc,dest,direction,restrictions,failure_message) VALUES ('0','0','N','{}','')";
        $this->db->query($sql);
        $this->set(mysql_insert_id());
        return $this->id;
    }
    
    public function get_reverse_connection(){
        
        $sql = "SELECT * from location_connections WHERE current_loc='".$this->dest."' AND dest = '".$this->current_loc."'";
        $rev = $this->db->query($sql);
        if (count($rev)==0)
        {
            return false;
        }
        $conn = new location_connections();
        $conn->set($rev[0]->id);
        return $conn;
    }
    
}
?>