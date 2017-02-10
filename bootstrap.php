<?php
//require_once('_lib/includer.php');
//require_once('definitions.php');

define ("BASEPATH",'/home/southsideslim/therealjimwilliams.com/');


//always be setting session
$ok = @session_start();

if(!$ok){
	session_regenerate_id(true); // replace the Session ID
	@session_start(); // restart the session (since previous start failed)
}
extract($_REQUEST);

if(strlen($PHPSESSID))
{
    session_id($PHPSESSID);
}

