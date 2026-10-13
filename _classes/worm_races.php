<?php

// Worm races at the racetrack (any location an admin marked with
// `set location attribute racetrack yes`). Ported from the old wormcraft page.
//
// A race starts every SLOT seconds. Its whole run is a sequence of random
// numbers made once and saved, so every player who watches replays the same
// race: each tick every worm draws a number from 1 to 100 and steps forward if
// it's under STEP_CHANCE. The loop numbers below are wormcraft's, which were
// tuned by hand; keep them in sync with js/worm_race.js.
class worm_races{

    const SLOT = 120;        // seconds between race starts
    const LANES = 5;
    const STEP_CHANCE = 20;  // a worm steps when its number is under this
    const STEP_PX = 5;       // pixels per step
    const DISTANCE = 600;    // the leader wins once it's past this many pixels
    const TICK_MS = 28;      // milliseconds per tick

    private $db;

    public function __construct(){
        $this->db = _db();
    }

    // Start time of the race running now, or the last one if it's over.
    public function current_start($now=null){
        if ($now===null)
            $now = time();
        return intval(floor($now / self::SLOT) * self::SLOT);
    }

    // The race starting at $start, made and saved the first time it's asked for.
    public function get_race($start){
        $start = intval($start);
        $rows = $this->db->query("SELECT * FROM worm_races WHERE start_time='$start'");
        if (count($rows)==0){
            $race = $this->generate();
            // INSERT IGNORE: if two players ask at once, the first sequence wins.
            $this->db->query("INSERT IGNORE INTO worm_races (start_time, ticks, winner) VALUES ('$start', '".json_encode($race['ticks'])."', '".intval($race['winner'])."')");
            $rows = $this->db->query("SELECT * FROM worm_races WHERE start_time='$start'");
        }
        $race = $rows[0];
        return array(
            'id' => intval($race->id),
            'start' => intval($race->start_time),
            'ticks' => json_decode($race->ticks),
            'winner' => intval($race->winner),
        );
    }

    // Length of a race in milliseconds.
    public function duration_ms($race){
        return count($race['ticks']) * self::TICK_MS;
    }

    // Is $location_id marked as the racetrack?
    public function is_racetrack($location_id){
        $rows = $this->db->query("SELECT attributes FROM locations WHERE id='".intval($location_id)."'");
        if (count($rows)==0)
            return false;
        $attributes = json_decode($rows[0]->attributes);
        if (!$attributes || !isset($attributes->racetrack))
            return false;
        return !in_array(strtolower(trim($attributes->racetrack)), array("", "no", "0", "false"));
    }

    // Draw numbers until a worm wins. Same rules as wormcraft's loop: worms
    // move in lane order, a worm takes the lead only by passing the leader, and
    // the race ends on the tick the leader is past DISTANCE. Lanes are 1-based.
    private function generate(){
        $positions = array_fill(1, self::LANES, 0);
        $leader = null;
        $ticks = array();
        while (true){
            $tick = array();
            for ($lane=1; $lane<=self::LANES; $lane++){
                $n = mt_rand(1, 100);
                $tick[] = $n;
                if ($n < self::STEP_CHANCE){
                    $positions[$lane] += self::STEP_PX;
                    if ($leader===null || $positions[$lane] > $positions[$leader])
                        $leader = $lane;
                }
            }
            $ticks[] = $tick;
            if ($leader!==null && $positions[$leader] > self::DISTANCE)
                break;
        }
        return array('ticks'=>$ticks, 'winner'=>$leader);
    }
}
?>
