<?php
require_once(BASEPATH."/_classes/commander/action.php");
// disconnect <dir> — remove the connection leaving the current room in a
// direction (and its reverse, if any). Gated on manage_locations, unlisted like
// the other admin build commands. Lets a builder remove or retarget a bad
// connection (e.g. one created toward a non-existent location) without raw SQL.
class disconnect extends action{

    public function _execute($args){
        $user = _user();
        if (!$user->user_has_permission('manage_locations')){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="who do you think you are, the real jim williams?";
            return $result;
        }
        if (count($args)==0 || !isset($args[0])){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="Disconnect which direction? Usage: disconnect <dir>";
            return $result;
        }
        $user->set($user->get_logged_in_user_id());
        $loc = _location();
        $loc->set($user->get_location_id());
        $connections = $loc->get_connections_by_direction($args[0]);
        if (!$connections){
            $result=array();
            $result['action']="OUTPUT";
            $result['data']="No connection that direction.";
            return $result;
        }
        $connection = $connections[0];
        $reverse = $connection->get_reverse_connection();   // may be false

        // delete this connection (and its reverse, if any). ids come from the
        // location_connections rows, so interpolation is safe (intval guard).
        $this->db->query("DELETE FROM location_connections WHERE id='".intval($connection->get_id())."'");
        if ($reverse){
            $this->db->query("DELETE FROM location_connections WHERE id='".intval($reverse->get_id())."'");
        }

        // a door that guarded this connection goes with it, unless another
        // connection still uses it
        $doors = array();
        foreach(array($connection, $reverse) as $conn){
            if ($conn && ($r = $conn->get_restrictions()) && isset($r->door)){
                $doors[intval($r->door)] = true;
            }
        }
        _items();
        foreach(array_keys($doors) as $door_id){
            $still_used = $this->db->query("SELECT id FROM location_connections WHERE restrictions='{\"door\":".$door_id."}'");
            if (count($still_used)==0){
                $door = new item();
                $door->delete_item($door_id);
            }
        }

        $result=array();
        $result['action']="OUTPUT";
        $result['data']="Connection removed.";
        return $result;
    }
}
?>
