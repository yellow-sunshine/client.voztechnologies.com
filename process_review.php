<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	print "User Not Authorized";
	exit();
}

require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz');

require_once('/var/www/shared_files/functions.v2.php');

# This is part of the Ajax that loads a new review when pressing next or prev on the admin reviews page.
if( isset($_POST['direction']) ){
	if($_POST['direction'] == 'next'){
		$ltgt='>';
		$descoa = '';
	}else{
		$ltgt='<';	
		$descoa = 'DESC';
	}
	$reviewLoadSQL = "/* process ad review*/ SELECT r.*, s.site_name, s.site_type 
			FROM `voz`.`reviews` r INNER JOIN sites s on s.site_id = r.site_id 
			WHERE r.review_status = 1 
				  AND r.review_id !='".$_POST['review_id']."'
				  AND r.review_id ".$ltgt." ".$_POST['review_id']."
			ORDER BY r.review_id ".$descoa."
			LIMIT 1";
	$review_result = $conn->query($reviewLoadSQL) or trigger_error($conn->error." [$reviewLoadSQL]");
	print "changeReview|@|";	
	while($row = $review_result->fetch_assoc()){
		?>			
		<div class="panel-heading">
			<h6 class="panel-title">Recent Reviews</h6>
			<div class="heading-elements">
				<ul class="pager pager-sm">
					<li><a href="#" onclick="changeReview('prev',<?php print $row['review_id'];?>)">&larr; Prev</a></li>
					<li><a href="#" onclick="changeReview('next',<?php print $row['review_id'];?>)">Next &rarr;</a></li>
				</ul>
			</div>
		</div>
		<div class="panel-body" id='recentReviewsBody'>
			<strong>Date: </strong><?php print $row['review_date'];?> <strong>URL: </strong><a href='http://www.<?php print $row['site_name']; ?>/<?php print $row['review_phone'];?>' target='_blank'>http://<?php print $row['site_name']; ?>/<?php print $row['review_phone'];?></a><br />
			<?php print preg_replace("/\\\'/","'",$row['review_body']); ?>
		</div>
		<?php
		$nrc++;
	}
	if(empty($nrc)){
		?>
        <div class="panel-heading">
            <h6 class="panel-title">Recent Reviews</h6>
            <div class="heading-elements">
                <ul class="pager pager-sm">
                    <li><a href="#" onclick="changeReview('prev',<?php print $_POST['review_id'];?>)">&larr; Prev</a></li>
                    <li><a href="#" onclick="changeReview('next',<?php print $_POST['review_id'];?>)">Next &rarr;</a></li>
                </ul>
            </div>
        </div>
		<div class="panel-body" id='recentReviewsBody'>
			There are no more current reviews. Try viewing previous reviews.
		</div>
		<?php
	}
	exit();
}
require $_SERVER['DOCUMENT_ROOT'].'vendor/autoload.php';
use Mailgun\Mailgun;

$searchreview = "/* process ad review*/ SELECT r.*, s.site_name FROM `voz`.`reviews` r
				INNER JOIN `voz`.`sites` s on s.site_id = r.site_id
				WHERE r.review_id=".$conn->real_escape_string($_POST['review_id'])." 
				and s.site_id=".$conn->real_escape_string($_POST['site_id'])." 
				LIMIT 1 ";

$searchreviewResult = $conn->query($searchreview) or trigger_error($conn->error." [$searchreview]");
while($row = $searchreviewResult->fetch_assoc()){
	$phone= $row['review_phone'];
	$review_ip= $row['review_ip'];
	$review_name= $row['review_name'];
	$site_name= $row['site_name'];
	$review_email= $row['review_email'];
	$site_type=$row['site_type'];
}


switch($_POST['action']){
	case 'spam': 	
					if(bann_ip($review_ip,2,"Moderator Spam Action")){
				 		$sql = "/* process ad review*/ UPDATE `voz`.`reviews` SET review_status='2' WHERE review_id='".$conn->real_escape_string($_POST['review_id'])."'";
					}else{
						print "/* process ad review*/ fail|Could not ban IP address";
						exit();
					}
				 	break;
	case 'reject': 	$sql = "/* process ad review*/ UPDATE `voz`.`reviews` SET review_status='5' WHERE review_id='".$conn->real_escape_string($_POST['review_id'])."'";
				 	break;
	case 'approve': $sql = "/* process ad review*/ UPDATE `voz`.`reviews` SET review_status='1', review_body='".$conn->real_escape_string($_POST['review_body'])."'  WHERE review_id='".$conn->real_escape_string($_POST['review_id'])."'";
				 	break;
	default: 		break;
}

if($conn->query($sql)){
	if($_POST['action']=='approve'){
		
		# Include the records object
		require_once(INCLUDES.'/records.class.php');
		$records = new recordManager();
		$forced_db = $site_type;
		$records->city_info($site_type);
		$recordsCache = $records->overview($phone,1);
		
		$mgClient = new Mailgun('key-89b611c80c7ed2370249456c93fd2881');
		$domain = $site_name;
		
		# Make the call to the client.
		/*
		$result = $mgClient->sendMessage($domain, array(
			'from'    => ucwords($site_name).' Reviews <reviews-noreply@'.$site_name.'>',
			'to'      => $review_name.' <'.$review_email.'>',
			'subject' => $review_name.', your review has been approved. See it live now.',
			'html'    => $review_name.",<br />".
						  "Your recent review for ".$phone." on ".$site_name." was just moderated and approved by our staff! ".
						  "<a href='http://www.".$site_name."/".$phone."'>See your review live here</a>.<br /> ".
						  "Reviews help others just like you find quality providers and prevent fakes, robs, and other dangerous situations.<br /><br /><br /> ".
						  "We salut you!<br /> ".
						  $site_name."<br />"
		));
		*/
			
	}
	print "success|@|".$_POST['action'].'|@|';
	$review_result = $conn->query("/* process ad review*/ SELECT r.*, s.site_name, s.site_type FROM `voz`.`reviews` r INNER JOIN `voz`.`sites` s on s.site_id = r.site_id  WHERE r.review_status=0 ORDER BY r.review_date ASC LIMIT 1") or trigger_error($conn->error." ");

	while($row = $review_result->fetch_assoc()){
		$rc++;
		$ip_count = $conn->query("/* process ad review*/ SELECT count(*) ipcount FROM `voz`.`reviews` WHERE review_ip='".$row['review_ip']."'")->fetch_object()->ipcount;
		$email_count = $conn->query("/* process ad review*/ SELECT count(*) emailcount FROM `voz`.`reviews` WHERE review_email='".$row['review_email']."'")->fetch_object()->emailcount;

		if(strlen(preg_replace(":@.*$:","",$row['review_email'])) > 2 ){ // If the part of the email before the @ is less than 3 characters then it will match any email that has only 2 characters in a row, example: bb@domain.com sssbbsss@domain.com whateverbb@domain.com, bb123@domain.com bbbb@domain.com 1-bb-2@domain.com
			$partial_email_count = $conn->query("SELECT count(*) emailcount FROM `voz`.`reviews` WHERE review_email LIKE '%".preg_replace('~@.*~','%@%',$row['review_email'])."' AND length(review_email) > 12")->fetch_object()->emailcount;
		}
		$phone_count = $conn->query("/* process ad review*/ SELECT count(*) phonecount FROM `voz`.`reviews` WHERE review_phone='".$row['review_phone']."'")->fetch_object()->phonecount;
		?>
		<div class="panel-heading" id='review-holder'>
			<?php
			print "A review for <strong>".$row['review_phone']."</strong> on <strong>".date('m/j Y', strtotime($row['review_date']))."</strong> by <strong>".$row['review_name']."</strong> is <span style='background-color:#eee;'>waiting approval</span><br />".
				  "<strong>".$row['review_email']."</strong> IP address:<strong>".$row['review_ip']."</strong><br />".
				  "<strong>".strtoupper($row['site_type'])."</strong> <a href='http://".$row['site_name']."/".$row['review_phone']."' target='_blank'>http://".$row['site_name']."/".$row['review_phone']."</a>";
			if($ip_count['ipcount'] >1 || $email_count['emailcount'] >1 || $partial_email_count['emailcount'] >1 || $phone_count['phonecount'] >1){
			?>
				<hr />
				<h6 class="panel-title">
					 <?php 
					 if($ip_count['ipcount'] > 1){ 
							print "<span style='cursor:pointer;' onclick=\"showhide('extraip')\"><strong>Matching IP's found:</strong>".$ip_count['ipcount']."</span>"; 
					 }
					 if($email_count['emailcount'] > 1){ 
							print "<span style='cursor:pointer;' onclick=\"showhide('extraemail')\"><strong>Exact email match found IP:</strong>".$email_count['emailcount']."</span>"; 
					 }
					 if($partial_email_count['emailcount'] > 1){ 
							print "<span style='cursor:pointer;' onclick=\"showhide('partialemail')\"><strong>Partial email match found:</strong>".$partial_email_count['emailcount']."</span>"; 
					 }
					 if($phone_count['phonecount'] > 1){ 
							print "<span style='cursor:pointer;' onclick=\"showhide('extraphone')\"><strong>Other reviews with this same phone:</strong>".$phone_count['phonecount']."</span>"; 
					 }
					?>
				</h6>
			<?php 
			} 
			?>
			<div class='existing_reviews' id='extraphone' style="display:none;">
				<?php
					if($phone_count['phonecount'] > 1){
						$result=$conn->query("/* process ad review*/ SELECT r.*, s.site_name, s.site_type 
												FROM `voz`.`reviews` r INNER JOIN sites s on s.site_id = r.site_id 
												WHERE review_phone='".$row['review_phone']."' 
													  AND review_id != '".$row['review_id']."' 
												LIMIT 7") or trigger_error($conn->error." ");

						while($irow = $result->fetch_assoc()){
							switch($irow['review_status']){
								case 0:$status="<span style='background-color:#eee;'>waiting approval</span>";break;
								case 1:$status="<span style='background-color:#4CAF50;'>already approved</span>";break;
								case 2:$status="<span style='background-color:#F44336;'>already spammed</span>";break;
								case 3:$status="<span style='background-color:#039BE5;'>an admin submited review</span>";break;
								case 4:$status="<span style='background-color:#99C1DC;'>in 'other' status</span>";break;
								case 5:$status="<span style='background-color:#FF5722;'>already rejected</span>";break;
								default: $status="<span style='background-color:#eee;'>in an unknown status</span>";

							}
							print "A review for <strong>".$irow['review_phone']."</strong> on <strong>".date('m/j Y', strtotime($irow['review_date']))."</strong> by <strong>".$irow['review_name']."</strong> is <strong>".$status."</strong>".
								  "<br /><i>".dots(170,preg_replace("/\\\'/","'",$irow['review_body']))."</i><br />".
								  "<strong>".$irow['review_email']."</strong> Ip address:<strong>".$irow['review_ip']."</strong><br />".
								  "<strong>".strtoupper($irow['site_type'])."</strong> <a href='http://".$irow['site_name']."/".$irow['review_phone']."' target='_blank'>http://".$irow['site_name']."/".$irow['review_phone']."</a><hr />";
						}
					}
				?>
			</div>
			<div class='existing_reviews' id='extraip' style="display:none;">
				<?php
					if($ip_count['ipcount'] > 1){
						$result=$conn->query("SELECT * FROM `voz`.`reviews` WHERE review_ip='".$row['review_ip']."' AND review_id != '".$row['review_id']."' AND review_id != '".$row['review_id']."' limit 7") or trigger_error($conn->error." ");
						while($irow = $result->fetch_assoc()){
							switch($irow['review_status']){
								case 0:$status="<span style='background-color:#eee;'>waiting approval</span>";break;
								case 1:$status="<span style='background-color:#4CAF50;'>already approved</span>";break;
								case 2:$status="<span style='background-color:#F44336;'>already spammed</span>";break;
								case 3:$status="<span style='background-color:#039BE5;'>an admin submited review</span>";break;
								case 4:$status="<span style='background-color:#99C1DC;'>in 'other' status</span>";break;
								case 5:$status="<span style='background-color:#FF5722;'>already rejected</span>";break;
								default: $status="<span style='background-color:#eee;'>in an unknown status</span>";

							}
							print "A review for <strong>".$irow['review_phone']."</strong> on <strong>".date('m/j Y', strtotime($irow['review_date']))."</strong> by <strong>".$irow['review_name']."</strong> is <strong>".$status."</strong>".
								  "<br /><i>".dots(170,preg_replace("/\\\'/","'",$irow['review_body']))."</i><br />".
								  "<strong>".strtoupper($irow['site_type'])."</strong> <strong>".$irow['review_email']."</strong> Ip address:<strong>".$irow['review_ip']."</strong><hr />";
						}
					}
				?>
			</div>
			<div class='existing_reviews' id='extraemail' style="display:none;">
				<?php
					if($email_count['emailcount'] > 1){
						$result=$conn->query("SELECT * FROM `voz`.`reviews` WHERE review_email='".$row['review_email']."' AND review_id != '".$row['review_id']."' AND review_id != '".$row['review_id']."' limit 7") or trigger_error($conn->error." ");
						while($irow = $result->fetch_assoc()){
							switch($irow['review_status']){
								case 0:$status="<span style='background-color:#eee;'>waiting approval</span>";break;
								case 1:$status="<span style='background-color:#4CAF50;'>already approved</span>";break;
								case 2:$status="<span style='background-color:#F44336;'>already spammed</span>";break;
								case 3:$status="<span style='background-color:#039BE5;'>an admin submited review</span>";break;
								case 4:$status="<span style='background-color:#99C1DC;'>in 'other' status</span>";break;
								case 5:$status="<span style='background-color:#FF5722;'>already rejected</span>";break;
								default: $status="<span style='background-color:#eee;'>in an unknown status</span>";

							}
							print "A review for <strong>".$irow['review_phone']."</strong> on <strong>".date('m/j Y', strtotime($irow['review_date']))."</strong> by <strong>".$irow['review_name']."</strong> is <strong>".$status."</strong>".
								  "<br /><i>".dots(170,preg_replace("/\\\'/","'",$irow['review_body']))."</i><br />".
								  "<strong>".strtoupper($irow['site_type'])."</strong> <strong>".$irow['review_email']."</strong> Ip address:<strong>".$irow['review_ip']."</strong><hr />";
						}
					}
				?>
			</div>
			<div class='existing_reviews' id='partialemail' style="display:none;">
				<?php
					if($partial_email_count['emailcount'] > 1){
						$result=$conn->query("SELECT * FROM `voz`.`reviews` WHERE review_email LIKE '%".preg_replace('~@.*~','%@%',$row['review_email'])."' AND review_id != '".$row['review_id']."' LIMIT 7") or trigger_error($conn->error." ");
						while($irow = $result->fetch_assoc()){
							switch($irow['review_status']){
								case 0:$status="<span style='background-color:#eee;'>waiting approval</span>";break;
								case 1:$status="<span style='background-color:#4CAF50;'>already approved</span>";break;
								case 2:$status="<span style='background-color:#F44336;'>already spammed</span>";break;
								case 3:$status="<span style='background-color:#039BE5;'>an admin submited review</span>";break;
								case 4:$status="<span style='background-color:#99C1DC;'>in 'other' status</span>";break;
								case 5:$status="<span style='background-color:#FF5722;'>already rejected</span>";break;
								default: $status="<span style='background-color:#eee;'>in an unknown status</span>";

							}
							print "A review for <strong>".$irow['review_phone']."</strong> on <strong>".date('m/j Y', strtotime($irow['review_date']))."</strong> by <strong>".$irow['review_name']."</strong> is <strong>".$status."</strong>".
								  "<br /><i>".dots(170,preg_replace("/\\\'/","'",$irow['review_body']))."</i><br />".
								  "<strong>".strtoupper($irow['site_type'])."</strong> <strong>".$irow['review_email']."</strong> Ip address:<strong>".$irow['review_ip']."</strong><hr />";
						}
					}
				?>
			</div>
		</div>
		<div class="panel-body">
			<?php print "<textarea id='review_body_txt'>".dots(3100,preg_replace("/\\\'/","'",$row['review_body']))."</textarea>"; ?>
		</div>
		<div class="btn-group btn-group-justified">
			<a href="#" class="btn btn-xlg btn-success" onclick="moderate(<?php print $row['review_id'];?>,'approve',<?php print $row['site_id'];?>)"><i class="icon-thumbs-up2 position-left"></i> Approve</a>
			<a href="#" class="btn btn-xlg btn-warning" onclick="moderate(<?php print $row['review_id'];?>,'reject',<?php print $row['site_id'];?>)"><i class="icon-thumbs-down2 position-left"></i> Reject</a>
			<a href="#" class="btn btn-xlg btn-danger" onclick="moderate(<?php print $row['review_id'];?>,'spam',<?php print $row['site_id'];?>)"><i class="icon-bell3 position-left"></i> Spam</a>
		</div>
		<?php 
		break;
	}
	if(empty($rc)){
		print "<div class='panel-body'>There are no reviews at this moment</div>";
	}
	
}else{
	print "fail|$sql";	
}
?>