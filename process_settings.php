<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=settings');
	exit();
}

$variable = mysqli_real_escape_string($connect,$_POST['variable']);
$value = mysqli_real_escape_string($connect,$_POST['value']);

$sql = "UPDATE `voz`.`voz_settings` SET `value` = '".$value."' WHERE variable='".$variable."' LIMIT 1";

if(mysqli_query($connect, $sql)){
	print "success|@|".$variable."|@|".$sql;
	
	$memcache = new Memcache;
	$cacheAvailable = $memcache->connect('127.0.0.1', '11211');
	$memcache->set('voz_settings_val', ' ',0,1); // Set it for 1 minute so everything does an auto refresh as a fail safe just because.
	
	// If we are changing a setting that effects the websites, kill their memcache so each site is forced to re-read the data from the database
	if($variable=='disable_all_sites' || $variable=='disable_all_sites_msg'){
		$voz_sitesDS = mysqli_query($connect,"SELECT site_name FROM `voz`.`sites` LIMIT 2000");
		while($row = mysqli_fetch_array($voz_sitesDS)){// loop over each site in the database
			$key = 'site_settings-'.$row['site_name'];
			$memcache->set($key, ' ',0,1);// Set memcache to 1 second
		}
	}
	
	
	
	
}else{
	print "fail|@|".$variable."|@|".$value."|@|$sql";	
}
?>