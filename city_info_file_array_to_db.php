<?php
// This script is ment to take the city_info.php array and load it into the databse
// It searches for lon and lat in different locations for each city
exit();
define('DB_NAME','');
/////
///// Make sure you change the input file... the city_info.php file needs to be the correct one for the db... city_info_bs.php, city_info_mt.php, city_info_ts.php
/////


$conn = new mysqli($host, 'voz', "2r5sT6bjeCGVbeUe", DB_NAME);
include_once('/var/www/shared_files/city_info.php');

print "<br />Starting<br />";


foreach($city_info as $subdomain=>$city){
	$i++;
	print "<hr><strong>".$city['display_name']."</strong><br />";
	$post_count = $conn->query("SELECT post_count FROM `".DB_NAME."`.`cities` WHERE loc_id='".$city['loc_id']."'")->fetch_object()->post_count;
	$scrape_count = $conn->query("SELECT scrape_count FROM `".DB_NAME."`.`cities` WHERE loc_id='".$city['loc_id']."'")->fetch_object()->scrape_count; 
	$lonResult = $conn->query("SELECT lon FROM `em`.`cities` WHERE display_name='".$city['display_name']."' LIMIT 1"); 
	if($lonResult->num_rows){$lonResult->data_seek(0);$lon = $lonResult->fetch_array(MYSQLI_ASSOC);$lon=$lon['lon'];}else{$lon=NULL;}
	$latResult = $conn->query("SELECT lat FROM `em`.`cities` WHERE display_name='".$city['display_name']."'");
	if($latResult->num_rows){$latResult->data_seek(0);$lat = $latResult->fetch_array(MYSQLI_ASSOC);$lat=$lat['lat'];}else{$lat=NULL;}
	print "1st try vars: post_count:$post_count scrape_count:$scrape_count lon:$lon lat:$lat<br />";
	
	
	if(empty($lon) || empty($lat)){
		print "2nd try<br />";
		$lonResult = $conn->query("SELECT lon FROM `bs`.`cities` WHERE display_name='".$city['display_name']."' LIMIT 1"); 
		if($lonResult->num_rows){$lonResult->data_seek(0);$lon = $lonResult->fetch_array(MYSQLI_ASSOC);$lon=$lon['lon'];}else{$lon=NULL;}
		$latResult = $conn->query("SELECT lat FROM `bs`.`cities` WHERE display_name='".$city['display_name']."'");
		if($latResult->num_rows){$latResult->data_seek(0);$lat = $latResult->fetch_array(MYSQLI_ASSOC);$lat=$lat['lat'];}else{$lat=NULL;}	
	}
	if(empty($lon) || empty($lat)){
		print "3nd try<br />";
		$lonResult = $conn->query("SELECT lon FROM `mt`.`cities` WHERE display_name='".$city['display_name']."' LIMIT 1"); 
		if($lonResult->num_rows){$lonResult->data_seek(0);$lon = $lonResult->fetch_array(MYSQLI_ASSOC);$lon=$lon['lon'];}else{$lon=NULL;}
		$latResult = $conn->query("SELECT lat FROM `mt`.`cities` WHERE display_name='".$city['display_name']."'");
		if($latResult->num_rows){$latResult->data_seek(0);$lat = $latResult->fetch_array(MYSQLI_ASSOC);$lat=$lat['lat'];}else{$lat=NULL;}		
	}
	if(empty($lon) || empty($lat)){
		print "4nd try<br />";
		$lonResult = $conn->query("SELECT lon FROM `ts`.`cities` WHERE display_name='".$city['display_name']."' LIMIT 1"); 
		if($lonResult->num_rows){$lonResult->data_seek(0);$lon = $lonResult->fetch_array(MYSQLI_ASSOC);$lon=$lon['lon'];}else{$lon=NULL;}
		$latResult = $conn->query("SELECT lat FROM `ts`.`cities` WHERE display_name='".$city['display_name']."'");
		if($latResult->num_rows){$latResult->data_seek(0);$lat = $latResult->fetch_array(MYSQLI_ASSOC);$lat=$lat['lat'];}else{$lat=NULL;}		
	}
	if(empty($lon) || empty($lat)){
		print "5nd try<br />";
		$lonResult = $conn->query("SELECT lon FROM `em`.`cities2` WHERE display_name='".$city['display_name']."' LIMIT 1"); 
		if($lonResult->num_rows){$lonResult->data_seek(0);$lon = $lonResult->fetch_array(MYSQLI_ASSOC);$lon=$lon['lon'];}else{$lon=NULL;}
		$latResult = $conn->query("SELECT lat FROM `em`.`cities2` WHERE display_name='".$city['display_name']."'");
		if($latResult->num_rows){$latResult->data_seek(0);$lat = $latResult->fetch_array(MYSQLI_ASSOC);$lat=$lat['lat'];}else{$lat=NULL;}		
	}
	if(empty($lon) || empty($lat)){
		print "6nd try<br />";
		$lon = $city['lon']; 
		$lat = $city['lat']; 	
	}
	print "Final lon and lat lon:".print_r($lon)." lat:$lat<br /><br />";

	$query="INSERT INTO `".DB_NAME."`.`cities2` SET 
			loc_id='".$city['loc_id']."', 
			sub_domain='".$subdomain."', 
			display_name='".$city['display_name']."', 
			state='".$city['state']."', 
			country='".$city['country']."', 
			cache_timeout='".$city['cache_timeout']."', 
			lon='".preg_replace('/\,/','',$lon)."', 
			lat='".preg_replace('/\,/','',$lat)."', 
			url_backpage='".$city['url_backpage']."',
			url_backpage_adult='".$city['url_backpage_adult']."',
			post_count='$post_count', 
			scrape_count='$scrape_count'"; 
	print $query."<br />";
	$conn->query($query);
}
print "<br />Done<br />";

?>