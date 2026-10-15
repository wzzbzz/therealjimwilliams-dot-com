<?php
require_once(BASEPATH."/_classes/commander/action.php");
// menu — read the menu of a food truck parked here.
class menu extends action{
    public function _execute($args){
        $user = _user();
        $user->set($user->get_logged_in_user_id());
        _items();
        $items = new items();
        $items->get_subclass("food_trucks");
        $trucks = new food_trucks();
        $truck = $trucks->get_truck_in_location($user->get_location_id());
        $result = array();
        $result['action'] = "OUTPUT";
        $result['data'] = $truck ? $truck->menu_output($user) : "There's no menu here.";
        return $result;
    }
}
?>
