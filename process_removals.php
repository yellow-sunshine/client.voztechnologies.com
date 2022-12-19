<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	print "failed|@|Not authorized";
	exit();
}

# Include configurations for the site
require_once('/var/www/shared_files/site.init.v2.php');

# Include site functions
include_once(INCLUDES.'/functions.v2.php');


site_init('client.voztechnologies.com', 'voz');

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();

function delMalChar($val){
	preg_replace("@x00|\\\n|\\\r|\\\|\'|\"|x1a@i",'',$val);
	return $val;
}


function delImages($loc_id, $imgName, $db){
	@unlink($_SERVER['DOCUMENT_ROOT']."/cities_".$db."/".$loc_id."/large/".$imgName);
	@unlink($_SERVER['DOCUMENT_ROOT']."/cities_".$db."/".$loc_id."/thumbnail/".$imgName);
}

function deleteImagesFromAllDB($phone,$forced_db){
	global $records;
	$records->city_info($forced_db);
	$recordsCache = $records->overview($phone,$updatingCache);

	foreach($recordsCache['posts'] as $postCount=>$postData){
		foreach($postData as $key=>$var){$post[$key]=$var;}
		if($post['image1']){
			delImages($post['loc_id'],$post['image1'], $forced_db); 
			$total_image_count++;
			if($post['image2']){
				delImages($post['loc_id'],$post['image2'], $forced_db);
				$total_image_count++;
				if($post['image3']){
					delImages($post['loc_id'],$post['image3'], $forced_db); 
					$total_image_count++;
					if($post['image4']){
						delImages($post['loc_id'],$post['image4'], $forced_db);
						$total_image_count++;
						if($post['image5']){
							delImages($post['loc_id'],$post['image5'], $forced_db);
							$total_image_count++;
							if($post['image6']){
								delImages($post['loc_id'],$post['image6'], $forced_db);
								$total_image_count++;
								if($post['image7']){
									delImages($post['loc_id'],$post['image7'], $forced_db); 
									$total_image_count++;
								}
							}
						}
					}
				}
			}
		}
		$conn->query("DELETE FROM `".$forced_db."`.`links` WHERE phone ='".$recordsCache['summary']['phone']."' LIMIT 1");
		$del_count++;
	}

}


$phone = delMalChar($_POST['phone']);
if($phone && strlen($phone) == 10 && is_numeric($phone)){
	$phone = $phone[0].$phone[1].$phone[2]."-".$phone[3].$phone[4].$phone[5]."-".$phone[6].$phone[7].$phone[8].$phone[9];
}else{
	print "Invalid phone number";
	exit();
}

switch($_POST['action']){
	case 'add': 	$site_id = delMalChar($_POST['site_id']);
					$sql = "SELECT vs.site_type
							FROM `voz`.`sites` vs
							WHERE vs.site_id='".$site_id."'
							LIMIT 1";
					$site_type = $conn->query($sql)->fetch_object()->site_type;
					if($site_id == 911){
						// Delete all images and posts
						deleteImagesFromAllDB($phone,'em');
						deleteImagesFromAllDB($phone,'bs');
						deleteImagesFromAllDB($phone,'ts');
						deleteImagesFromAllDB($phone,'mt');
						// Prevent any news ads, if downloaded, from being displayed on any future sites too
						$result = $conn->query("INSERT IGNORE INTO `voz`.`banned_phone` SET phone='".$phone."', site_id='100'");
						$result = $conn->query("INSERT IGNORE INTO `voz`.`banned_phone_waiting` SET phone='".$phone."', site_id='100', email='admin@voztechnologies.com', processed=1, ip_address='8.8.8.8'");
					}else{
						$result = $conn->query("INSERT IGNORE INTO `voz`.`banned_phone_waiting` SET phone='".$phone."', site_id=".$site_id.", email='admin@voztechnologies.com', processed=1, ip_address='8.8.8.8'");
						$result = $conn->query("INSERT IGNORE INTO `voz`.`banned_phone` SET phone='".$phone."', site_id=".$site_id);
					}

					if($result){
						$removing_site_name = $conn->query("SELECT site_name as removing_site_name FROM `voz`.`sites` WHERE site_id='".$site_id."' LIMIT 1")->fetch_object()->removing_site_name;					
						$memcache->set("ban-".$site_id."-".$phone, 1, 0, 300); // Set cache for 5 min. If someone else visits, it'll get banned in memcache for longer
						$records->phone_banned_sitelist($site_id,1); // Force a reload of the banlist
						$memcache->set("records-".$site_type."-".$phone, 1, 0, 1); // Set the cache to 1 second
						print "removal|@|success|@|".$phone." was removed from ".$removing_site_name.". To see the results, you can add ?updatecache=1 at the end of the url when viewing the ad. This forces a refresh. This does not work on all sites however. If it does not, removals can take up to 4hrs.|@|$add_sql";	
					}else{
						print "removal|@|fail|@|".$site_id."|@|Query Failed";
					}
				 	break;
	case 'search': 	$sql = "SELECT b.* , s.site_name
							FROM `voz`.`banned_phone_waiting` b
							INNER JOIN `voz`.`sites` s ON b.site_id=s.site_id
							WHERE b.phone='".$phone."'
							LIMIT 4000";
					$result = $conn->query($sql);
					if($result){
						print "search|@|success|@|
								<table id='searchResultsTable' class='display nowrap table-bordered table-striped table-xxs'>
								<thead><tr><th>Ban ID</th><th>Site</th><th>Date Added</th><th>Status</th></tr></thead>";
						while($row = $result->fetch_assoc()){
							if($row['processed'] == 0){ $status = 'Waiting';
							}elseif($row['processed'] == 1){$status = 'Processed';
							}elseif($row['processed'] == 2){$status = 'Too Many Submits';	
							}else{$status = "Unknown";}
							print "<tr><td>".$row['ban_id']."</td><td>".$row['site_name']."</td><td>".$row['date_added']."</td><td>".$status."</td></tr>";
						}
						print "</table>|@|".$sql;
					}else{
						print "search|@|fail|@|".$sql;
					}
				 	break;
	default: 		
					break;
}
	
?>