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

// Get the databse we are selecting through the URL
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


if(empty($_POST['post_id'])){
	print "fail|@|No post_id";
	exit();
}else{
	$post_id = $conn->real_escape_string($_POST['post_id']);
}

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
$post = $records->post($post_id,$phone,1,'post_id');

function delImages($loc_id, $imgName, $db){
	@unlink($_SERVER['DOCUMENT_ROOT']."/cities_".$db."/".$loc_id."/large/".$imgName);
	@unlink($_SERVER['DOCUMENT_ROOT']."/cities_".$db."/".$loc_id."/thumbnail/".$imgName);
}

for($i=1; $i <= 7; $i++){
	if($post['image'.$i]){
		delImages($post['loc_id'], $post['image'.$i], $forced_db);
		$total_image_count++;
	}
}

$conn->query("UPDATE `".$_SESSION['user']['selected_db']."`.`links` 
			 SET image1='', image2='', image3='', image4='', image5='', image6='', image7='', body='admin deleted', title='admin deleted' 
			 WHERE post_id ='".$post_id."' LIMIT 1");


$recordsCache = $records->overview($phone,1);
print "success|@|postDeleted|@|".$post_id."|@|Deleted post and $total_image_count images";


?>