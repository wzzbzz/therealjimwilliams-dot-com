<?php
/*
 * NOTES :
 *
 *   Items should be able to be multiple-class.
 *   For example a box can be a seat, and a container.
 *   a guitar can be an instrument, and a weapon.
 *   
 */
require_once(BASEPATH."/_lib/includer.php");
// item class for making things you can get, drop, and use.

class items{
    
    protected $db;
    
    public function __construct(){
        $this->db = _db();
    }
    
    public function get_items_in_location($loc,$type=null){
        
        if ($type){
            $join = "  JOIN items ON items.id = item_locations.item_id JOIN item_types on items.type_id = item_types.ID ";
            $where = " AND item_types.name ='$type'";
        }
        else{
            $join="";
            $type="";
        }
        $sql = "SELECT * from item_locations $join WHERE location_id='$loc' $where";

        $items = $this->db->query($sql);
        if (count($items)==0)
            return false;
        else
            return $items;
    }
    
    public function get_item_description($item_id){
        $sql = "SELECT description from items WHERE id='".$item_id."'";
        $desc = $this->db->query($sql);
        return $desc[0]->description;
    }
    
    public function get_subclass($subclass){
        if ($subclass!=""){
            require_once(getcwd()."/_classes/items/$subclass.php");
        }
    }
    
   public function get_items_in_location_by_type($type_id, $loc){
        $itemObj = new item();
        $item_list = $itemObj->get_type_hierarchy($type_id);
        $prep = array();
        foreach($item_list as $item){
            $prep[]="'".$item."'";
        }
        $items_in = implode(",",$prep);
        $sql = "SELECT items.id, items.attributes from items JOIN item_locations ON items.id=item_locations.item_id WHERE location_id='$loc' AND type_id IN(".$items_in.")";

        $items = $this->db->query($sql);

        if (count($items)==0)
            return false;
        else
            return $items;
    }    
    
    public function get_type_id($type){
        $sql = "SELECT * FROM item_types WHERE name='$type'";
        $type = $this->db->query($sql);
        if (count($type)==0)
            return false;
        return $type[0]->id;
    }
     
}

class item{
    
    protected $db;
    protected $id;
    protected $attributes;
    protected $type_id;
    protected $name;
    protected $title;
    
    public function __construct(){
        $this->db = _db();
    }
    
    public function get_item($id){
        $sql = "SELECT * from items WHERE id='$id'";
        $item = $this->db->query($sql);
        if (count($item)==0)
            return false;
        $this->id = $id;
        return $item[0];
    }
    
    public function set($id){
        $item = $this->get_item($id);
        $this->id = $id;
        $this->attributes = $item->attributes;
        $this->type_id = $item->type_id;
        $this->name = $item->name;
        $this->title = $item->title;
        return;
    }
    
    public function get_type($id=null){
        if ($id==null){
            $id = $this->id;
        }
        $sql = "SELECT item_types.name FROM item_types JOIN items ON items.type_id = item_types.id WHERE items.id='$id'";
        
        $type_id = $this->db->query($sql);
        if (count($type_id)==0){
            return false;
        }
        return $type_id[0]->name;
    }
    
    public function get_type_id(){

        return $this->type_id;
    }
    
    public function get_type_hierarchy($type_id){
        $types = array();
        $sql = "SELECT * FROM item_types WHERE id='".$type_id."'";
        $item_type = $this->db->query($sql);
        $types[]=$type_id;
        $children = $this->get_child_types($type_id);
        $types = array_merge($types,$this->get_child_types($type_id));
        return $types;
    }
    
    public function get_child_types($type_id){
        $return = array();
        $sql = "SELECT * FROM item_types WHERE parent_type='".$type_id."'";
        $children = $this->db->query($sql);
        if (count($children)==0){
            return $return;
        }
        else{
            foreach($children as $child){
                $return[]=$child->id;
                if ($childs = $this->get_child_types($child->id)){
                    $return = array_merge($return, $childs);
                }
            }
            return $return;            
        }
    }
    
    public function is_type($type_id){
        $types = $this->get_type_hierarchy($type_id);
        return (array_search($this->get_type_id(),$types)!==FALSE);
        
    }
    
    public function get_item_location(){
        $sql = "SELECT location_id from item_locations WHERE item_id='".$this->id."'";
        $location = $this->db->query($sql);
        return $location[0]->location_id;
    }
    
    public function update_attributes($attributes=null){
        if ($attributes == null){
            $attributes = $this->attributes;
        }
        $sql = "UPDATE items SET attributes='$attributes' WHERE id='".$this->id."'";
        $result = $this->db->query($sql);
        return;
    }
    
    public function get_attributes($id=null){
        if ($id==null)
            $id=$this->id;
        $sql = "SELECT attributes FROM items WHERE id='$id'";
        $result = $this->db->query($sql);
        return $result[0]->attributes;
    }
    
    public function get_attribute($attr){
        $attributes = json_decode($this->attributes);
        return $attributes->$attr;   
    }
    
    public function set_attribute($attr,$val){
        $attributes = json_decode($this->attributes);
        $attributes->$attr=$val;
        $this->attributes = json_encode($attributes);
    }
    
    public function save_attributes(){
        $this->update_attributes($this->attributes);
        return;
    }
    
    public function set_location($loc){
        $sql = "UPDATE item_locations SET location_id='$loc' WHERE item_id='".$this->id."'";
        $this->db->query($sql);
    }
    
    public function add_location($loc){
        $sql = "INSERT INTO item_locations (location_id,item_id) VALUES ('$loc','".$this->id."')";
        $this->db->query($sql);
    }
    
    public function get_actions(){
        $types = $this->get_type_hierarchy($this->type_id);
        $types_in = implode(",",$types);
        $sql = "SELECT actions FROM item_types WHERE id IN($types_in)";
        $type_actions = $this->db->query($sql);
        $actions = array();
        foreach($type_actions as $type_action){
            $actions = array_merge($actions,json_decode($type_action));
        }
        $actions = array_unique($actions);
        return $actions;
    }
    
    public function get_name(){
        return $this->name;
    }
    
    public function get_id(){
        return $this->id;
    }
    
    public function get_title(){
        return $this->title;
    }
    
    public function get_type_by_type_name($type){
        $sql = "SELECT * from item_types WHERE name='$type'";
        $type = $this->db->query($sql);
        if (count($type)==0)
            return false;
        return $type[0]->id;
    }
    
    //takes a phrase and checks to see if it describes an item, and returns the item name / id.
    public function is_item($words){
        $words_in=array();
        foreach($words as $word){
            $words_in[]="'".$word."'";    
        }
        $words = implode(",",$words_in);
        $sql = "SELECT * from item_types WHERE name IN ($words)";
        $item_type = $this->db->query($sql);
     
        if (count($item_type)==0)
            return false;
        return $item_type[0];
    }
    
    public function get_type_field($field){
        $sql = "SELECT $field FROM item_types WHERE id='".$this->type_id."'";
        $type_field = $this->db->query($sql);
        if (count($field)==0){
            return false;
        }
        return $type_field[0]->$field;
    }
    
    protected function update_field($field,$val){
        $sql = "UPDATE items SET $field='$val' WHERE id='".$this->id."'";
        $this->db->query($sql);
        return;
    }
    
    public function create_item($type){
        $sql = "SELECT name,id,title,description,base_attributes as attributes from item_types WHERE name='$type'";
        $item_type = $this->db->query($sql);
        $item_type_values = array();
        $item_type=$item_type[0];
        foreach($item_type as $item_field){
            $item_type_values[]="'".$item_field."'";
        }
        $item_type_values = implode(",",$item_type_values);
        $sql = "INSERT INTO items (name,type_id,title,description,attributes) VALUES ($item_type_values)";
        $item = $this->db->query($sql);
        $id = mysql_insert_id();
        $sql = "INSERT into item_locations (item_id, location_id) VALUES ($id,0)";
        $this->db->query($sql);
        return $id;
    }
    
    public function delete_item($id){
        $sql = "DELETE from item_locations WHERE item_id='$id'";
        $this->db->query($sql);
        $sql = "DELETE FROM item_users WHERE item_id='$id'";
        $this->db->query($sql);        
        $sql = "DELETE FROM items WHERE id='$id'";
        $this->db->query($sql);
        return true;
    }
}
?>