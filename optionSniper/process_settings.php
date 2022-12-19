<?php
session_start();
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once(__DIR__.'/get_settings.php');

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=tweets');
	exit();
}

$variable = mysqli_real_escape_string($connect,$_POST['variable']);
$value = mysqli_real_escape_string($connect,$_POST['value']);

$sql = "UPDATE `twitter_sniper`.`settings` SET `value` = '".$value."' WHERE variable='".$variable."' LIMIT 1";

if(mysqli_query($connect, $sql)){
	print "success|@|".$variable."|@|".$sql;

	$memcache = new Memcache;
	$cacheAvailable = $memcache->connect('127.0.0.1', '11211');
	$memcache->delete('optionsniper_settings'); // Delete the sniper settings so we will have to get it again
}else{
	print "fail|@|".$variable."|@|".$value."|@|$sql";
}
?>