<?php

require_once(getcwd()."/_classes/items.php");

// Food trucks park in a room and sell food off their menu for money. When a
// truck sells out it drives to the farm (any location an admin marked with
// `set location attribute farm yes`), loads ingredients, and drives back to
// where it was parked. The trip runs on the same scheduled callback events as
// the elevator (see elevators.php).
class food_trucks extends items{

    public function __construct(){
        parent::__construct();
    }

    // The food truck parked in $loc, or false.
    public function get_truck_in_location($loc){
        $rows = $this->db->query("SELECT items.id FROM items JOIN item_locations ON items.id = item_locations.item_id JOIN item_types ON items.type_id = item_types.id WHERE item_types.name='truck' AND item_locations.location_id='".intval($loc)."' LIMIT 1");
        if (count($rows)==0)
            return false;
        $truck = new food_truck();
        $truck->set($rows[0]->id);
        return $truck;
    }
}

class food_truck extends item{

    const DRIVE_TIME = 20; // seconds each way between the truck's spot and the farm
    const LOAD_TIME = 10;  // seconds spent loading ingredients at the farm

    public function __construct(){
        parent::__construct();
    }

    // Attribute with a default, without warnings for missing keys.
    private function attr($name, $default=null){
        $attributes = json_decode($this->attributes);
        if (!$attributes || !isset($attributes->$name))
            return $default;
        return $attributes->$name;
    }

    public function get_menu(){
        return (array) $this->attr("menu", array());
    }

    public function get_stock(){
        return intval($this->attr("stock", 0));
    }

    public function is_open(){
        return $this->attr("state", "open") == "open";
    }

    // The menu as terminal output.
    public function menu_output($user){
        $output = "<div>The food truck's menu:</div>";
        foreach($this->get_menu() as $food=>$price){
            $output .= "<div>".htmlspecialchars($food)." ... \$".intval($price)."</div>";
        }
        if (!$this->is_open()){
            $output .= "<div>The truck is closed while the crew loads up.</div>";
        }
        elseif ($this->get_stock()<=0){
            $output .= "<div>Sold out.</div>";
        }
        $output .= "<div>You have \$".$user->get_money().".</div>";
        return $output;
    }

    // Sell one $food to $user. Returns array('result'=>bool, 'data'=>string).
    public function sell($user, $food){
        $menu = $this->get_menu();
        if (!isset($menu[$food])){
            return array('result'=>false, 'data'=>"The food truck doesn't sell that. Type 'menu' to see what it has.");
        }
        if (!$this->is_open()){
            return array('result'=>false, 'data'=>"The food truck is closed while the crew loads up.");
        }
        if ($this->get_stock()<=0){
            $data = "Sorry, the food truck is sold out.";
            if ($this->start_restock()){
                $data .= " It's heading to the farm for more ingredients.";
            }
            return array('result'=>false, 'data'=>$data);
        }
        $price = intval($menu[$food]);
        $money = $user->get_money();
        if ($money < $price){
            return array('result'=>false, 'data'=>"A $food costs \$$price. You only have \$$money.");
        }

        // Make the food and put it straight into the buyer's hands.
        $item = new item();
        $item_id = intval($item->create_item($food));
        $this->db->query("DELETE FROM item_locations WHERE item_id='$item_id'");
        $this->db->query("INSERT INTO item_users (item_id, user_id) VALUES ('$item_id','".intval($user->get_id())."')");

        $money -= $price;
        $user->set_attribute("money", $money);
        $user->update_attributes();

        $stock = $this->get_stock() - 1;
        $this->set_attribute("stock", $stock);
        $this->save_attributes();

        $data = "You hand over \$$price and get a $food. You have \$$money left.";
        if ($stock==0 && $this->start_restock()){
            $data .= " That was the last one. The food truck heads off to the farm for more ingredients.";
        }
        return array('result'=>true, 'data'=>$data);
    }

    // Start the run to the farm. Returns false when there is no farm to go to.
    public function start_restock(){
        if (!$this->is_open())
            return true; // already on a run
        $home = $this->get_item_location();
        $farm = $this->find_farm($home);
        if (!$farm)
            return false;
        $this->set_attribute("home", $home);
        $this->set_attribute("farm", $farm);
        $this->drive("to_farm", $home, "arrive_farm");
        return true;
    }

    public function arrive_farm($args){
        $this->set($args->id);
        $this->park($this->attr("farm"), "loading");
        $this->schedule("loaded", self::LOAD_TIME);
    }

    public function loaded($args){
        $this->set($args->id);
        $this->set_attribute("stock", intval($this->attr("capacity", 5)));
        $this->drive("returning", $this->get_item_location(), "arrive_home");
    }

    public function arrive_home($args){
        $this->set($args->id);
        $this->park($this->attr("home"), "open");
    }

    // Leave $from for the road (location 0) and schedule $then on arrival.
    private function drive($state, $from, $then){
        _command();
        $command = new command(NULL,NULL);
        $command->register_event("food_truck_departs",0,$from);
        $this->set_location(0);
        $this->set_attribute("state",$state);
        $this->save_attributes();
        $this->schedule($then, self::DRIVE_TIME);
    }

    private function park($loc, $state){
        $this->set_location($loc);
        $this->set_attribute("state",$state);
        $this->save_attributes();
        _command();
        $command = new command(NULL,NULL);
        $command->register_event("food_truck_arrives",0,$loc);
    }

    private function schedule($method, $delay){
        _command();
        $command = new command(NULL,NULL);
        $callback = array("obj"=>"food_truck","method"=>$method,"args"=>array("id"=>$this->id));
        $command->register_event("no_report",0,0,json_encode($callback),time()+$delay);
    }

    // First location (other than $home) whose "farm" attribute is set.
    private function find_farm($home){
        $locations = $this->db->query("SELECT id, attributes FROM locations WHERE attributes LIKE '%farm%' ORDER BY id");
        foreach($locations as $location){
            if ($location->id == $home)
                continue;
            $attributes = json_decode($location->attributes);
            if (!$attributes || !isset($attributes->farm))
                continue;
            if (in_array(strtolower(trim($attributes->farm)), array("", "no", "0", "false")))
                continue;
            return $location->id;
        }
        return false;
    }
}
?>
