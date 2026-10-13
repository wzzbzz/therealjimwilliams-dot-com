<?php
include("bootstrap.php");
include("_lib/includer.php");
include("_lib/php/user/user.php");

$u = $_REQUEST['u'];
$p = $_REQUEST['p'];
$s = $_REQUEST['PHPSESSID'];
$d = $_REQUEST['d'];

$user = new user();
$result = $user->doLogin($u,$p,$s,$d);

$_SESSION['userid'] = $result['id'];
$_SESSION['username'] = $result['username'];
$_SESSION['domain'] = $result['domain'];

echo json_encode($result);
