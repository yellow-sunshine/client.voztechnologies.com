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

$ip=mysqli_real_escape_string($connect,$_POST['proxyip']);
$port=mysqli_real_escape_string($connect,$_POST['proxyport']);
$proxyid=mysqli_real_escape_string($connect,$_POST['proxyid']);

if($ip && $port){
		
	// Get a list of all new IP's
	$response = file_get_contents("http://www.sharedproxies.com/api.php?m=mailbrent%40gmail.com&s=01f3069c00e17236ed9142a19679a8fe3647b4e3&do=switchone&ip=$ip:$port");

	if($response){
		$lastYear = date('Y')-1;
		
		// Update proxy ID with the new proxy IP and port number
		$proxy = explode(":",trim($response));
		if(preg_match(":ERROR:i",$proxy[0]) || $proxy[0] == "ERROR"){
			print "renewone|@|fail|@|Error";
			exit();
		}
		
		if(!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $proxy[0])){
			$response = file_get_contents("http://www.sharedproxies.com/api.php?m=mailbrent%40gmail.com&s=01f3069c00e17236ed9142a19679a8fe3647b4e3&do=switchone&ip=$ip:8080");
			$proxy = explode(":",trim($response));
			if(!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $proxy[0])){
				print "renewone|@|fail|@|Error";
				exit();
			}
		}
		
			$sql = "UPDATE proxy SET 
					ip='".mysqli_real_escape_string($connect,$proxy[0])."', 
					port='".mysqli_real_escape_string($connect,$proxy[1])."', 
					website='http://sharedproxies.com', 
					add_date='".date('Y-m-d g:i:s')."', 
					ban_date='".$lastYear.date('-m-d g:i:s')."', 
					ban_time=0, 
					ban_count=0, 
					enabled=1
					WHERE
					proxy_id='$proxyid'
					LIMIT 1";
			$finalResult = mysqli_query($connect,$sql) or die(mysqli_error());
		
			if($finalResult){
				print "renewone|@|success|@|".$proxy[0]."|@|".$proxy[1]."|@|".$proxyid;
				exit();
			}else{
				print "renewone|@|fail|@|1";
				exit();
			}
		
	}else{
		print "renewone|@|fail|@|2";
		exit();
	}
}else{
	print "renewone|@|fail|@|3";
	exit();
}
?>