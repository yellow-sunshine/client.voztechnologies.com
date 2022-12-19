<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
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
	case 'em':$a_style='.dbAcolor a,.dbAcolor label{color:#0393D9 !important;}';break;
	case 'bs':$a_style='.dbAcolor a,.dbAcolor label{color:#F44336 !important;}';break;
	case 'mt':$a_style='.dbAcolor a,.dbAcolor label{color:#409843 !important;}';break;
	case 'ts':$a_style='.dbAcolor a,.dbAcolor label{color:#ff8000 !important;}';break;
	default:break;	
}

if(empty($_SESSION['user']['selected_db'])){
	header('Location:http://'.ADMIN_DOMAIN.'/records.php');
	exit();	
}

if($_GET['phone']){
	$phone=$_GET['phone'];
}elseif($_POST['phone']){
	$phone=$_POST['phone'];
}else{
	header('Location:http://'.ADMIN_DOMAIN.'/records.php');
	exit();	
}

if($_GET['post_id']){
	$post_id=$_GET['post_id'];
	$search_id = $_GET['post_id'];
	$type = 'post_id';
}elseif($_POST['post_id']){
	$post_id=$_POST['post_id'];
	$search_id = $_POST['post_id'];
	$type = 'post_id';
}elseif($_GET['bp_id']){
	$bp_id=$_GET['bp_id'];
	$search_id = $_GET['bp_id'];
	$type = 'bp_id';
}elseif($_POST['bp_id']){
	$bp_id=$_POST['bp_id'];
	$search_id = $_POST['bp_id'];
	$type = 'bp_id';
}else{	
	header('Location:http://'.ADMIN_DOMAIN.'/overview.php?phone='.$phone);
	exit();	
}

# to update cache, pass the get or post variable updatecache
($_GET['updatecache'])?$updatingCache=1:$updatingCache=0;

require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz', $forced_db);

# Include site functions
require_once('/var/www/shared_files/functions.v2.php');

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();
$records->city_info($forced_db);
$post = $records->post($search_id,$phone,$updatingCache,$type);

if($_POST['updatingPost']){
	// we are posting
	function delMalChar($val){
		preg_replace("@x00|\\\n|\\\r|\\\|\'|\"|x1a@i",'',$val);
		return $val;
	}
	$phone = delMalChar(preg_replace("/\D/","",$_POST['phone']));
	if($phone && strlen($phone) == 10 && is_numeric($phone)){
		$phone = $phone[0].$phone[1].$phone[2]."-".$phone[3].$phone[4].$phone[5]."-".$phone[6].$phone[7].$phone[8].$phone[9];
	}else{
		print "Invalid phone number";
		exit();
	}
	
	if(strlen($_POST['title'])<1){
		print "Invalid title";
		exit();
	}
	
	if(strlen($_POST['body'])<1){
		print "Invalid body";
		exit();
	}
	$sql = "UPDATE `".$_SESSION['user']['selected_db']."`.`links` SET title='".$conn->real_escape_string($_POST['title'])."', body='".$conn->real_escape_string($_POST['body'])."' WHERE post_id='".$conn->real_escape_string($_POST['post_id'])."' AND phone='".$conn->real_escape_string($_POST['phone'])."' LIMIT 1";
	$result = $conn->query($sql);
	if($result){
		$post = $records->post($search_id,$_POST['phone'],1,$type);
		print "success|@|saved";
	}else{
		print "failed|@|query not successfull";
	}
	exit();
}






# Include the header
include_once(INCLUDE_PATH.'/header.php');
?>
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
		width:155px; 
		margin:8px 1px 8px 1px;
		float:left;
	}
	.galleryHolder{
		height:295px;

	}
	.pgCont {
		margin: 0 20px 0 20px;
		<?php print $a_style; ?>
	}

</style>
<div class="panel invoice-grid" id='<?php print $post['post_id'];?>'>
	<div class="row">
		<div class="col-sm-12 dbAcolor" style="text-align:center; padding:6px;">
		<h1><a href='/overview.php?force_db=<?php print $_SESSION['user']['selected_db'];?>&phone=<?php print $recordsCache['summary']['phone']; ?>'><?php print $recordsCache['summary']['phone']."</a> on ".strtoupper($_SESSION['user']['selected_db']); ?><h1>
			<ul class="list-inline text-center">
				<li>
					<?php
					// Get data for creating bp link to posting if it still exists.
					$postCityInfo  = loc2city('loc_id', $post['loc_id'], $city_info);
					switch($_SESSION['user']['selected_db']){
						case 'em':$bp_folder='WomenSeekMen';break;
						case 'bs':$bp_folder='MenSeekWomen';break;
						case 'mt':$bp_folder='TherapeuticMassage';break;
						case 'ts':$bp_folder='Transgender';break;
						default:break;	
					}	
					$bp_url = preg_replace('@backpage\.com.*@','',$postCityInfo['url_backpage_adult']).'backpage.com/'.$bp_folder.'/title/'.$post['bp_id'];
					?>
					Post ID: <?php print $post['post_id'];?> / BPID: <a href='<?php print $bp_url; ?>' target='_blank'><?php print $post['bp_id'];?></a>
				</li><br />
				<li>
					Escort Name: <?php (!empty($recordsCache['summary']['escort_name']))?print $recordsCache['summary']['escort_name']: print "Unknown";?> 
					&nbsp;&nbsp;|&nbsp;&nbsp;<?php print $post['page_views']; ?> Views
					&nbsp;&nbsp;|&nbsp;&nbsp;<?php print $post['google_count']; ?> Google Count
					&nbsp;&nbsp;|&nbsp;&nbsp;<a href='/overview.php?force_db=<?php print $_SESSION['user']['selected_db']; ?>&phone=<?php print $recordsCache['summary']['phone']; ?>&updatecache=1'>Update Cache</a>
				</li><br />
				<li class="text-muted">
					Location: 
					<a href='<?php print "/listings.php?db=".$_SESSION['user']['selected_db']."&loc_id=".$post['loc_id']; ?>'>
						<?php print $post['match_city_name']; ?>
					</a>
				</li><br />
				<li class="text-muted">
					<strong>Date Posted:</strong> 
					<?php print date('D M jS, Y @ H:i s\s\e\c',strtotime($post['date_posted'])); ?>
				</li><br/>
				<li class="text-muted">
					<strong>Date Updated:</strong> 
					<?php print date('D M jS, Y @ H:i s\s\e\c',strtotime($post['date_updated'])); ?>
				</li><br/>
				<li class="text-muted">
					<strong>Date Added:</strong>
					<?php print date('D M jS, Y @ H:i s\s\e\c',strtotime($post['date_added'])); ?>
				</li><br />	
				<li class="text-muted">
					<strong>Date Last Viewed:</strong> 
					<?php print date('D M jS, Y @ H:i s\s\e\c',strtotime($post['date_last_viewed'])); ?>
				</li><br/>
				<li class="text-muted">
					<strong>Last Google Visit:</strong>
					<?php if(date('Y',strtotime($post['last_google_visit']))>2000){print date('D M jS, Y @ H:i s\s\e\c',strtotime($post['last_google_visit']));}else{print "Never";} ?>
				</li><br />				
				<li class="text-muted">
					<strong>Age used in post:</strong> <?php print $post['age']; ?> years old
				</li><br />
				<li class="text-muted">
					Memcached at <?php print date('g:ia',strtotime($recordsCache['summary']['date_cached'])); ?>, expires at <?php print date('g:ia',strtotime($recordsCache['summary']['date_cached_expire'])); ?>
				</li><br/>
			</ul>
		</div>
	</div>
</div>
<style>
#postform label{
	margin-top:18px;
	font-size:1.2em;
	font-weight:bold;
}
#postform input,#postform textarea{
    width:100%;
    padding:2px 2px 2px 12px;
	border:1px solid #f3f3f3;
	outline:none;
}
#postform textarea{
    padding:5px 5px 5px 12px;
    height:12em;
}
#postform input:focus,#postform textarea:focus { 
    background-color:#fcfcfc;
    border:1px solid 
    box-shadow: 0 0 5px rgba(81, 203, 238, 1);
    border: 1px solid rgba(81, 203, 238, 1);
}
#postform button{
	margin:10px 10px 10px 0;
}
</style>
<script>
function alertContents() {
	if(http_request.readyState == 4) {
		if(http_request.status == 200) {
			presponse = http_request.responseText.split("|@|");
			if(presponse[0] == 'success'){
				switch(presponse[1]){
					case 'saved':
						setTimeout(function(){changebg('#a2e060', 'postform');}, 1);
						setTimeout(function(){changebg('#a2e060', 'posttitle');}, 1);
						setTimeout(function(){changebg('#a2e060', 'postbody');}, 1);
						setTimeout(function(){changebg('#fff', 'postform');}, 500);
						setTimeout(function(){changebg('#fff', 'posttitle');}, 500);
						setTimeout(function(){changebg('#fff', 'postbody');}, 500);
						break;
					case 'postDeleted':
						setTimeout(function(){changebg('#ffe6e6', presponse[2]);$('#'+presponse[2]).fadeTo(1200,0.5);}, 1);
						setTimeout(function(){$('#'+presponse[2]).fadeOut();}, 600);
						setTimeout(function(){window.location.replace("<?php print BASE_URL; ?>/overview.php?phone=<?php print $recordsCache['summary']['phone']; ?>&forced_db=<?php print $_SESSION['user']['selected_db'];?>");}, 600);
						break;
					default:break;
				}
				
			}else if(presponse[0] == 'fail'){
				alert("Sorry, the update failed with response:" + http_request.responseText);
			}else{
				alert("An unknown response came back: [" + http_request.responseText + "] - [" + presponse[0] + "]");	
			}
		}
	}
}
function resetVals(){
	document.getElementById("posttitle").value = "<?php print preg_replace("@\"@",'\"',$post['title']); ?>";
	document.getElementById("postbody").value = "<?php print preg_replace("@\"@",'\"',$post['body']); ?>";
}

function updatePost(){
	var poststr = "post_id=" + encodeURI('<?php print $post['post_id']; ?>')
				+ "&phone=" + encodeURI('<?php print $recordsCache['summary']['phone']; ?>')
				+ "&title=" + encodeURIComponent(document.getElementById("posttitle").value)
				+ "&body=" + encodeURIComponent(document.getElementById("postbody").value)
				+ "&force_db=" + encodeURI('<?php print $_SESSION['user']['selected_db']; ?>')
				+ "&updatingPost=" + encodeURI('1');
	makePOSTRequest('<?php print BASE_URL; ?>/post.php', poststr);
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
<div class="panel invoice-grid">
	<div class="row">
	<div class='pgCont dbAcolor'>
		<form id='postform'>
			<label>Title</label>
			<input name='title' id='posttitle' type='text' value='<?php print $post['title'];?>'>
			<label>Body</label>
			<textarea id='postbody' name='body'><?php print $post['body'];?></textarea>
			<button type="button" onclick='resetVals();' class="btn btn-success"><i class=" icon-rotate-ccw2 position-left"></i> Reset </button>
			<button type="button" onclick='updatePost();' class="btn btn-primary"><i class="icon-floppy-disk position-left"></i> Save</button>
			<button style='float:right;' onclick="deletePost('<?php print $recordsCache['summary']['phone']; ?>','<?php print $post['post_id']; ?>');" type="button"  class="btn btn-warning"><i class="icon-trash position-left"></i> Delete this post & pics</button>
		</form>
	</div>
	</div>
</div>




<div class="panel invoice-grid">
	<div class="row">
		<div class="col">
			<div class='pgCont dbAcolor'>
				<?php

				for($i=1; $i <= 7; $i++){
					if($post['image'.$i]){
						list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/cities_'.$_SESSION['user']['selected_db'].'/'.$post['loc_id'].'/large/'.$post['image'.$i]);
						list($twidth, $theight, $ttype, $tattr) = getimagesize($_SERVER['DOCUMENT_ROOT'].'/cities_'.$_SESSION['user']['selected_db'].'/'.$post['loc_id'].'/thumbnail/'.$post['image'.$i]);

						$large_image_size = filesize($_SERVER['DOCUMENT_ROOT'].'/cities_'.$_SESSION['user']['selected_db'].'/'.$post['loc_id'].'/large/'.$post['image'.$i]);
						$thumb_image_size = filesize($_SERVER['DOCUMENT_ROOT'].'/cities_'.$_SESSION['user']['selected_db'].'/'.$post['loc_id'].'/thumbnail/'.$post['image'.$i]);
						$large_image_size = number_format($large_image_size / 1024, 2) . ' KB';
						$thumb_image_size = number_format($thumb_image_size / 1024, 2) . ' KB';
						?>	
						<div class='galleryHolder listingsHolder thumbnail smallfont'>
							<a class='gallery' href='<?php print '/cities_'.$_SESSION['user']['selected_db'].'/'.$post['loc_id'].'/large/'.$post['image'.$i]; ?>'>
								<img src="<?php print '/cities_'.$_SESSION['user']['selected_db'].'/'.$post['loc_id']."/thumbnail/".$post['image'.$i];?>?>" />
							</a>
							<div class='listingDataCont'>
								<strong><?php print $post['image'.$i]; ?></strong>
								<br />Large: <?php print $width; ?> X <?php print $height." ".$large_image_size; ?>
								<br />Thumb: <?php print $twidth; ?> X <?php print $theight." ".$thumb_image_size; ?>
								<br />Added <?php print date('M j Y @ g:ia',strtotime($post['date_added']));?>
								<br /><a href='/post.php?phone=<?php print $recordsCache['summary']['phone']; ?>&bp_id=<?php print preg_replace("@\..*$|\-.*$@","",$post['image'.$i]); ?>&foce_db=<?php print $_SESSION['user']['selected_db']; ?>'>View image post</a>
								<br /><?php print $post['match_city_name']; ?>
								<br />Image Database: <?php print $_SESSION['user']['selected_db']; ?>
								<br />Image Folder: <?php print $post['loc_id']; ?>
								<br /><a href='https://www.google.com/searchbyimage?image_url=http://<?php print ADMIN_DOMAIN; ?>/cities_<?php print $_SESSION['user']['selected_db']; ?>/<?php print $post['loc_id']; ?>/large/<?php print $post['image'.$i]; ?>' target='_blank'>Google</a> |   
								<a href='http://tineye.com/search?url=http://<?php print ADMIN_DOMAIN; ?>/cities_<?php print $_SESSION['user']['selected_db']; ?>/<?php print $post['loc_id']; ?>/large/<?php print $post['image'.$i]; ?>' target='_blank'>Tineye</a>
							</div>	
						</div>
						<?php	
						if($i==40){break;}
					}
				}
				?>
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