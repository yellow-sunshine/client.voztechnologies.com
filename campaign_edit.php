<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=campaigns');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

# Include an array containing all data about each city
include_once(INCLUDE_PATH.'/city_info.php');

if(isset($_POST['campaign_name'])){
	// Make variables from post and clean vars for db
	foreach($_POST as $key=>$value){ ${$key} = mysqli_real_escape_string($connect,trim($value)); }

	if(strlen($campaign_name) < 3
		|| strlen($network) < 3
		|| strlen($url_admin) < 7
		|| strlen($url) < 7
		|| !is_numeric($campaign_id)
		|| !is_numeric($original_campaign_id)
		|| empty($campaign_id)
		|| empty($original_campaign_id)
		){
		# One of the conditions was not met and therefor this record should not be saved
		print "error";
	}else{
		$updateSQL="UPDATE campaigns SET
							campaign_id='".$campaign_id."',
							campaign_name='".$campaign_name."',
							network='".$network."',
							url_admin='".$url_admin."',
							url='".$url."'
							WHERE 
							campaign_id='".$original_campaign_id."'
							LIMIT 1";
		if(mysqli_query($connect, $updateSQL)){
			print "success";
		}else{
			print "error";	
		}
	}
	exit();
}



$PAGE_NAME = 'Edit Campaign';

# Include the header
$dontShowSide=1;
include_once(INCLUDE_PATH.'/header.php');
?>
<script type="application/javascript">

	function alertContents() {
		if(http_request.readyState == 4) {
			if(http_request.status == 200) {
				var success = http_request.responseText.match(/success/);
				if(success){
					colorSuccess('updateCampaignContainer');
				}else{
					colorFail('updateCampaignContainer');
				}
			}
		}
	}

	function correctPerecentb(){
		document.getElementById('b_percent').value = 100 - document.getElementById('a_percent').value
	}
	function correctPerecenta(){
		document.getElementById('a_percent').value = 100 - document.getElementById('b_percent').value
	}
	function updateCampaign(){
		var errors = 0;
	 	var campaign_name = document.getElementById('campaign_name');
		var url_admin = document.getElementById('url_admin');
		var campaign_id = document.getElementById('campaign_id');
		var url = document.getElementById('url');
		var network = document.getElementById('network');

		var ua = url_admin.value
		if(ua.length < 7){
			colorError('url_admin');
			errors=1;
		}

		var href = url.value
		if(href.length < 7){
			colorError('url');
			errors=1;
		}

		var net = network.value;
		if(net.length < 3){
			colorError('network');
			errors=1;
		}
		
		var cn = campaign_name.value;
		if(cn.length < 3){
			colorError('campaign_name');
			errors=1;
		}
		
		if(!isNumber(campaign_id.value)){
			colorError('campaign_id');
			errors=1;
		}
		
		
		if(errors < 1){
			var poststr = "campaign_id=" + encodeURI(campaign_id.value)
						  + "&original_campaign_id=" + encodeURI(document.getElementById('original_campaign_id').value)
						  + "&campaign_name=" + encodeURIComponent(campaign_name.value)
						  + "&network=" + encodeURIComponent(network.value)
						  + "&url=" + encodeURIComponent(url.value)
						  + "&url_admin=" + encodeURIComponent(url_admin.value)
			makePOSTRequest('<?php print BASE_URL; ?>/campaign_edit.php', poststr); 
		}
	}
</script>
<?php
$campaignsSQL = mysqli_query($connect,"
				SELECT *
				FROM 
					campaigns 
				WHERE 
					campaign_id='".mysqli_real_escape_string($connect,$_GET['id'])."' 
				ORDER BY 
					campaign_name
				LIMIT 1") or die(mysqli_error());

while($campaignsrow = mysqli_fetch_array($campaignsSQL)){
?>
<form method="post" action="">
	<div class="row">
		<div class="col-lg-6 col-lg-offset-3">
			<div class="panel registration-form">
				<div class="panel-body" id='updateCampaignContainer'>
					<div class="text-center">
						<div class="icon-object border-success text-success"><i class="icon-pencil6"></i></div>
						<h5 class="content-group-lg"><?php print $campaignsrow['campaign_name']; ?>
                        </h5>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group has-feedback">
								Campaign Name: <input id="campaign_name" name="campaign_name" type="text" class="form-control" placeholder="Easy Sex" value="<?php print $campaignsrow['campaign_name']; ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group has-feedback">
								Campaign ID:
                                <?php
								$campaign_id_list = mysqli_query($connect,"SELECT campaign_id FROM `voz`.`campaigns` LIMIT 300");
								$campaign_id_listA = array();
								while($idrow=mysqli_fetch_array($campaign_id_list)){
									$campaign_id_listA[$idrow['campaign_id']]=$idrow['campaign_id'];
								}
								?>
                                <input id="original_campaign_id" name='original_campaign_id' type="hidden" value='<?php print $campaignsrow['campaign_id']; ?>'>
                                <select id="campaign_id" name='campaign_id' class="form-control">
									<?php 
									$i=1;
									for(;;){
										if($i>100){break;}
										if($i==$campaignsrow['campaign_id']){
											print "<option value='$i' selected>$i</option>\n";
										}else{
											if(!in_array($i,$campaign_id_listA)){
												print "<option value='$i'>$i</option>\n";
											}
										}
										$i++;
                                    }
                                    ?>
                                </select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group has-feedback">
								Network: <input id="network" name="network" type="text" class="form-control" placeholder="The Dating Network" value="<?php print $campaignsrow['network']; ?>">
							</div>
						</div>
					</div>
                    
					<div class="row">
						<div class="col-md-12">
							<div class="form-group has-feedback">
								Campaign URL: <input id="url" name="url" type="text" class="form-control" placeholder="http://adsite.com?affid=23423&c=3e4" value="<?php print $campaignsrow['url']; ?>">
							</div>
						</div>
					</div>
                    
					<div class="row">
						<div class="col-md-12">
							<div class="form-group has-feedback">
								Admin URL: <input id="url_admin" name="url_admin" type="text" class="form-control" placeholder="http://adsite.com/login.php" value="<?php print $campaignsrow['url_admin']; ?>">
							</div>
						</div>
					</div>
					<div class="text-right">
						<a href="/campaigns.php"<i class="icon-arrow-left13 position-left"></i> Back to campaign list</a>
						<span class="btn bg-teal-400 btn-labeled btn-labeled-right ml-10" onclick="updateCampaign();"><b><i class="icon-pencil6"></i></b> Update Campaign</span>
					</div>
                    
					<script language="javascript">

                        
                        $(document).ready(function() {
	
							var table = $('#2weeks').DataTable( {
								responsive: false,
								paging: false,
								fixedHeader: true,
								searching:false,
								info:false,
								order: [[ 0, "asc" ]],
								fixedColumns: {
									heightMatch: 'none'
								},
								columnDefs: [
									{ targets: [0, 2,4], visible: true},
								]
							} );
							$('#2weeks tbody')
							.on( 'mouseenter', 'td', function () {
								var colIdx = table.cell(this).index().column;
								$( table.cells().nodes() ).removeClass( 'highlight' );
								$( table.column( colIdx ).nodes() ).addClass( 'highlight' );
							} );												
						} );
                    </script> 
                    

                    <table id="2weeks" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" >Site</th>
                                <th class="text-center" >Today</th>
                                <th class="text-center" >Yesterday</th>
                                <th class="text-center" >Total</th>
                                <th class="text-center" >Last Hit</th> 
                            </tr>
                        </thead>
                        <tbody>
                                <?php
                                $id = mysqli_real_escape_string($connect,$_GET['id']);
                                $campaign_sitesDS = mysqli_query($connect,"SELECT * 
                                                                           FROM sites 
                                                                           WHERE 
                                                                                campaign_id_a='".$id."' 
                                                                                OR campaign_id_b='".$id."' 
                                                                                OR campaign_id_reviews='".$id."'
                                                                                OR campaign_id_reviews_phone='".$id."'");
                                
                                while($csr = mysqli_fetch_array($campaign_sitesDS)){
                                    $site_id = $csr['site_id'];
                                    $site_name = $csr['site_name'];
                                    
                                    $hits_today = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `site_id`='".$csr['site_id']."' AND campaign_id='".$id."' AND `date`='".date('Y-m-d')."' GROUP BY `site_id`"));
                                    
                                    $hits_yesterday = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `site_id`='".$csr['site_id']."' AND campaign_id='".$id."' AND `date`=SUBDATE(CURDATE(),1) GROUP BY `site_id`"));
                                    
                                    $last_hit = mysqli_fetch_array(mysqli_query($connect,"SELECT `date_last_hit` FROM `out` WHERE `site_id`='".$csr['site_id']."' AND campaign_id='".$id."' ORDER BY `date_last_hit` DESC LIMIT 1"));	

                                    if(empty($hits_today[0])){$hits_today[0]=0;}
                                    if(empty($hits_yesterday[0])){$hits_yesterday[0]=0;}
                                    
                                    $lifetime_hits = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `site_id`='".$csr['site_id']."' AND campaign_id='".$id."' GROUP BY `site_id`"));
                                    if(empty($lifetime_hits[0])){continue;}
									$t_lifetime=$lifetime_hits[0]+$t_lifetime;
									$t_hits_yesterday=$hits_yesterday[0]+$t_hits_yesterday;
									$t_hits_today=$hits_today[0]+$t_hits_today;
                                    ?>
                                    <tr>
                                    <td><?php print "<a href='/site_edit.php?id=".$csr['site_id']."'>".$csr['site_name']."</a>"; ?></td>
                                    <td><?php print $hits_today[0]; ?></td>
                                    <td><?php print $hits_yesterday[0]; ?></td>
                                    <td><?php print $lifetime_hits[0]; ?></td>
                                    <td><?php print date("Y-m-d",strtotime($last_hit[0])); ?></td>
                                    </tr>
                                    <?php 
                                }
                                ?>
                                    <tr>
                                    <td><strong>TOTAL</strong></td>
                                    <td><?php print $t_hits_today; ?></td>
                                    <td><?php print $t_hits_yesterday; ?></td>
                                    <td><?php print $t_lifetime; ?></td>
                                    <td></td>
                                    </tr>
                        </tbody>
                     </table>

				</div>
			</div>
		</div>
	</div>
</form>
<?php
}
?>
			
    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>