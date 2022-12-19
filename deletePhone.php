<?php
/*
	This file will delete each post and it's images that the $sql query returns.
	Make sure all images, the post id, and the loc_id are returned by $sql or the script will destroy the db-imagefile sync
	ALSO, make sure the DOWNLOADED_IMAGES_PATH constant is defined properly, you only get ONE shot to delete images. If not, they wil remain forever.
	select db in the url: http://cron.blr.pw:8082/scripts/delete_many_posts.php?db=mt
*/
session_start();
if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	print "fail|@|Authentication Error";
	exit();
}

// Ge t the databse we are selecting through the URL
if(empty($_SESSION['user']['selected_db']) || empty($_POST['forced_db']) || $_POST['forced_db']!=$_SESSION['user']['selected_db']){
	print "fail|@|No DB selected";
	exit();
}else{
	$_SESSION['user']['selected_db'] = $_POST['forced_db'];
	$forced_db = $_POST['forced_db'];
}

// Get connection to DB and other settings
require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz', $_SESSION['user']['selected_db']);

if(empty($_POST['phone'])){
	print "fail|@|No phone";
	exit();
}else{
	$phone = $conn->real_escape_string($_POST['phone']);
}

# Include site functions
require_once('/var/www/shared_files/functions.v2.php');

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();
$records->city_info($forced_db);
$recordsCache = $records->overview($phone,1); // Here we are going to update the cache to make sure we get all of the posts

function delImages($loc_id, $imgName, $db){
	@unlink($_SERVER['DOCUMENT_ROOT']."/cities_".$db."/".$loc_id."/large/".$imgName);
	@unlink($_SERVER['DOCUMENT_ROOT']."/cities_".$db."/".$loc_id."/thumbnail/".$imgName);
}

foreach($recordsCache['posts'] as $postCount=>$postData){
	foreach($postData as $key=>$var){$post[$key]=$var;}
	if($post['image1']){
		delImages($post['loc_id'],$post['image1'], $_SESSION['user']['selected_db']); 
		$total_image_count++;
		if($post['image2']){
			delImages($post['loc_id'],$post['image2'], $_SESSION['user']['selected_db']);
			$total_image_count++;
			if($post['image3']){
				delImages($post['loc_id'],$post['image3'], $_SESSION['user']['selected_db']); 
				$total_image_count++;
				if($post['image4']){
					delImages($post['loc_id'],$post['image4'], $_SESSION['user']['selected_db']);
					$total_image_count++;
					if($post['image5']){
						delImages($post['loc_id'],$post['image5'], $_SESSION['user']['selected_db']);
						$total_image_count++;
						if($post['image6']){
							delImages($post['loc_id'],$post['image6'], $_SESSION['user']['selected_db']);
							$total_image_count++;
							if($post['image7']){
								delImages($post['loc_id'],$post['image7'], $_SESSION['user']['selected_db']); 
								$total_image_count++;
							}
						}
					}
				}
			}
		}
	}
	$conn->query("DELETE FROM `".$_SESSION['user']['selected_db']."`.`links` WHERE phone ='".$recordsCache['summary']['phone']."' LIMIT 1");
	$del_count++;
}


if($del_count){
	$recordsCache = $records->overview($phone,1);
	print "success|@|phoneDeleted|@|".$_POST['post_id']."|@|Deleted $del_count postings and $total_image_count images";
}

?>