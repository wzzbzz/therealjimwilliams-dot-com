<?php

// Worm races at the racetrack (any location an admin marked with
// `set location attribute racetrack yes`). Ported from the old wormcraft page.
//
// A race starts every SLOT seconds. Its whole run is a sequence of random
// numbers made once and saved, so every player who watches replays the same
// race: each tick every worm draws a number from 1 to 1000 and steps forward if
// it's under that worm's threshold. The baseline (a 19% chance a tick) and the
// loop numbers below are wormcraft's, which were tuned by hand; keep them in
// sync with js/worm_race.js. Numbers run to 1000 rather than wormcraft's 100 so
// a stat point can move the odds by half a percent instead of a whole one.
//
// A worm's threshold moves off the baseline with its stats (class worm below):
// its Wriggle, the weather, its form (fatigue or rust), and, once it's past
// halfway, its Stamina.
class worm_races{

    const SLOT = 120;        // seconds between race starts
    const LANES = 5;
    const ROLL = 1000;       // each tick's numbers run from 1 to ROLL
    const STEP_CHANCE = 191; // baseline: a worm steps when its number is under this (19%)
    const POINT = 5;         // how far one point of modifier moves a threshold (0.5%)
    const STEP_PX = 5;       // pixels per step
    const DISTANCE = 600;    // the leader wins once it's past this many pixels
    const TICK_MS = 28;      // milliseconds per tick
    const MIN_CHANCE = 50;   // thresholds are clamped to this range
    const MAX_CHANCE = 400;

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

    // The race starting at $start, made and saved the first time it's asked
    // for. False when the stable has fewer than two worms.
    public function get_race($start){
        $start = intval($start);
        $rows = $this->db->query("SELECT * FROM worm_races WHERE start_time='$start'");
        if (count($rows)==0){
            $weather = worm_weather::at($start);
            $lineup = $this->pick_lineup($start, $weather);
            if (count($lineup) < 2)
                return false;
            $race = $this->generate($lineup);
            foreach ($race['places'] as $i=>$place){
                $lineup[$i]['place'] = $place;
            }
            // INSERT IGNORE: if two players ask at once, the first sequence wins.
            $this->db->query("INSERT IGNORE INTO worm_races (start_time, ticks, winner, weather, lineup) VALUES ('$start', '".json_encode($race['ticks'])."', '".intval($race['winner'])."', '".$this->db->escape($weather['name'])."', '".$this->db->escape(json_encode($lineup))."')");
            if ($this->db->insert_id()){
                $this->record_results($start, $lineup);
            }
            $rows = $this->db->query("SELECT * FROM worm_races WHERE start_time='$start'");
            if (count($rows)==0)
                return false;
        }
        $race = $rows[0];
        return array(
            'id' => intval($race->id),
            'start' => intval($race->start_time),
            'ticks' => json_decode($race->ticks),
            'winner' => intval($race->winner),
            'weather' => worm_weather::named($race->weather),
            'lineup' => json_decode($race->lineup, true),
            'announced' => intval($race->announced),
        );
    }

    // Keep the races running and tell everyone at the track. Called on every
    // event poll (get_events.php), so races run whenever anyone's online, not
    // only while someone is watching. Once the current race is over it
    // announces the winner, then the next race's card, each exactly once.
    public function run_schedule($now=null){
        if ($now===null)
            $now = microtime(true);
        $race = $this->get_race($this->current_start($now));
        if (!$race)
            return;
        if (($now - $race['start']) * 1000 < $this->duration_ms($race))
            return;
        if ($race['announced'] < 2 && $this->claim($race, 2)){
            $winner = $race['lineup'][$race['winner']-1]['name'];
            $this->announce($race['id'], "<div>".htmlspecialchars($winner)." wins the race!</div>");
        }
        $next = $this->get_race($race['start'] + self::SLOT);
        if ($next && $next['announced'] < 1 && $this->claim($next, 1)){
            $minutes = max(1, ceil(($next['start'] - $now) / 60));
            $this->announce($next['id'], "<div>The worms are lining up for the next race, in $minutes minute".($minutes==1 ? "" : "s").". Type 'watch' to see it.</div>".$this->racecard($next));
        }
    }

    // Move a race's announcement stage up to $stage. True only for the one
    // caller whose update did it, so two players polling at once don't both
    // announce.
    private function claim($race, $stage){
        $token = uniqid('', true);
        $this->db->query("UPDATE worm_races SET announced='".intval($stage)."', claim='$token' WHERE id='".intval($race['id'])."' AND announced < '".intval($stage)."'");
        $rows = $this->db->query("SELECT claim FROM worm_races WHERE id='".intval($race['id'])."'");
        return count($rows) > 0 && $rows[0]->claim == $token;
    }

    // Print $html for everyone at every racetrack.
    private function announce($race_id, $html){
        _event();
        $script = json_encode(array(
            'type' => "EXECUTE",
            'user_id' => 0,
            'user_name' => "",
            'js' => "my_console.print(".json_encode($html).");",
        ));
        foreach ($this->racetracks() as $location_id){
            $event = new event(array(
                'name' => "worm_race_".intval($race_id),
                'event_time' => date('Y-m-d H:i:s'),
                'originator' => 0,
                'location' => $location_id,
                'event_script' => $script,
                'callback' => null,
            ));
            $event->save();
        }
    }

    // Ids of every location marked as a racetrack.
    private function racetracks(){
        $ids = array();
        foreach ($this->db->query("SELECT id FROM locations WHERE attributes LIKE '%racetrack%'") as $row){
            if ($this->is_racetrack($row->id))
                $ids[] = intval($row->id);
        }
        return $ids;
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

    // The racecard for a race, as terminal output.
    public function racecard($race){
        $weather = $race['weather'];
        $output = "<div>Weather: ".$weather['name'].". ".$weather['note']."</div>";
        foreach ($race['lineup'] as $i=>$worm){
            $stats = array();
            foreach (worm::$STATS as $stat=>$abbr){
                $stats[] = $abbr." ".$worm['stats'][$stat].($worm['rising'][$stat] ? "&uarr;" : "&darr;");
            }
            $output .= "<div>Lane ".($i+1).": ".htmlspecialchars($worm['name'])." &nbsp; RTG ".$worm['rating']." &nbsp; ".implode(" &nbsp; ", $stats)." &nbsp; (".$worm['form'].")</div>";
        }
        return $output;
    }

    // The LANES worms nearest their sweet spot at $start, each with its stats,
    // form and thresholds for this race. Lane order is random.
    private function pick_lineup($start, $weather){
        $worms = array();
        foreach ($this->db->query("SELECT * FROM worms ORDER BY id") as $row){
            $worm = new worm($row);
            $worms[] = array('worm'=>$worm, 'off'=>$worm->off_sweet_spot($start), 'tiebreak'=>mt_rand());
        }
        usort($worms, function($a, $b){
            if ($a['off'] != $b['off'])
                return $a['off'] < $b['off'] ? -1 : 1;
            return $a['tiebreak'] < $b['tiebreak'] ? -1 : 1;
        });
        $lineup = array();
        foreach (array_slice($worms, 0, self::LANES) as $entry){
            $lineup[] = $entry['worm']->race_entry($start, $weather);
        }
        shuffle($lineup);
        return $lineup;
    }

    // Mark the lineup as raced and move each worm's rating: every pair of
    // worms in the race counts as a head-to-head, Elo style, so beating
    // higher-rated worms gains more than beating lower-rated ones.
    private function record_results($start, $lineup){
        $n = count($lineup);
        foreach ($lineup as $i=>$worm){
            $delta = 0;
            foreach ($lineup as $j=>$other){
                if ($i==$j)
                    continue;
                $expected = 1 / (1 + pow(10, ($other['rating'] - $worm['rating']) / worm::RATING_SPREAD));
                $score = $worm['place'] < $other['place'] ? 1 : 0;
                $delta += worm::RATING_K * ($score - $expected) / ($n - 1);
            }
            $rating = max(1, intval(round($worm['rating'] + $delta)));
            $won = $worm['place']==1 ? 1 : 0;
            $this->db->query("UPDATE worms SET last_raced_at='".intval($start)."', races=races+1, wins=wins+$won, rating='$rating' WHERE id='".intval($worm['id'])."'");
        }
    }

    // Draw numbers until a worm wins. Same rules as wormcraft's loop: worms
    // move in lane order, a worm takes the lead only by passing the leader, and
    // the race ends on the tick the leader is past DISTANCE. A worm uses its
    // early threshold until it's past halfway, then its late one. Lanes are
    // 1-based.
    private function generate($lineup){
        $lanes = count($lineup);
        $positions = array_fill(1, $lanes, 0);
        $leader = null;
        $ticks = array();
        while (true){
            $tick = array();
            for ($lane=1; $lane<=$lanes; $lane++){
                $n = mt_rand(1, self::ROLL);
                $tick[] = $n;
                $worm = $lineup[$lane-1];
                $threshold = $positions[$lane] > self::DISTANCE/2 ? $worm['late'] : $worm['early'];
                if ($n < $threshold){
                    $positions[$lane] += self::STEP_PX;
                    if ($leader===null || $positions[$lane] > $positions[$leader])
                        $leader = $lane;
                }
            }
            $ticks[] = $tick;
            if ($leader!==null && $positions[$leader] > self::DISTANCE)
                break;
        }
        // Finishing places by distance covered; on a tie the earlier lane
        // places higher, and the winner is always first.
        $order = array_keys($positions);
        usort($order, function($a, $b) use ($positions, $leader){
            if ($a==$leader) return -1;
            if ($b==$leader) return 1;
            if ($positions[$a] != $positions[$b])
                return $positions[$a] > $positions[$b] ? -1 : 1;
            return $a < $b ? -1 : 1;
        });
        $places = array();
        foreach ($order as $place=>$lane){
            $places[$lane-1] = $place + 1;
        }
        return array('ticks'=>$ticks, 'winner'=>$leader, 'places'=>$places);
    }
}

// A worm in the stable. Four D&D-style stats rolled 3d6 at hatching; each
// swings up to BIO_SWING on its own biorhythm cycle counted from the hatch date.
class worm{

    public static $STATS = array('wriggle'=>'WRG', 'stamina'=>'STA', 'constitution'=>'CON', 'grit'=>'GRT');
    public static $CYCLE_DAYS = array('wriggle'=>23, 'stamina'=>28, 'constitution'=>33, 'grit'=>38);
    const BIO_SWING = 2;
    const MAX_FORM_PENALTY = 4;
    const RATING_K = 8;        // most a rating can move in one race
    const RATING_SPREAD = 40;  // a 40-point gap makes the better worm a 10-to-1 favorite head-to-head

    public $id, $name, $base, $hatched_at, $last_raced_at, $rating, $races, $wins;

    public function __construct($row){
        $this->id = intval($row->id);
        $this->name = $row->name;
        $this->hatched_at = intval($row->hatched_at);
        $this->last_raced_at = $row->last_raced_at===null ? null : intval($row->last_raced_at);
        $this->rating = intval($row->rating);
        $this->races = intval($row->races);
        $this->wins = intval($row->wins);
        $this->base = array();
        foreach (self::$STATS as $stat=>$abbr){
            $this->base[$stat] = intval($row->$stat);
        }
    }

    // Hatch a new worm into the stable. Returns its id, or false if the name's taken.
    public static function hatch($name){
        $db = _db();
        if (count($db->query("SELECT id FROM worms WHERE name='".$db->escape($name)."'")) > 0)
            return false;
        $keyvals = array('name'=>$name, 'hatched_at'=>time());
        $modifiers = 0;
        foreach (self::$STATS as $stat=>$abbr){
            $keyvals[$stat] = mt_rand(1,6) + mt_rand(1,6) + mt_rand(1,6);
            $modifiers += self::modifier($keyvals[$stat]);
        }
        // Starting rating from its stats: 50 for an average worm. Races move it from there.
        $keyvals['rating'] = max(1, 50 + 3 * $modifiers);
        $db->insert(array('table'=>'worms', 'keyvals'=>$keyvals));
        return $db->insert_id();
    }

    public static function modifier($score){
        return intval(floor(($score - 10) / 2));
    }

    // The stat with its biorhythm at time $t.
    public function stat($stat, $t){
        return $this->base[$stat] + intval(round(self::BIO_SWING * sin($this->phase($stat, $t))));
    }

    // Is the stat's biorhythm on the way up at time $t?
    public function rising($stat, $t){
        return cos($this->phase($stat, $t)) >= 0;
    }

    // Where the stat is in its cycle. Each worm starts each cycle at its own
    // point (fixed by its name), so worms hatched together don't rise and fall
    // in step.
    private function phase($stat, $t){
        $cycle = self::$CYCLE_DAYS[$stat];
        $offset = (abs(crc32($this->name." ".$stat)) % ($cycle * 1000)) / 1000;
        $days = ($t - $this->hatched_at) / 86400 + $offset;
        return 2 * M_PI * $days / $cycle;
    }

    // Every worm in the stable, best rating first.
    public static function stable(){
        $worms = array();
        foreach (_db()->query("SELECT * FROM worms ORDER BY rating DESC, name") as $row){
            $worms[] = new worm($row);
        }
        return $worms;
    }

    // The rest between races, in seconds, that keeps this worm sharp. A worm
    // with more Constitution recovers sooner and stays sharp longer.
    public function sweet_spot(){
        $con = $this->base['constitution'];
        $from = (24 - $con) * 60;            // 6 to 21 minutes
        $to = $from + $con * 120;            // plus 6 to 36 minutes
        return array($from, $to);
    }

    // Seconds since the worm last raced. A worm that hasn't raced yet counts
    // from its hatching, but is never tired.
    private function rest($t){
        if ($this->last_raced_at===null)
            return max($t - $this->hatched_at, $this->sweet_spot()[0]);
        return $t - $this->last_raced_at;
    }

    // How far, in seconds, this worm's rest at $t is outside its sweet spot.
    public function off_sweet_spot($t){
        list($from, $to) = $this->sweet_spot();
        $rest = $this->rest($t);
        if ($rest < $from)
            return $from - $rest;
        if ($rest > $to)
            return $rest - $to;
        return 0;
    }

    // Fatigue (raced too recently) and rust (hasn't raced in too long) at $t.
    // Fatigue comes off the threshold; rust comes off Wriggle and Stamina.
    public function form($t){
        $form = array('fatigue'=>0, 'rust'=>0, 'label'=>'sharp');
        list($from, $to) = $this->sweet_spot();
        $rest = $this->rest($t);
        if ($rest < $from){
            $form['fatigue'] = intval(ceil(($from - $rest) / $from * self::MAX_FORM_PENALTY));
            $form['label'] = 'tired';
        }
        elseif ($rest > $to){
            $form['rust'] = min(self::MAX_FORM_PENALTY, intval(floor(($rest - $to) / 3600)) + 1);
            $form['label'] = 'rusty';
        }
        return $form;
    }

    // Everything a race needs to know about this worm at $t.
    public function race_entry($t, $weather){
        $stats = array();
        $rising = array();
        foreach (self::$STATS as $stat=>$abbr){
            $stats[$stat] = $this->stat($stat, $t);
            $rising[$stat] = $this->rising($stat, $t);
        }
        $form = $this->form($t);
        $wriggle = self::modifier($stats['wriggle'] - $form['rust']);
        $stamina = self::modifier($stats['stamina'] - $form['rust']);
        $favored = $weather['favors'] ? self::modifier($stats[$weather['favors']]) : 0;
        $points = $wriggle + $weather['shift'] + $favored - $form['fatigue'];
        $early = worm_races::STEP_CHANCE + $points * worm_races::POINT;
        $late = $early + $stamina * worm_races::POINT;
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'rating' => $this->rating,
            'stats' => $stats,
            'rising' => $rising,
            'form' => $form['label'],
            'early' => max(worm_races::MIN_CHANCE, min(worm_races::MAX_CHANCE, $early)),
            'late' => max(worm_races::MIN_CHANCE, min(worm_races::MAX_CHANCE, $late)),
        );
    }
}

// The track's weather, one condition per hour. Each day is fair, wet or hot,
// and the hours of the day run through that kind of day's pattern. A condition
// shifts everyone's threshold and adds the modifier of the stat it favors.
class worm_weather{

    public static $CONDITIONS = array(
        'Mild'      => array('shift'=> 0, 'favors'=>null,           'note'=>"Nothing to it."),
        'Cool'      => array('shift'=>-1, 'favors'=>'constitution', 'note'=>"Sluggish going; hardy worms do better."),
        'Dew'       => array('shift'=> 1, 'favors'=>'wriggle',      'note'=>"A slick track; wrigglers fly."),
        'Dry'       => array('shift'=> 0, 'favors'=>'wriggle',      'note'=>"A fast, hard track."),
        'Drizzle'   => array('shift'=> 0, 'favors'=>'grit',         'note'=>"Getting soft; gritty worms dig in."),
        'Rain'      => array('shift'=>-2, 'favors'=>'grit',         'note'=>"Heavy going; grit counts."),
        'Mud'       => array('shift'=>-3, 'favors'=>'grit',         'note'=>"A bog. Only grit gets through."),
        'Scorching' => array('shift'=>-2, 'favors'=>'constitution', 'note'=>"Baking hot; hardy worms hold up."),
    );

    // Night (22-4), morning (5-9), midday (10-16) and evening (17-21) by kind of day.
    public static $DAYS = array(
        'fair' => array('Cool', 'Dew', 'Dry', 'Mild'),
        'wet'  => array('Mud', 'Drizzle', 'Rain', 'Mud'),
        'hot'  => array('Mild', 'Mild', 'Scorching', 'Dry'),
    );

    public static function at($t){
        $day = date('Y-m-d', $t);
        $kinds = array('fair', 'fair', 'fair', 'wet', 'hot');
        $kind = $kinds[abs(crc32("worm weather ".$day)) % count($kinds)];
        $hour = intval(date('G', $t));
        if ($hour >= 22 || $hour <= 4)
            $part = 0;
        elseif ($hour <= 9)
            $part = 1;
        elseif ($hour <= 16)
            $part = 2;
        else
            $part = 3;
        return self::named(self::$DAYS[$kind][$part]);
    }

    public static function named($name){
        if (!isset(self::$CONDITIONS[$name]))
            $name = 'Mild';
        return array_merge(array('name'=>$name), self::$CONDITIONS[$name]);
    }
}
?>
