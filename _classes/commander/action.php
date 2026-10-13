<?php
require_once("command.php");

class action extends command{
    
    // name of the command
    protected $item_types;
    
    // id of the user that is issuing the command.
    protected $user_id;
    
    public function find_item(){
        
    }
    
    public function item_can($item_id){
        
    }
    
    public function can($args = null){
        
    }
    
    protected function requires($args){

    }

    // Item type named by the phrase: a word matching item_types.name, or failing
    // that, the type of an item in the room whose alias/title the phrase names
    // (so "lock office" finds the office door without the word "door").
    protected function find_item_type($args){
        $db = _db();
        $words = array();
        foreach($args as $arg){
            $words[]="'".$db->escape(strtolower($arg))."'";
        }
        if (count($words)==0)
            return false;
        $item_type = $db->query("SELECT * from item_types WHERE name IN (".implode(",",$words).")");
        if (count($item_type)>0)
            return $item_type[0];

        $phrase = $this->item_phrase($args);
        foreach($this->items_here() as $item){
            if ($this->item_names_match($item, $phrase, $item->name)){
                return $db->query("SELECT * from item_types WHERE id='".intval($item->type_id)."'")[0];
            }
        }
        return false;
    }

    // Items of $item_type in the user's room. item_id/attributes/item_title come
    // from the item; the rest of the row (actions, action_responses) from its type.
    protected function items_here($item_type=null){
        $db = _db();
        $user = _user();
        $location = $user->get_location($user->get_logged_in_user_id());
        $sql = "SELECT item_types.*, items.type_id, items.title AS item_title, items.attributes, item_locations.item_id FROM item_locations JOIN items ON items.ID = item_locations.item_id JOIN item_types ON items.type_id = item_types.id WHERE item_locations.location_id='".intval($location->id)."'";
        if ($item_type!==null){
            $sql .= " AND item_types.id='".intval($item_type->id)."'";
        }
        return $db->query($sql);
    }

    // Pick the one item of $item_type the player means. Returns stdClass with
    // ->item (null if unresolved) and ->reason. Matches the phrase against the
    // item's alias or title, then against the direction of the connection the
    // door guards ("open north door", "open door north").
    protected function resolve_item($item_type, $args){
        $return = new stdClass();
        $return->item = null;

        $items = $this->items_here($item_type);
        if (count($items)==0){
            $return->reason="You can't see a ".$item_type->name." here";
            return $return;
        }
        if (count($items)==1){
            $return->item = $items[0];
            return $return;
        }

        $phrase = $this->item_phrase($args);
        foreach($items as $item){
            if ($this->item_names_match($item, $phrase, $item_type->name)){
                $return->item = $item;
                return $return;
            }
        }

        $exits = $this->door_directions();
        $qualifier = trim(preg_replace('/\b'.preg_quote($item_type->name,'/').'\b/', '', $phrase));
        $dir = $this->direction_word($qualifier);
        if ($dir!==false){
            foreach($items as $item){
                if (isset($exits[$item->item_id]) && in_array($dir, $exits[$item->item_id])){
                    $return->item = $item;
                    return $return;
                }
            }
        }

        $choices = array();
        foreach($items as $item){
            $choice = $this->item_label($item);
            if (isset($exits[$item->item_id])){
                $choice .= " (".implode("/", $exits[$item->item_id]).")";
            }
            $choices[] = $choice;
        }
        $return->reason="Which ".$item_type->name." did you mean? ".implode(", ", $choices);
        return $return;
    }

    protected function item_phrase($args){
        $phrase = strtolower(trim(implode(" ", $args)));
        return trim(preg_replace('/^the /', '', $phrase));
    }

    // phrase equals the alias or title, with or without "the" and the type word
    private function item_names_match($item, $phrase, $type_name){
        $attributes = json_decode($item->attributes);
        $names = array();
        if (isset($attributes->alias)) $names[] = $attributes->alias;
        if ($item->item_title) $names[] = $item->item_title;
        foreach($names as $name){
            $name = strtolower(trim(preg_replace('/^the /i', '', trim($name))));
            $short = ($type_name!="") ? trim(preg_replace('/\b'.preg_quote($type_name,'/').'\b/', '', $name)) : $name;
            if ($phrase==$name || ($short!="" && $phrase==$short)){
                return true;
            }
        }
        return false;
    }

    private function item_label($item){
        $attributes = json_decode($item->attributes);
        if (isset($attributes->alias) && $attributes->alias!="")
            return "the ".$attributes->alias;
        if ($item->item_title)
            return $item->item_title;
        return "the ".$item->name." #".$item->item_id;
    }

    // door item id => directions (lowercase short form) it guards out of this room
    private function door_directions(){
        $db = _db();
        $user = _user();
        $location = $user->get_location($user->get_logged_in_user_id());
        $rows = $db->query("SELECT direction, restrictions FROM location_connections WHERE current_loc='".intval($location->id)."'");
        $exits = array();
        foreach($rows as $row){
            $restrictions = json_decode($row->restrictions);
            if (isset($restrictions->door)){
                $exits[$restrictions->door][] = strtolower($row->direction);
            }
        }
        return $exits;
    }

    private function direction_word($word){
        $directions = array("north"=>"n","south"=>"s","east"=>"e","west"=>"w","n"=>"n","s"=>"s","e"=>"e","w"=>"w","up"=>"up","down"=>"down","u"=>"up","d"=>"down");
        return isset($directions[$word]) ? $directions[$word] : false;
    }

}
?>