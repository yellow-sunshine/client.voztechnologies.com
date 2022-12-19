<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=proxies');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

// Get a list of all new IP's
$response = file_get_contents("http://www.sharedproxies.com/api.php?m=mailbrent%40gmail.com&s=01f3069c00e17236ed9142a19679a8fe3647b4e3&do=switchall");

$response = explode("\n",$response);

if(count($response) > 1){
	$lastYear = date('Y')-1;
	// Remove all the old proxies and add the new ones
	$removeOldProxiesResult = mysqli_query($connect,"DELETE FROM proxy") or die(mysqli_error());
	foreach($response as $key=>$val){
		$proxy = explode(":",trim($val));
		//print $proxy[0]."---".$proxy[1]."<br />";
		$sql = "INSERT INTO proxy SET 
				ip='".mysqli_real_escape_string($connect,$proxy[0])."', 
				port='".mysqli_real_escape_string($connect,$proxy[1])."', 
				website='http://sharedproxies.com', 
				add_date='".date('Y-m-d g:i:s')."', 
				ban_date='".$lastYear.date('-m-d g:i:s')."', 
				ban_time=0, 
				ban_count=0, 
				enabled=1";
		mysqli_query($connect,$sql) or die(mysqli_error());
	}
}
?>






