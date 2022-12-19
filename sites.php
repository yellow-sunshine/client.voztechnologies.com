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
		$sql = "DELETE FROM sites WHERE site_id = '".mysqli_real_escape_string($connect,$_POST['site_id'])."' LIMIT 1";
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
									document.getElementById('cont-'+currentSiteID).style.display = 'none';
							},1200);	
				}else{
					alert('There was an error deleting the site');					
				}
			}
		}
	}
	
	var currentSiteID = '';
	function deleteSite(site_id,site_name){
		if (confirm('Are you SURE you want to DELETE the site ' + site_name +'?')) {
			window.currentSiteID = site_id;
			var poststr = "site_id=" + encodeURI(site_id)
						  + "&delete=" + encodeURI('1');
			makePOSTRequest('<?php print BASE_URL; ?>/sites.php', poststr);
		}
	}
	
	
	</script>	
	<?php	
}



# Include an array containing all data about each city
include_once(INCLUDE_PATH.'/city_info.php');

$PAGE_NAME = 'Site Settings';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

?>
        <div class="panel invoice-grid">
            <div class="row">
                <div class="col-sm-12" style="text-align:center; padding:6px;">
                    <ul class="list-inline text-center">
                        <li>
                            <a href="/site_add.php" class="btn border-primary-600 text-primary-300 btn-flat btn-rounded btn-icon btn-xs valign-text-bottom"><i class="icon-file-plus2"></i></a>
                        </li>
                        <li class="text-left">
                            <div class="text-semibold"><a href='/site_add.php'>Add New Site</a></div>
                                <?php
                                $total_sites = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as total FROM sites"));
                                ?>
                            <div class="text-muted"><?php print $total_sites['total']; ?> Sites</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
<?php
$sitesSQL = mysqli_query($connect,"
				SELECT DISTINCT
					s.*, 
					c.campaign_name as 'campaign_name_a', c.url as 'url_a', 
					c2.campaign_name as 'campaign_name_b', c2.url as 'url_b',
					c3.campaign_name as 'campaign_name_c', c3.url as 'url_c',
					c4.campaign_name as 'campaign_name_d', c4.url as 'url_d'
				FROM 
					sites s
				INNER JOIN 
					campaigns c ON s.campaign_id_a=c.campaign_id
				INNER JOIN 
					campaigns c2 ON s.campaign_id_b=c2.campaign_id
				INNER JOIN 
					campaigns c3 ON s.campaign_id_reviews=c3.campaign_id
				INNER JOIN 
					campaigns c4 ON s.campaign_id_reviews_phone=c4.campaign_id
				WHERE 
					site_id NOT IN(100,101,0) 
				ORDER BY 
					site_name") or die(mysqli_error());

while($sitesrow = mysqli_fetch_array($sitesSQL)){
	$hits_today = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `site_id`='".$sitesrow['site_id']."' AND `date`='".date('Y-m-d')."' GROUP BY `site_id`"));
	$hits_yesterday = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `site_id`='".$sitesrow['site_id']."' AND `date`=SUBDATE(CURDATE(),1) GROUP BY `site_id`"));
	if(empty($hits_today[0])){$hits_today[0]=0;}
	if(empty($hits_yesterday[0])){$hits_yesterday[0]=0;}
	
	$lifetime_hits = mysqli_fetch_array(mysqli_query($connect,"SELECT sum(`hits`) as 'total' FROM `out` WHERE `site_id`='".$sitesrow['site_id']."' GROUP BY `site_id`"));	
	if(empty($lifetime_hits[0])){$lifetime_hits[0]=0;}
	$rc++;
	if($rc % 2 ==0){
		print "<div class='row'>";
	}
	?>                      
        <div class="col-md-6" id="cont-<?php print $sitesrow['site_id']; ?>">
            <div class="panel invoice-grid" id="site-<?php print $sitesrow['site_id']; ?>">
            	<h6 class="text-semibold no-margin-top" style='background-color:#ECEDF0; padding:4px 4px 4px 10px; border-top:5px solid #DFE1E6; width:100%; border-bottom:1px solid #DFE1E6;'>
                    <div style='text-align:left; width:65%; display:inline-block;'><?php print $sitesrow['site_name']; ?></div>
                    <div style='display:inline-block; float:right;'>
						<?php print $sitesrow['site_id']; ?>&nbsp;&nbsp;
                    	<a href='/site_edit.php?id=<?php print $sitesrow['site_id']; ?>'><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href='#'><i class="fa fa-trash" onclick="deleteSite(<?php print $sitesrow['site_id']; ?>,'<?php print $sitesrow['site_name']; ?>')"></i></a>
                    </div>
                </h6>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <ul class="list list-unstyled">
                                <li><strong>Site Name:</strong> &nbsp;<a href='http://<?php print $sitesrow['site_name']; ?>' target='_blank'><?php print $sitesrow['display_name']; ?></a></li>
                                <li><strong>Hits Today</strong>: <?php print $hits_today[0]; ?></li>
                                <li><strong>Hits Yesterday:</strong> <?php print $hits_yesterday[0]; ?></li>
                                <li><strong>Lifetime Hits:</strong> <?php print $lifetime_hits[0]; ?></li>
                            </ul>
                        </div>
                        <div class="col-sm-6">
                            <ul class="list list-unstyled">
                                <li><strong>Campaign A:</strong> &nbsp;<a href='<?php print $sitesrow['url_a']; ?>'><?php print $sitesrow['campaign_name_a']; ?></a></li>
                                <li><strong>Campaign B:</strong> &nbsp;<a href='<?php print $sitesrow['url_b']; ?>'><?php print $sitesrow['campaign_name_b']; ?></a></li>
                                <li><strong>Review No Phone:</strong> &nbsp;<a href='<?php print $sitesrow['url_c']; ?>'><?php print $sitesrow['campaign_name_c']; ?></a></li>
                                <li><strong>Review w/ Phone:</strong> &nbsp;<a href='<?php print $sitesrow['url_d']; ?>'><?php print $sitesrow['campaign_name_d']; ?></a></li>
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