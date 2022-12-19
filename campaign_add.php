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
		|| empty($campaign_id)
		){
		# One of the conditions was not met and therefor this record should not be saved
		print "error-1";
	}else{
		$updateSQL="INSERT INTO campaigns SET
							campaign_id='".$campaign_id."',
							campaign_name='".$campaign_name."',
							network='".$network."',
							url_admin='".$url_admin."',
							url='".$url."'";
		if(mysqli_query($connect, $updateSQL)){
			print "success";
		}else{
			print "error-2";	
		}
	}
	exit();
}



$PAGE_NAME = 'Create Campaign';

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
							colorError('updateCampaignContainer');
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
                    makePOSTRequest('<?php print BASE_URL; ?>/campaign_add.php', poststr); 
                }
            }
        </script>
        <form method="post" action="">
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <div class="panel registration-form">
                        <div class="panel-body" id='updateCampaignContainer'>
                            <div class="text-center">
                                <div class="icon-object border-primary-600 text-primary-300"><i class="icon-file-plus2"></i></div>
                                <h5 class="content-group-lg">Add Campaign</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group has-feedback">
                                        Campaign Name: 
                                        <input id="campaign_name" name="campaign_name" type="text" class="form-control" placeholder="Discriptive name of the campaign" value="<?php print $_POST['campaign_name']; ?>">
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
                                        <input id="original_campaign_id" name='original_campaign_id' type="hidden" value='<?php print $_POST['campaign_id']; ?>'>
                                        <select id="campaign_id" name='campaign_id' class="form-control">
                                            <?php 
                                            $i=1;
                                            for(;;){
                                                if($i>100){break;}
                                                if($i==$_POST['campaign_id']){
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
                                        Network: <input id="network" name="network" type="text" class="form-control" placeholder="Name of the ad network" value="<?php print $_POST['network']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group has-feedback">
                                        Campaign URL: <input id="url" name="url" type="text" class="form-control" placeholder="The full URL where traffic will be sent" value="<?php print $_POST['url']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group has-feedback">
                                        Admin URL: <input id="url_admin" name="url_admin" type="text" class="form-control" placeholder="http URL to network's administration for this campaign" value="<?php print $_POST['url_admin']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <a href="/campaigns.php"<i class="icon-arrow-left13 position-left"></i> Back to campaign list</a>
                                <span class="btn bg-primary-400 btn-labeled btn-labeled-right ml-10" onclick="updateCampaign();"><b><i class="icon-pencil6"></i></b> Create Campaign</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>		
    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>