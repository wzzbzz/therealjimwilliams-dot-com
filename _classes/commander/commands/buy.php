<?php
require_once(BASEPATH."/_classes/commander/action.php");
// buy <food> — buy something off the menu of a food truck parked here.
class buy extends action{
    public function _execute($args){
        $user = _user();
        $user->set($user->get_logged_in_user_id());
        _items();
        $items = new items();
        $items->get_subclass("food_trucks");
        $trucks = new food_trucks();
        $truck = $trucks->get_truck_in_location($user->get_location_id());
        if (!$truck){
            return $this->out("There's nothing to buy here.");
        }
        if (count($args)==0 || trim($args[0])===""){
            return $this->out($truck->menu_output($user));
        }
        $food = strtolower(trim(implode(" ", $args)));
        $sale = $truck->sell($user, $food);
        if ($sale['result']){
            $this->register_event("buys",$user->get_id(),$user->get_location_id());
        }
        return $this->out($sale['data']);
    }

    private function out($msg){
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = $msg;
        return $result;
    }
}
?>
