<?php
include("../bootstrap.php");
require("../_lib/php/nativex/nativex.php");

$key = $argv[1];

$c = trim($argv[2]);

$output = 'raw';

$action = $argv[3];

//$special = $_REQUEST['special'];

$nativex = new NativeX();

//if($special=="checked"){
  //  $nativex->set_output("image");
//}

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

