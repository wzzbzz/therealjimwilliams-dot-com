<?php
include("bootstrap.php");
include(BASEPATH."/_lib/includer.php");
include(BASEPATH."/_lib/php/user/user.php");

$user = new user();

$user_id = $user->get_logged_in_user_id();
$location = $user->get_location($user_id);
$location_id = $location->id;

_events();
$events = new events();

$events->process_events();

// Keep the worm races at the racetrack running and announced.
require_once(BASEPATH."/_classes/worm_races.php");
$worm_races = new worm_races();
$worm_races->run_schedule();

$user = _user();
$user->set($user_id);
$user->process_user_status();


$location_events = $events->get_scheduled_user_events($user_id);

$output = array();
$c=0;

foreach ($location_events as $event){

    $e = new event(array("id"=>$event->event_id));
    $event_script = $e->get_event_script();
    $event_script = str_replace("[user_id]",$user_id,$event_script);
    $output[]= json_decode($event_script);
    $e->delete_user_event($user_id, $event->event_id);

}

echo json_encode($output);
