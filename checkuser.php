<?php
include("bootstrap.php");
include("_lib/includer.php");
include("_lib/php/user/user.php");

$user = new user();

$result = array();

if ($user->session_logged_in($_REQUEST['PHPSESSID'])){
    $result['result']='false';
    $result['reason']='USERLOGGEDIN';
}
else if ($user->user_exists($_REQUEST['u'])){
    $result['result']="true";
}
else{
    $result['result']="false";
    $result['reason']="NEWUSER";
}

echo json_encode($result);
?>