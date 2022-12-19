<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=records');
	exit();
}

if($_GET['forced_db']){
	$forced_db=preg_replace("/[^A-Za-z0-9 ]/", '', $_GET['forced_db']);
}elseif($_SESSION['user']['selected_db']){
	$forced_db=$_SESSION['user']['selected_db'];
}
$_SESSION['user']['selected_db'] = $forced_db;
switch($_SESSION['user']['selected_db']){
	case 'em':$a_style='.dbAcolor a,.dbAcolor label,.delLink{color:#0393D9 !important;}';$raDomain='www';break;
	case 'bs':$a_style='.dbAcolor a,.dbAcolor label,.delLink{color:#F44336 !important;}';$raDomain='gay';break;
	case 'mt':$a_style='.dbAcolor a,.dbAcolor label,.delLink{color:#409843 !important;}';$raDomain='massage';break;
	case 'ts':$a_style='.dbAcolor a,.dbAcolor label,.delLink{color:#ff8000 !important;}';$raDomain='ts';break;
	default:break;	
}

if(empty($_SESSION['user']['selected_db'])){
	header('Location://'.ADMIN_DOMAIN.'/records.php');
	exit();	
}

if($_GET['phone']){
	$phone=$_GET['phone'];
}else{
	header('Location://'.ADMIN_DOMAIN.'/records.php');
	exit();	
}

require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz', $forced_db);


# to update cache, pass the get or post variable updatecache
($_GET['updatecache'])?$updatingCache=1:$updatingCache=0;

# Include site functions
require_once('/var/www/shared_files/functions.v2.php');

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();
$records->city_info($forced_db);
$recordsCache = $records->overview($phone,$updatingCache);

# Include the header
include_once(INCLUDE_PATH.'/header.php');
?>
<script language="javascript">
function alertContents() {
	if(http_request.readyState == 4) {
		if(http_request.status == 200) {
			theResponse = http_request.responseText.split("|@|");
			if(theResponse[0] == 'success'){
				switch(theResponse[1]) {
					case 'phoneDeleted':
						setTimeout(function(){changebg('#ff1a1a', theResponse[2]);$('#'+theResponse[2]).fadeTo(1200,0.5);}, 1);
						setTimeout(function(){$('#'+theResponse[2]).fadeOut();}, 600);
						setTimeout(function(){window.location.replace("<?php print BASE_URL; ?>/records.php?forced_db=<?php print $_SESSION['user']['selected_db'];?>");}, 600);
						break;
					case 'postDeleted':
						setTimeout(function(){changebg('#ffe6e6', theResponse[2]);$('#'+theResponse[2]).fadeTo(1200,0.5);}, 1);
						setTimeout(function(){$('#'+theResponse[2]).fadeOut();}, 600);
						break;
					case 'spam':
						setTimeout(function(){changebg('#fc7474', 'waitingReviews');}, 1);
						break;
					default:
						setTimeout(function(){changebg('#ddd', 'waitingReviews');}, 1);
				}
				
				setTimeout(function(){changebg('#fff', 'waitingReviews');}, 500);
				setTimeout(document.getElementById('waitingReviews').innerHTML = theResponse[2], 500);
				document.getElementById('reviewsWaitingCount').innerHTML = parseInt(document.getElementById('reviewsWaitingCount').innerHTML)-1;
			}else if(theResponse[0] == 'fail'){
				alert("Sorry, the update failed with response:" + http_request.responseText);
			}else{
				alert("An unknown response came back: " + http_request.responseText);	
			}
		}
	}
}
         
function deletePhone(phoneNum,post_id){
    var r = confirm("Are you sure you want to delete everything for this phone number?");
    if (r == true) {
		var poststr = "phone=" + encodeURI(phoneNum) + 
					  "&post_id=" + encodeURI(post_id) +
					  "&forced_db=" + encodeURI('<?php print $_SESSION['user']['selected_db']; ?>');
		makePOSTRequest('<?php print BASE_URL; ?>/deletePhone.php', poststr);
	}
}
function deletePost(phoneNum,post_id){
    var r = confirm("Are you sure you want to delete this post and it's images?");
    if (r == true) {
		var poststr = "phone=" + encodeURI(phoneNum) + 
					  "&post_id=" + encodeURI(post_id) +
					  "&forced_db=" + encodeURI('<?php print $_SESSION['user']['selected_db']; ?>');
		makePOSTRequest('<?php print BASE_URL; ?>/deletePost.php', poststr);
	}
}
</script>

<div class="panel invoice-grid" id='1111111111111111111111'>
	<div class="row">
		<div class="col-sm-12 col-md-6 col-lg-8 dbAcolor" style="text-align:center; padding:6px;">
		<h1> <?php print $recordsCache['summary']['phone']." Overview on ".strtoupper($_SESSION['user']['selected_db']); ?><h1>
			<ul class="list-inline text-center">
				<li>
					Escort Name: <?php (!empty($recordsCache['summary']['escort_name']))?print $recordsCache['summary']['escort_name']: print "Unknown";?> 
					&nbsp;&nbsp;|&nbsp;&nbsp;<?php print $recordsCache['summary']['record_count_with_images']; ?> Postings
					&nbsp;&nbsp;|&nbsp;&nbsp;<a href='/overview.php?force_db=<?php print $_SESSION['user']['selected_db']; ?>&phone=<?php print $recordsCache['summary']['phone']; ?>&updatecache=1'>Update Cache</a>
				</li><br />
				<li>
					<?php 										
					if(!$recordsCache['summary']['previous_city_loc_id']){
						$previous_city_display="<a href='/listings.php?db=".$_SESSION['user']['selected_db']."&loc_id=".$recordsCache['summary']['current_city_loc_id']."'>".$recordsCache['summary']['current_city_match_city_name']."</a>";
					}else{
						$previous_city_display="<a href='/listings.php?db=".$_SESSION['user']['selected_db']."&loc_id=".$recordsCache['summary']['previous_city_loc_id']."'>".$recordsCache['summary']['previous_city_match_city_name']."</a>";
					}
					?>
				</li><br />
				<li class="text-muted">
					Current Location: 
					<a href='<?php print "/listings.php?db=".$_SESSION['user']['selected_db']."&loc_id=".$recordsCache['summary']['current_city_loc_id']; ?>'>
						<?php print $recordsCache['summary']['current_city_match_city_name']; ?>
					</a>
				</li>
				<li class="text-muted">
					<strong>Previous Location:</strong> 
					<?php print $previous_city_display; ?>
				</li><br/>
				<li class="text-muted">
					<strong>Last Post:</strong> 
					<?php print date('D M jS, Y @ H:i s\s\e\c',strtotime($recordsCache['posts'][0]['date_posted'])); ?>
				</li><br/>
				<li class="text-muted">
					<strong>First Post:</strong>
					<?php print date('D M jS, Y @ H:i s\s\e\c',strtotime($recordsCache['summary']['first_record_date'])); ?>
				</li><br />				
				<li class="text-muted">
					<strong>Points:</strong> <?php print $recordsCache['summary']['risk_assessment']['summary']['points']; ?> 
					<strong>Views:</strong> <?php print $recordsCache['summary']['total_page_views']; ?> 
					<strong>Ages:</strong> <?php print $recordsCache['summary']['ages_used']; ?> yrs
				</li><br />
				<li class="text-muted">
					<strong>Memcached at</strong> <?php print date('g:ia',strtotime($recordsCache['summary']['date_cached'])); ?>, expires at <?php print date('g:ia',strtotime($recordsCache['summary']['date_cached_expire'])); ?>
				</li><br/>
				<li class="text-muted">
					<span class='delLink' onclick="deletePhone('<?php print $recordsCache['summary']['phone']; ?>','1111111111111111111111');">Delete this phone</span> 			   
				</li><br/>
			</ul>
		</div>
		<div class="col-sm-12 col-md-6 col-lg-4 dbAcolor" style="text-align:left; padding:6px;">
			<a href='http://<?php print $raDomain;?>.escortresearch.com/<?php print $recordsCache['summary']['phone'];?>' target='_blank'>
				<img src='http://<?php print $raDomain;?>.escortresearch.com/rai/rai.php?<?php print $recordsCache['summary']['phone'];?>' alt='EscortResearch.com Risk Assessment' />
			</a>
		</div>
	</div>
</div>
<style>
	<?php print $a_style; ?>
	
	.smallfont{
		font-size:.8em;
	}
	.thumbnail{
		text-align:center;
		height:225px;	
	}
	.thumbnail img{
		max-height:150px;
	}
	
	.listingsHolder{
		width:146px; 
		margin:8px 1px 8px 1px;
		float:left;
	}
	.galleryHolder{
		height:275px;

	}
	.pgCont {
		margin: 0 20px 0 20px;
		<?php print $a_style; ?>
	}
	.delLink{
		cursor:pointer;
	}
	.riskrow{
		border:1px solid #ccc;
		border-bottom:none;
		padding:4px 0 4px 0;
	}

	.riskrow:last-child{
		border-bottom:1px solid #ccc;
	}
	.nodeco{
		text-transform: capitalize;
	}
	.big-icon {
		font-size: 32px;
	}
</style>
<script>
$(document).ready(function() {

    var table = $('#postList').DataTable( {
		responsive: true,
		paging: true,
		fixedHeader: true,
		searching:true,
		info:false,
		order: [[ 0, "asc" ]],
		fixedColumns: {
			heightMatch: 'none'
		},
        columnDefs: [
            { responsivePriority: 1, targets: 1 },
            { responsivePriority: 2, targets: 2 },
			{ responsivePriority: 3, targets: 7 },
			{ responsivePriority: 4, targets: 5 }
        ]
    } );
    $('#postList tbody')
	.on( 'mouseenter', 'td', function () {
		var colIdx = table.cell(this).index().column;
		$( table.cells().nodes() ).removeClass( 'highlight' );
		$( table.column( colIdx ).nodes() ).addClass( 'highlight' );
	} );

} );
</script>

<div class="panel invoice-grid">
	<div class="row">
		<div class="col">
			<div class='pgCont dbAcolor'>
			<table id="postList" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
				<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">Post ID</th>
					<th class="text-center">BP ID</th>
					<th class="text-center">Posted</th>
					<th class="text-center">Updated</th>
					<th class="text-center">Added</th>
					<th class="text-center">Location</th>
					<?php if(in_array($site_type, array('em','bs','ts'))){ ?><th class="text-center">Age</th><?php } ?>
					<th class="text-center">Title</th>
					<th class="text-center">Images</th>
					<th class="text-center">Last Viewed</th>
					<th class="text-center">Delete</th>
				</tr>
				</thead>
				<tbody>
					<?php 
					foreach($recordsCache['posts'] as $postCount=>$postData){
						foreach($postData as $key=>$var){$post[$key]=$var;}
						$pc++;
						$postCityInfo  = loc2city('loc_id', $post['loc_id'], $city_info);
						// Get the folder of the backpage url where this posting is
						switch($_SESSION['user']['selected_db']){
							case 'em':$bp_folder='WomenSeekMen';break;
							case 'bs':$bp_folder='MenSeekWomen';break;
							case 'mt':$bp_folder='TherapeuticMassage';break;
							case 'ts':$bp_folder='Transgender';break;
							default:break;	
						}
						$bp_url = preg_replace('@backpage\.com.*@','',$postCityInfo['url_backpage_adult']).'backpage.com/'.$bp_folder.'/title/'.$post['bp_id'];
							
						if(!empty($post['image7'])){$image_count=7;
						}elseif(!empty($post['image6'])){$image_count=6;
						}elseif(!empty($post['image5'])){$image_count=5;
						}elseif(!empty($post['image4'])){$image_count=4;
						}elseif(!empty($post['image3'])){$image_count=3;
						}elseif(!empty($post['image2'])){$image_count=2;
						}elseif(!empty($post['image1'])){$image_count=1;}
						?>
						<tr id='<?php print $post['post_id']; ?>'>
							<td><?php print $pc; ?></td>
							<td><a href="/post.php?post_id=<?php print $post['post_id']; ?>&phone=<?php print $recordsCache['summary']['phone']; ?>&force_db=<?php print $_SESSION['user']['selected_db']; ?>"><?php print $post['post_id']; ?></a></td>
							<td><a href='<?php print $bp_url; ?>' target='_blank'><?php print $post['bp_id']; ?></a></td>
							<td><?php print date("`y n-j @ H:i",strtotime($post['date_posted'])); ?></td>
							<td><?php print date("`y n-j @ H:i",strtotime($post['date_updated'])); ?></td>
							<td><?php print date("`y n-j @ H:i",strtotime($post['date_added'])); ?></td>
							<td><?php print $post['match_city_name']; ?></td>
							<?php if(in_array($site_type, array('em','bs','ts'))){ ?><td><?php print $post['age']; ?></td><?php } ?>
							<td><a href="/post.php?post_id=<?php print $post['post_id']; ?>&phone=<?php print $recordsCache['summary']['phone']; ?>&force_db=<?php print $_SESSION['user']['selected_db']; ?>"><?php print dots(70,$post['title']); ?></a></td>
							<td><?php print $image_count; ?></td>
							<td><?php print date("`y n-j @ H:i",strtotime($post['date_last_viewed'])); ?></td>
							<td><span class='delLink' onclick="deletePost('<?php print $recordsCache['summary']['phone']; ?>','<?php print $post['post_id']; ?>');">Del Post</span></td>
						
						</tr>
						<?php
						if($pc==2000){break;}
					}
					?>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>


<div class="panel invoice-grid">
	<div class="row">
		<div class="col">
			<div class='pgCont dbAcolor'>
			<?php if(!empty($recordsCache['summary']['reviews'])){?>
			<table id="postList" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
				<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">Review ID</th>
					<th class="text-center">Name</th>
					<th class="text-center">Email</th>
					<th class="text-center">Review</th>
					<th class="text-center">Date</th>
					<th class="text-center">IP</th>
					<th class="text-center">Site</th>
				</tr>
				</thead>
				<tbody>
					<?php 
					foreach($recordsCache['summary']['reviews'] as $review){
						$rc++;
						?>
						<tr>
							<td><?php print $rc; ?></td>
							<td><?php print $review['id']; ?></a></td>
							<td><?php print $review['name']; ?></td>
							<td><?php print $review['email']; ?></td>
							<td style='max-width:250px;white-space: normal;'><?php print $review['body']; ?></td>
							<td><?php print date('n-j-y @ H:i',strtotime($review['date_unformated'])); ?></td>
							<td><?php print $review['ip']; ?></td>
							<td><?php print $review['site_name']; ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php 
			}else{
				print "<h1>No Reviews Found</h1>";
			}
			?>
			</div>
		</div>
	</div>
</div>


<div class="panel invoice-grid">
	<div class="row">
		<div class="col">
			<div class='pgCont dbAcolor'>
				<?php
				foreach($recordsCache['summary']['images'] as $image){
					$i++;
					$large_image_size = filesize($_SERVER['DOCUMENT_ROOT'].'/cities_'.$_SESSION['user']['selected_db'].'/'.$image['loc_id'].'/large/'.$image['filename']);
					$thumb_image_size = filesize($_SERVER['DOCUMENT_ROOT'].'/cities_'.$_SESSION['user']['selected_db'].'/'.$image['loc_id'].'/thumbnail/'.$image['filename']);
					$large_image_size = number_format($large_image_size / 1024, 2) . ' KB';
					$thumb_image_size = number_format($thumb_image_size / 1024, 2) . ' KB';
					?>	
					<div class='galleryHolder listingsHolder thumbnail smallfont'>
						<a class='gallery' href='<?php print '/cities_'.$_SESSION['user']['selected_db'].'/'.$image['loc_id'].'/large/'.$image['filename']; ?>'>
							<img src="<?php print '/cities_'.$_SESSION['user']['selected_db'].'/'.$image['loc_id']."/thumbnail/".$image['filename'];?>?>" />
						</a>
						<div class='listingDataCont'>
							<strong><?php print $image['filename']; ?></strong>
							<br />Large: <?php print $image['width']; ?> X <?php print $image['height']." ".$large_image_size; ?>
							<br />Thumb: <?php print $image['wThumb']; ?> X <?php print $image['hThumb']." ".$thumb_image_size; ?>
							<br /><?php print date('M j Y @ g:ia',strtotime($image['date_added']));?>
							<br /><a href='/post.php?bp_id=<?php print preg_replace("@\..*$|\-.*$@","",$image['filename']); ?>&phone=<?php print  $recordsCache['summary']['phone']; ?>&foce_db=<?php print $_SESSION['user']['selected_db']; ?>'>View image post</a>
							<?php $imageCityInfo  = loc2city('loc_id', $image['loc_id'], $city_info);?>
							<br /><?php print ucwords($imageCityInfo['display_name']).", ".$state_abrv[preg_replace('@\s@','',$imageCityInfo['state'])]; ?>
							<br /><a href='https://www.google.com/searchbyimage?image_url=http://<?php print ADMIN_DOMAIN; ?>/cities_<?php print $_SESSION['user']['selected_db']; ?>/<?php print $image['loc_id']; ?>/large/<?php print $image['filename']; ?>' target='_blank'>Google</a> |   
							<a href='http://tineye.com/search?url=http://<?php print ADMIN_DOMAIN; ?>/cities_<?php print $_SESSION['user']['selected_db']; ?>/<?php print $image['loc_id']; ?>/large/<?php print $image['filename']; ?>' target='_blank'>Tineye</a>
						</div>	
					</div>
					<?php	
					if($i==300){break;}
				}
				?>
			</div>
		</div>
	</div>
</div>

<?php	
$points = $recordsCache['summary']['risk_assessment']['summary']['points'];
switch($points){
	case ($points<=-55): $riskBarColor='progress-bar-danger';  $riskText="Extreme Risk";   $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=10;   break;
	case ($points<=-45): $riskBarColor='progress-bar-danger';  $riskText="Extreme Risk";   $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=10;   break;
	case ($points<=-32): $riskBarColor='progress-bar-danger';  $riskText="Very High Risk"; $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=10;   break;
	case ($points<=-25): $riskBarColor='progress-bar-danger';  $riskText="Very High Risk"; $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=10;  break;
	case ($points<=0):   $riskBarColor='progress-bar-danger';  $riskText="High Risk"; 	   $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=20;  break;
	case ($points<=15):  $riskBarColor='progress-bar-danger';  $riskText="High Risk"; 	   $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=25;  break;
	case ($points<=30):  $riskBarColor='progress-bar-danger';  $riskText="High Risk"; 	   $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=30;  break;
	case ($points<=32):  $riskBarColor='progress-bar-danger';  $riskText="Risky"; 		   $riskIcon='icon-warning22';  $riskLabelColor='danger'; $riskPercent=35;  break;
	case ($points<=35):  $riskBarColor='progress-bar-gray';    $riskText="Risky"; 		   $riskIcon='icon-question4';  $riskLabelColor='gray';   $riskPercent=40;  break;
	case ($points<=47):  $riskBarColor='progress-bar-gray';    $riskText="Medium Risk";    $riskIcon='icon-question4';  $riskLabelColor='gray';   $riskPercent=45;  break;
	case ($points<=60):  $riskBarColor='progress-bar-gray';    $riskText="Medium Risk";    $riskIcon='icon-question4';  $riskLabelColor='gray';   $riskPercent=50;  break;
	case ($points<=67):  $riskBarColor='progress-bar-gray';    $riskText="Some Risk"; 	   $riskIcon='icon-question4';  $riskLabelColor='gray';   $riskPercent=55;  break;
	case ($points<=75):  $riskBarColor='progress-bar-gray';    $riskText="Some Risk"; 	   $riskIcon='icon-question4';  $riskLabelColor='gray';   $riskPercent=60;  break;
	case ($points<=82):  $riskBarColor='progress-bar-success'; $riskText="Some Risk"; 	   $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=65;  break;
	case ($points<=90):  $riskBarColor='progress-bar-success'; $riskText="Limited Risk";   $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=70;  break;
	case ($points<=105): $riskBarColor='progress-bar-success'; $riskText="Little Risk";    $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=75;  break;
	case ($points<=130): $riskBarColor='progress-bar-success'; $riskText="Little Risk";    $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=80;  break;
	case ($points<=140): $riskBarColor='progress-bar-success'; $riskText="Low Risk"; 	   $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=85;  break;
	case ($points<=150): $riskBarColor='progress-bar-success'; $riskText="Very Low Risk";  $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=90;  break;
	case ($points<=162): $riskBarColor='progress-bar-success'; $riskText="Very Low Risk";  $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=95;  break;
	case ($points<=175): $riskBarColor='progress-bar-success'; $riskText="Lowest Risk";    $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=100; break;
	case ($points>=175): $riskBarColor='progress-bar-success'; $riskText="Lowest Risk";    $riskIcon='icon-thumbs-up3'; $riskLabelColor='success';  $riskPercent=100; break;
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-body border-top-pink text-center">
			<h2 class="no-margin mb-5 text-semimuted">
				This provider has a 
				<span class="btn bg-<?php print $riskLabelColor;?>-600">
					<i class="<?php print $riskIcon;?> position-left"></i> 
					<?php print $riskText; ?>
				</span>
				Rating
			</h2>
				
			<div class="row">
				<div class="col-md-1  col-xs-2">
					<i class="fa fa-exclamation-triangle text-danger big-icon"></i>
				</div>
				<div class="col-md-10 col-xs-8">
					<div class="progress">
						<div class="progress-bar progress-bar-striped <?php print $riskBarColor; ?>" style="width:<?php print $riskPercent; ?>%">
							<span class="sr-only"><?php print $riskPercent; ?>% Risk</span>
						</div>
					</div>
				</div>
				<div class="col-md-1  col-xs-2">
					<i class="fa fa-thumbs-o-up text-green big-icon"></i>
				</div>
			</div>
			
		</div>
	</div>
</div>




<div class="panel panel-flat">
	<div class="panel-heading">
		<h5 class="panel-title">Risk Breakdown</h5>
	</div>

	<div class="panel-body">
		<div class="grid">
			<?php foreach($recordsCache['summary']['risk_assessment'] as $category=>$reason){ if($category=='summary'){continue;}?>
			<div class="row riskrow">
				<div class="col-lg-2 text-center">
					<h6 class="no-margin"><?php print $reason['value']; ?> 
						<small class="display-block text-size-small no-margin">
							<?php print $reason['measurement']; ?>
						</small>
					</h6>
				</div>
				<div class="col-lg-3">
					<div class='row'>
						<div class="col-lg-2 text-center">
							<?php
								switch($reason['type']){
									case 'pos':$type_color='success';$type_icon='icon-thumbs-up2';break;
									case 'neg':$type_color='danger';$type_icon='icon-thumbs-down2';break;
									case 'nue':$type_color='grey';$type_icon='icon-wave2';break;
									default: $type_color='grey';$type_icon='icon-wave2';break;
								}
							?>
							<span class="btn border-<?php print $type_color;?> text-<?php print $type_color;?> btn-flat btn-rounded btn-icon btn-xs">
								<i class="<?php print $type_icon; ?>"></i>
							</span>
						</div>
						<div class="col-lg-10">
							<span class="display-inline-block text-default text-semibold letter-icon-title"><?php print $reason['category']; ?></span>
							<div class="text-muted text-size-small"><span class="status-mark border-blue position-left"></span> <?php print $reason['importance']; ?></div>
						</div>
					</div>
				</div>
				<div class="col-lg-6 m-10">
					<div class='row'>
						<span class="text-semibold"><?php print $reason['msg']; ?></span>
					</div>
					<div class='row'>
						<span class="text-muted text-size-small"><?php print $reason['explanation']; ?></span>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

	<script type="text/javascript" src="/assets/js/plugins/visualization/echarts/echarts.js"></script>
	<script type="text/javascript" src="/assets/js/core/app.js"></script>
	<script>
	$(function () {
		// Set paths
		// ------------------------------
		require.config({
			paths: {
				echarts: '/assets/js/plugins/visualization/echarts'
			}
		});
		// Configuration
		// ------------------------------
		require(
			[
				'echarts',
				'echarts/theme/limitless',
				'echarts/chart/bar',
				'echarts/chart/line'
			],
			// Charts setup
			function (ec, limitless) {
				// Initialize charts
				// ------------------------------
				var tornado_bars_staggered = ec.init(document.getElementById('tornado_bars_staggered'), limitless);
				// Charts setup
				// ------------------------------
				//
				// Tornado with staggered labels options
				//

				var labelRight = {
					normal: {
						color: '#FF3333',
						label: {
							position: 'right'
						}
					}
				};

				tornado_bars_staggered_options = {
					// Setup grid
					grid: {
						x: 25,
						x2: 25,
						y: 25,
						y2: 10,
						containLabel: true
					},

					// Horizontal axis
					xAxis: [{
						type: 'value',
						position: 'top',
						splitLine: {
							lineStyle: {
								type: 'dashed'
							}
						},
					}],

					// Vertical axis
					yAxis: [{
						type: 'category',

						axisLine: {show: false},
						axisLabel: {show: false},
						axisTick: {show: false},
						splitLine: {show: false},

						data: [
							   <?php
							   $risk_assessment = array_reverse($recordsCache['summary']['risk_assessment']);
							   foreach($risk_assessment as $category=>$reason){if($category=='summary'){continue;}
									print "'".$reason['category']."',\n";
							   }
							   ?>
							  ]
					}],

					// Add series
					series: [
						{
							name: 'Score',
							type: 'bar',
							stack: 'Total',
							itemStyle: {
								normal: {
									color: '#66BB6A',
									barBorderRadius: 3,
									label: {
										show: true,
										position: 'left',
										formatter: '{b}',
										position: 'inside'
									}
								},
								emphasis: {
									barBorderRadius: 3
								}
							},
							data: [

							   <?php
							   foreach($risk_assessment as $category=>$reason){if($category=='summary'){continue;}
									switch($reason['category_score']){
										case 0:  $category_score='-5'; break;
										case 1:  $category_score='-4'; break;
										case 2:  $category_score='-3'; break;
										case 3:  $category_score='-2'; break;
										case 4:  $category_score='-1'; break;
										case 5:  $category_score='2'; break;
										case 6:  $category_score='1'; break;
										case 7:  $category_score='2'; break;
										case 8:  $category_score='3'; break;
										case 9:  $category_score='4'; break;
										case 10: $category_score='5'; break;
									}

									if($category_score <=0){
										print "{value: $category_score, itemStyle: labelRight}, \n";
									}else{
										print "'".$category_score."', \n";
									}
							   }
							   ?>
							]
						}
					]
				};

				// Apply options
				// ------------------------------
				tornado_bars_staggered.setOption(tornado_bars_staggered_options);
				// Resize charts
				// ------------------------------

				window.onresize = function () {
					setTimeout(function (){
						tornado_bars_staggered.resize();
					}, 200);
				}
			}
		);
	});
	</script>
	<!-- Funnel charts -->
	<div class="row">
		<div class="col-md-12">

			<div class="panel panel-flat">
				<div class="panel-heading">
					<h5 class="panel-title">Risk Graph</h5>
				</div>
				<div class="panel-body">
					<div class="chart-container">
						<div class="chart has-fixed-height" id="tornado_bars_staggered"></div>
					</div>
				</div>
			</div>
		</div>
	</div>









	
	


<!-- featherlight -->
<script src="//cdnjs.cloudflare.com/ajax/libs/detect_swipe/2.1.3/jquery.detect_swipe.min.js"></script>
<link href="//cdn.rawgit.com/noelboss/featherlight/master/release/featherlight.min.css" type="text/css" rel="stylesheet" />
<script src="//cdn.rawgit.com/noelboss/featherlight/master/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>

<!-- featherlight gallery -->

<link href="//cdn.rawgit.com/noelboss/featherlight/master/release/featherlight.gallery.min.css" type="text/css" rel="stylesheet" />
<script src="//cdn.rawgit.com/noelboss/featherlight/master/release/featherlight.gallery.min.js" type="text/javascript"></script>
<script>
$(document).ready(function(){
  $('a.gallery').featherlightGallery({
		gallery: {
		previous: '«',
		next: '»',
		fadeIn: 300
	},
	openSpeed: 300
  });
});
</script>


			
    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>