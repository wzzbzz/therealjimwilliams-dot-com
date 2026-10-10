<?php
include("bootstrap.php");
include("_lib/includer.php");
include("_lib/php/user/user.php");

$u = $_REQUEST['u'];
$p = $_REQUEST['p'];
$s = $_REQUEST['PHPSESSID'];

$user = new user();

$result = array('result'=>$user->create_user($u,$p));

echo json_encode($result);
