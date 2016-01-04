<?php
// this file initialize default conf for the system

if(!isset($_SESSION))
	session_start();
function returnIfRequestExist($var,$mode="post",$default="")
{
	$return=$default;
	switch($mode)
	{
		case "get":
			if(isset($_GET[$var]))
				$return=$_GET[$var];
			break;
		case "post":
			if(isset($_POST[$var]))
				$return=$_POST[$var];
			break;
		case "session":
			if(isset($_SESSION[$var]))
				$return=$_SESSION[$var];
			break;
		default:
			if(isset($_SESSION[$var]))
				$return= $_SESSION[$var];
			else if(isset($_REQUEST[$var]))
				$return= $_REQUEST[$var];
			else if(isset($_COOKIE[$var]))
				$return= $_COOKIE[$var];
			break;
	}
	return $return;
}
function request($var)
{
	return returnIfRequestExist($var,"","");
}
function requestMethod($var,$method)
{
	return returnIfRequestExist($var,$method,"");
}
function requestDefault($var,$default)
{
	return returnIfRequestExist($var,"",$default);
}

function getTimestamp()
{
	return $_SERVER['REQUEST_TIME'];
}
?>
