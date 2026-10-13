<?php
require_once(BASEPATH."/_classes/commander/command.php");
require_once(BASEPATH."/_classes/worm_races.php");
// hatch <name> — (admin) hatch a new racing worm into the stable. Its stats are
// rolled 3d6 and its biorhythms start today. See _classes/worm_races.php.
class hatch extends command{
    public function _execute($args){
        $user = _user();
        $result = array();
        $result['action'] = "OUTPUT";
        if (!$user->user_has_permission('manage_locations')){
            $result['data'] = "who do you think you are, the real jim williams?";
            return $result;
        }
        $name = trim(implode(" ", $args));
        if ($name=="" || strlen($name) > 30){
            $result['data'] = "Hatch what? Type 'hatch <name>' (up to 30 letters).";
            return $result;
        }
        $id = worm::hatch($name);
        if (!$id){
            $result['data'] = "There's already a worm called ".htmlspecialchars($name).".";
            return $result;
        }
        $rows = _db()->query("SELECT * FROM worms WHERE id='".intval($id)."'");
        $worm = new worm($rows[0]);
        $stats = array();
        foreach (worm::$STATS as $stat=>$abbr){
            $stats[] = "$abbr ".$worm->base[$stat];
        }
        $result['data'] = htmlspecialchars($worm->name)." wriggles out of its egg. ".implode(" &nbsp; ", $stats);
        return $result;
    }
}
?>
