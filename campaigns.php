<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=sites');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

if(isset($_POST['delete'])){
	if(is_numeric($_POST['delete']) && $_POST['delete'] > 0){
		$sql = "DELETE FROM campaigns WHERE campaign_id = '".mysqli_real_escape_string($connect,$_POST['campaign_id'])."' LIMIT 1";
		if(mysqli_query($connect,$sql)){
			print "success";
		}else{
			print "error";	
		}
	}else{
		print "error";	
	}
	exit();
}else{
	?>
	<script language="javascript">
	function alertContents() {
		if(http_request.readyState == 4) {
			if(http_request.status == 200) {
				if(http_request.responseText.match(/success/)){
					colorFadeMe('site-'+currentSiteID,700,'#ffffff',"#ffc9c9",'#ffffff')
					setTimeout(function(){
									document.getElementById('cont-'+currentCampaignID).style.display = 'none';
							},1200);	
				}else{
					alert('There was an error deleting the site');					
				}
			}
		}
	}
	
	var currentCampaignID = '';
	function deleteCampaign(campaign_id,campaign_name){
		if (confirm('Are you SURE you want to DELETE the site ' + campaign_name +'?')) {
			window.currentCampaignID = campaign_id;
			var poststr = "campaign_id=" + encodeURI(campaign_id)
						  + "&delete=" + encodeURI('1');
			makePOSTRequest('<?php print BASE_URL; ?>/campaigns.php', poststr);
		}
	}
	
	
	</script>	
	<?php	
}

$PAGE_NAME = 'Site Settings';

# Include the header
include_once(INCLUDE_PATH.'/header.php');
?>

        <div class="panel invoice-grid">
            <div class="row">
                <div class="col-sm-12" style="text-align:center; padding:6px;">
                    <ul class="list-inline text-center">
                        <li>
                            <a href="/campaign_add.php" class="btn border-primary-600 text-primary-300 btn-flat btn-rounded btn-icon btn-xs valign-text-bottom"><i class="icon-file-plus2"></i></a>
                        </li>
                        <li class="text-left">
                            <div class="text-semibold"><a href='/campaign_add.php'>Add New Campaign</a></div>
                                <?php
                                $total_campaigns = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as total FROM campaigns WHERE campaign_id!=0"));
                                ?>
                            <div class="text-muted"><?php print $total_campaigns['total']; ?> Campaigns</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

<?php
$campaignSQL = mysqli_query($connect,"
				SELECT *
				FROM 
					campaigns
				WHERE campaign_id !=0
				ORDER BY 
					`network`,`campaign_name`
                LIMIT 400") or die(mysqli_error());

while($campaignrow = mysqli_fetch_array($campaignSQL)){
	$hits_today = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `campaign_id`='".$campaignrow['campaign_id']."' AND date='".date('Y-m-d')."'"));
	$hits_yesterday = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `campaign_id`='".$campaignrow['campaign_id']."' AND date=SUBDATE(CURDATE(),1)"));
	$last_hit = mysqli_fetch_array(mysqli_query($connect,"SELECT `date_last_hit` FROM `out` WHERE campaign_id='".$campaignrow['campaign_id']."' ORDER BY `date_last_hit` DESC LIMIT 1"));	
	$lifetime_hits = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE campaign_id='".$campaignrow['campaign_id']."'"));	
	if(empty($hits_today[0])){$hits_today[0]=0;}
	if(empty($hits_yesterday[0])){$hits_yesterday[0]=0;}
	if(empty($last_hit[0])){$last_hit[0]='Never';}else{$last_hit[0]=date("M jS, Y g:i a",strtotime($last_hit[0]));}
	if(empty($lifetime_hits[0])){$lifetime_hits[0]=0;}
	$rc++;
	if($rc % 2 ==0){
		print "<div class='row'>";
	}
	?>                      
        <div class="col-md-6" id="cont-<?php print $campaignrow['campaign_id']; ?>">
            <div class="panel invoice-grid" id="campaign-<?php print $campaignrow['campaign_id']; ?>">
            	<h6 class="text-semibold no-margin-top" style='background-color:#ECEDF0; padding:4px 4px 4px 10px; border-top:5px solid #DFE1E6; width:100%; border-bottom:1px solid #DFE1E6;'>
                    <div style='text-align:left; width:65%; display:inline-block;'><?php print $campaignrow['campaign_name']; ?></div>
                    <div style='display:inline-block; float:right;'>
						<?php print $campaignrow['campaign_id']; ?>&nbsp;&nbsp;
                    	<a href='/campaign_edit.php?id=<?php print $campaignrow['campaign_id']; ?>'><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href='#'><i class="fa fa-trash" onclick="deleteCampaign(<?php print $campaignrow['campaign_id']; ?>,'<?php print $campaignrow['campaign_name']; ?>')"></i></a>
                    </div>
                </h6>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <ul class="list list-unstyled">
                                <li><strong>Network:</strong> &nbsp;<a href='<?php print $campaignrow['url_admin']; ?>' target='_blank'><?php print $campaignrow['network']; ?></a></li>
                                <li><strong>Hits Today:</strong> <?php print $hits_today[0]; ?></li>
                                <li><strong>Hits Yesterday:</strong> <?php print $hits_yesterday[0]; ?></li>
								<li><strong>Lifetime Hits:</strong> <?php print $lifetime_hits[0]; ?></li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <ul class="list list-unstyled">
                                <li><strong>Date of Last Hit:</strong> &nbsp;<?php print $last_hit[0]; ?></li>
                                <li><a href='http://sextravelersguide.com/go.php?s=0&c=<?php print $campaignrow['campaign_id']; ?>' target="_blank">Hit Through Voz</a></li>
                                <li><a href='<?php print $campaignrow['url']; ?>' target="_blank">Direct Hit</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    if($rc % 2 ==0){
        print "</div>";
    }
}
?>



			
    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>