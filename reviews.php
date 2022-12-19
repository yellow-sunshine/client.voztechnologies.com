<?php
session_start();
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=reviews');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

$PAGE_NAME = 'Reviews';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

?>
<script type="text/javascript" src="/assets/js/core/voz.js"></script>
<script language="javascript">
function alertContents() {
	if(http_request.readyState == 4) {
		if(http_request.status == 200) {
			mresponse = http_request.responseText.split("|@|");
			if(mresponse[0] == 'success'){
				switch(mresponse[1]) {
					case 'approve':
						setTimeout(function(){changebg('#a2e060', 'waitingReviews');}, 1);
						break;
					case 'reject':
						setTimeout(function(){changebg('#f7b72e', 'waitingReviews');}, 1);
						break;
					case 'spam':
						setTimeout(function(){changebg('#fc7474', 'waitingReviews');}, 1);
						break;
					default:
						setTimeout(function(){changebg('#ddd', 'waitingReviews');}, 1);
				}
				
				setTimeout(function(){changebg('#fff', 'waitingReviews');}, 500);
				setTimeout(document.getElementById('waitingReviews').innerHTML = mresponse[2], 500);
				document.getElementById('reviewsWaitingCount').innerHTML = parseInt(document.getElementById('reviewsWaitingCount').innerHTML)-1;
			}else if(mresponse[0] == 'fail'){
				alert("Sorry, the update failed with response:" + http_request.responseText);
			}else if(mresponse[0] == 'changeReview'){
				document.getElementById('recentReviews').innerHTML = mresponse[1];
			}else{
				alert("An unknown response came back: " + http_request.responseText);	
			}
		}
	}
}
window.onresize = function () {
    myChart.draw(0, true);
	reviewStatsChart.draw(0, true);
};
function moderate(review_id,action,site_id){
	var poststr = "action=" + encodeURI(action)
				+ "&review_id=" + encodeURI(review_id)
				+ "&review_body=" + encodeURIComponent(document.getElementById('review_body_txt').value)
				+ "&site_id=" + encodeURI(site_id);
	makePOSTRequest('<?php print BASE_URL; ?>/process_review.php', poststr);
}
function changeReview(direction,review_id){
	document.getElementById('recentReviewsBody').innerHTML = "<div><i class='icon-spinner3 spinner position-left'></i></div>";
	var poststr = "direction=" + encodeURI(direction) + "&review_id=" + encodeURI(review_id);
	makePOSTRequest('<?php print BASE_URL; ?>/process_review.php', poststr);
}
</script>
<?php
if(isset($_GET['review_id'])){
	$searchString = " AND review_id=".mysqli_real_escape_string($connect,$_GET['review_id'])." ";
}
$review_result = mysqli_query($connect,"SELECT r.*, s.site_name, s.site_type FROM `voz`.`reviews` r INNER JOIN sites s on s.site_id = r.site_id WHERE r.review_status=0 ".$searchString." ORDER BY r.review_date ASC LIMIT 1") or die(mysqli_error());
?>
<style>
	#review-holder span{
		padding: 5px;
	}
	.existing_reviews{
		border: 2px solid #959595;
		margin-top:4px;
		padding:4px;
	}
#waitingReviews textarea{
    width:100%;
	border:1px solid #f3f3f3;
	outline:none;
    padding:5px 5px 5px 12px;
    height:12em;
	font-style: italic;
}
#waitingReviews textarea:focus { 
    background-color:#fcfcfc;
    border:1px solid 
    box-shadow: 0 0 5px rgba(81, 203, 238, 1);
    border: 1px solid rgba(81, 203, 238, 1);
}
</style>
<script language="javascript">
function showhide(elementName){
	theelement = document.getElementById(elementName);
	if(theelement.style.display == 'none'){
		theelement.style.display = 'block'
	}else{
		theelement.style.display = 'none'
		
	}
}
</script>
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-white" id="waitingReviews">
            <?php
			while($row=mysqli_fetch_array($review_result))
			{
				$rc++;
				$ad_info = mysqli_fetch_array(mysqli_query($connect, "SELECT l.image1, l.loc_id FROM `".$row['site_type']."`.`links` l INNER JOIN `voz`.`reviews` r on r.review_phone = l.phone WHERE r.review_phone='".$row['review_phone']."' LIMIT 1"));
				$ip_count = mysqli_fetch_array(mysqli_query($connect, "SELECT count(*) ipcount FROM `voz`.`reviews` WHERE review_ip='".$row['review_ip']."'"));
				$email_count = mysqli_fetch_array(mysqli_query($connect, "SELECT count(*) emailcount FROM `voz`.`reviews` WHERE review_email='".$row['review_email']."'"));
				if(strlen(preg_replace(":@.*$:","",$row['review_email'])) > 2 ){ // If the part of the email before the @ is less than 3 characters then it will match any email that has only 2 characters in a row, example: bb@domain.com sssbbsss@domain.com whateverbb@domain.com, bb123@domain.com bbbb@domain.com 1-bb-2@domain.com
					$partial_email_count = mysqli_fetch_array(mysqli_query($connect, "SELECT count(*) emailcount FROM `voz`.`reviews` WHERE review_email LIKE '%".preg_replace('~@.*~','%@%',$row['review_email'])."' AND length(review_email) > 12"));
				}
				$phone_count = mysqli_fetch_array(mysqli_query($connect, "SELECT count(*) phonecount FROM `voz`.`reviews` WHERE review_phone='".$row['review_phone']."'"));
				
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
								$result=mysqli_query($connect, "SELECT r.*, s.site_name, s.site_type 
																FROM `voz`.`reviews` r INNER JOIN sites s on s.site_id = r.site_id 
																WHERE review_phone='".$row['review_phone']."' 
																	  AND review_id != '".$row['review_id']."' 
																LIMIT 7");
								
								while($irow = mysqli_fetch_array($result)){
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
								$result=mysqli_query($connect, "SELECT * FROM `voz`.`reviews` WHERE review_ip='".$row['review_ip']."' AND review_id != '".$row['review_id']."' AND review_id != '".$row['review_id']."' limit 7");
								while($irow = mysqli_fetch_array($result)){
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
								$result=mysqli_query($connect, "SELECT * FROM `voz`.`reviews` WHERE review_email='".$row['review_email']."' AND review_id != '".$row['review_id']."' AND review_id != '".$row['review_id']."' limit 7");
								while($irow = mysqli_fetch_array($result)){
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
								$result=mysqli_query($connect, "SELECT * FROM `voz`.`reviews` WHERE review_email LIKE '%".preg_replace('~@.*~','%@%',$row['review_email'])."' AND review_id != '".$row['review_id']."' LIMIT 7");
								while($irow = mysqli_fetch_array($result)){
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
			?>
        </div>
    

	</div>
    <div class="col-md-6">
        <div class="panel panel-white" id="recentReviews">
			<?php
            $review_result = mysqli_query($connect,"SELECT r.*, s.site_name, s.site_type FROM `voz`.`reviews` r INNER JOIN sites s on s.site_id = r.site_id WHERE r.review_status=1 order by review_date desc LIMIT 1") or die(mysqli_error());
            while($row=mysqli_fetch_array($review_result)){
                $ad_info = mysqli_fetch_array(mysqli_query($connect, "SELECT l.image1, l.loc_id FROM `".$row['site_type']."`.`links` l INNER JOIN `voz`.`reviews` r on r.review_phone = l.phone WHERE r.review_phone='612-203-3142' LIMIT 1"));
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
                	<strong>Date: </strong><?php print $row['review_date'];?>
                    <strong>URL: </strong><a href='http://www.<?php print $row['site_name']; ?>/<?php print $row['review_phone'];?>' target='_blank'>http://<?php print $row['site_name']; ?>/<?php print $row['review_phone'];?></a><br />
					<?php print preg_replace("/\\\'/","'",$row['review_body']); ?>
				</div>
				<?php
            }
            ?>
        </div>
    </div>
</div>


<div class="row">
	<?php
    $siteSQLResult = mysqli_query($connect, "SELECT count(*) AS reviewCount , s.site_name
                                            FROM `voz`.reviews r
                                            INNER JOIN `voz`.sites s WHERE s.site_id = r.site_id
                                            GROUP BY s.site_name");
    while($row=mysqli_fetch_array($siteSQLResult)){
        if($row['reviewCount'] > 0){
            $dataChart .="{'SiteName':'".$row['site_name']."','ReviewCount':'".$row['reviewCount']."',},";
        }
    }
    ?>
    <div class="col-md-6">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Reviews Per Site</h6>
            </div>
            <div class="container-fluid">
                <div id="chartContainer">
                    <script type="text/javascript">		
					var svg = dimple.newSvg("#chartContainer", 490, 500);
					var data = [<?php print $dataChart; ?>];
					var myChart = new dimple.chart(svg, data);
					/* Distance from left, distance from top, width, height */
					myChart.setBounds(10, 0, 280, 280);
					myChart.addMeasureAxis("p", "ReviewCount");
					myChart.addSeries("SiteName", dimple.plot.pie);
					myChart.addLegend(10, 280, 90, 300, "left");
					myChart.draw();
                    </script>
                </div>
            </div>
		</div>
    </div>

<?php
    $reviewSQLResult = mysqli_query($connect, "SELECT DATE(`review_date`) 'Date', COUNT(DISTINCT `review_id`) totalCount 
											  FROM `voz`.`reviews` 
											  GROUP BY DATE(`review_date`) 
											  ORDER BY `review_date` DESC
											  LIMIT 15");
    while($row=mysqli_fetch_array($reviewSQLResult)){
		$placeVal++;
    	$dataBar .="{'placeval':'".$placeVal."','Date':'".date('jS D', strtotime($row["Date"]))."','totalCount':'".$row['totalCount']."',},";
    }
?>
    <div class="col-md-6">
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Reviews Per Day</h6>
            </div>
            <div class="container-fluid">
                <div id="reviewStatsCont">
                    <script type="text/javascript">
                    var svg = dimple.newSvg("#reviewStatsCont", "100%", 535);
                    var data = [<?php print $dataBar; ?>];    
                    var reviewStatsChart = new dimple.chart(svg, data);
                    reviewStatsChart.setMargins(100, 10, 50, 100); 
                    
                    var x = reviewStatsChart.addCategoryAxis("x", "Date");
                    x.addOrderRule("placeval", true);
                    
                    var y = reviewStatsChart.addMeasureAxis("y", "totalCount");
                    y.tickFormat = ",.f";
                    
                    reviewStatsChart.addSeries("Channel", dimple.plot.bar);
                    reviewStatsChart.draw();    
                    </script>
                </div>
            </div>
		</div>
    </div>
</div>


<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>