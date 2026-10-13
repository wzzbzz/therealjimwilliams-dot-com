<?php
require_once(BASEPATH."/_classes/commander/command.php");
require_once(BASEPATH."/_classes/worm_races.php");
// watch — watch the worm race at the racetrack: the one running now, or the
// next one if the last has finished, with its racecard. See _classes/worm_races.php.
class watch extends command{
    public function _execute($args){
        $user = _user();
        $user->set($user->get_logged_in_user_id());
        $races = new worm_races();
        $result = array();
        if (!$races->is_racetrack($user->get_location_id())){
            $result['action'] = "OUTPUT";
            $result['data'] = "There's nothing to watch here.";
            return $result;
        }

        $now = microtime(true);
        $race = $races->get_race($races->current_start($now));
        if (!$race){
            $result['action'] = "OUTPUT";
            $result['data'] = "The track's quiet. There aren't enough worms in the stable to race.";
            return $result;
        }
        $elapsed_ms = ($now - $race['start']) * 1000;
        $intro = "";
        if ($elapsed_ms < $races->duration_ms($race)){
            // Join the race in progress.
            $skip = intval(floor($elapsed_ms / worm_races::TICK_MS));
            $delay = 0;
        }
        else{
            // That one's over; line up for the next.
            $winner = $race['lineup'][$race['winner']-1]['name'];
            $intro = "<div>".htmlspecialchars($winner)." won the last race. ";
            $race = $races->get_race($race['start'] + worm_races::SLOT);
            $skip = 0;
            $delay = intval(($race['start'] - $now) * 1000);
            $minutes = ceil($delay / 60000);
            $intro .= "The next race starts in $minutes minute".($minutes==1 ? "" : "s").".</div>";
        }
        $intro .= $races->racecard($race);

        $lanes = array();
        foreach ($race['lineup'] as $worm){
            $lanes[] = array('name'=>htmlspecialchars($worm['name']), 'early'=>$worm['early'], 'late'=>$worm['late']);
        }
        $play = array(
            'id' => $race['id'],
            'ticks' => $race['ticks'],
            'lanes' => $lanes,
            'skip' => $skip,
            'delay' => $delay,
            'intro' => $intro,
        );
        $js = file_get_contents(BASEPATH."/_classes/commander/commands/js/worm_race.js");
        $result['action'] = "EXECUTE";
        $result['data'] = str_replace("{race}", json_encode($play), $js);
        return $result;
    }
}
?>
