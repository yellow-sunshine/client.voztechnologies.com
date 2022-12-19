<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=records');
	exit();
}

if(!$_POST['forced_db']){
	print "No db selected from search form";
	exit();
}else{
	$_SESSION['user']['selected_db'] = $_POST['forced_db'];
	$forced_db = $_SESSION['user']['selected_db'];
}

require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz', $forced_db);

# Include site functions
require_once('/var/www/shared_files/functions.v2.php');


$phone = $conn->real_escape_string($_POST['phone']);
$phone = $phone[0].$phone[1].$phone[2].'-'.$phone[3].$phone[4].$phone[5].'-'.$phone[6].$phone[7].$phone[8].$phone[9];
$bpid = $conn->real_escape_string($_POST['bpid']);

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();
$records->city_info($forced_db);
$records->locations($_GET['updatecache']);
if(isset($_POST['phone'])){
	$recordsCache = $records->overview($phone,1); // We are always updating cache when doing a search in admin
}
if(isset($_POST['bpid']) && !$recordsCache){
	$post = $records->post($bpid,NULL,1,'bp_id');
}

if(empty($recordsCache['posts'][0]) && $_POST['phone']){
	// No record found for this phone number
	include_once(INCLUDE_PATH.'/header.php');
	?>
	<div class="panel invoice-grid">
		<div class="row">
			<div class="col-sm-12 dbAcolor" style="text-align:center;">
				<h1>No records found for phone number "<?php print $phone;?>"</h1>
			</div>
		</div>
	</div>
<?php
}

if(empty($post['bp_id']) && $_POST['bpid']){
	// No record found for this bpid
	include_once(INCLUDE_PATH.'/header.php');
	?>
	<div class="panel invoice-grid">
		<div class="row">
			<div class="col-sm-12 dbAcolor" style="text-align:center;">
				<h1>No records found for bpid "<?php print $bpid;?>"</h1>
			</div>
		</div>
	</div>
	<?php
}

if(!empty($recordsCache['posts'][0]) && $_POST['phone']){
	// a record was found, lets forward to it
	header('Location://'.ADMIN_DOMAIN.'/overview.php?phone='.$phone.'&force_db='.$_SESSION['user']['selected_db']);
	exit();
}

if(!empty($post['bp_id']) && $_POST['bpid']){
	// a record was found, lets forward to it
	header('Location://'.ADMIN_DOMAIN.'/post.php?post_id='.$post['post_id'].'&phone='.$post['phone'].'&force_db='.$_SESSION['user']['selected_db']);
	exit();
}
include_once(INCLUDE_PATH.'/footer.php');
exit();
?>