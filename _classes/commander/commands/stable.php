<?php
require_once(BASEPATH."/_classes/commander/command.php");
require_once(BASEPATH."/_classes/worm_races.php");
// stable — at the racetrack, list every worm in the stable, best rating first:
// its rating, record, today's stats (with where each biorhythm is heading) and
// form. See _classes/worm_races.php.
class stable extends command{
    public function _execute($args){
        $user = _user();
        $user->set($user->get_logged_in_user_id());
        $result = array();
        $result['action'] = "OUTPUT";
        $races = new worm_races();
        if (!$races->is_racetrack($user->get_location_id())){
            $result['data'] = "There's no stable here.";
            return $result;
        }
        $worms = worm::stable();
        if (count($worms)==0){
            $result['data'] = "The stable's empty.";
            return $result;
        }
        $now = time();
        $output = "<div>The stable, best first:</div>";
        foreach ($worms as $worm){
            $stats = array();
            foreach (worm::$STATS as $stat=>$abbr){
                $stats[] = $abbr." ".$worm->stat($stat, $now).($worm->rising($stat, $now) ? "&uarr;" : "&darr;");
            }
            $form = $worm->form($now);
            $output .= "<div>".htmlspecialchars($worm->name)." &nbsp; RTG ".$worm->rating." &nbsp; won ".$worm->wins." of ".$worm->races." &nbsp; ".implode(" &nbsp; ", $stats)." &nbsp; (".$form['label'].")</div>";
        }
        $output .= "<div>Arrows show where each stat's biorhythm is heading.</div>";
        $result['data'] = $output;
        return $result;
    }
}
?>
