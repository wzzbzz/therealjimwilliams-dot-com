<?php
include("bootstrap.php");
include(BASEPATH."/_lib/includer.php");
include(BASEPATH."/_classes/learner/learner.php");
include(BASEPATH."/_classes/commander/commander.php");

$c = new commander($PHPSESSID);
$result = $c->parse_command($t);

echo json_encode($result);

$l = new Learner();
$l->storeInput($t,$PHPSESSID);

// store 
?>