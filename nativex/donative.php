<?php
include("../bootstrap.php");
require("../_lib/php/nativex/nativex.php");

$key = $_REQUEST['key'];

$c = trim(base64_decode($_REQUEST['c']));

$output = isset($_REQUEST['output'])?$_REQUEST['output']:'json';
$action = $_REQUEST['action'];

$special = $_REQUEST['special'];

$nativex = new NativeX();

if($special=="checked"){
    $nativex->set_output("image");
}

$nativex->setKey($key);

$decode = $nativex->$action($c);

switch($output){
	case 'raw':
		echo $decode;
		break;
	case 'json':
	default:
		$result = array('result'=>$decode);
		echo json_encode($result);
		break;
}
