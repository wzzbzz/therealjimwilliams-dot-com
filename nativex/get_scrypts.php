<?php
include("../bootstrap.php");
include("../_lib/includer.php");

extract($_REQUEST);
_user();
$user = new User();
$scrypts = $user->get_scrypts($u,"js");
$result = new stdClass();
$result->result="success";
$result->scrypts=$scrypts;
echo json_encode($result);