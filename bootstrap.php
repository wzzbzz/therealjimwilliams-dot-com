<?php
//require_once('_lib/includer.php');
//require_once('definitions.php');

// Local dev on modern PHP: keep notices/deprecations out of the response body.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

// Align PHP's clock with MySQL (which runs in local/Eastern time) so scheduled
// events (event_time < NOW()) fire correctly. Mismatch here = elevator stuck "en route".
date_default_timezone_set('America/New_York');

define ("BASEPATH",'/Users/jamespwilliams/Projects/therealjimwilliams/therealjimwilliams.com/');


//always be setting session
$ok = @session_start();

if(!$ok){
	session_regenerate_id(true); // replace the Session ID
	@session_start(); // restart the session (since previous start failed)
}
extract($_REQUEST);

if(isset($PHPSESSID) && strlen($PHPSESSID))
{
    session_id($PHPSESSID);
}

