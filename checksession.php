<?php
include("bootstrap.php");
include(BASEPATH."/_lib/includer.php");
include(BASEPATH."/_lib/php/user/user.php");

$user = new user();

$result = $user->session_logged_in();

if ($result==true){
    $userinfo = $user->get_logged_in_userinfo();
    $result = array('result'=>$userinfo['username']);
}

else{
    $result = array('result'=>$result);
}

echo json_encode($result);