<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=records');
	exit();
}


// Ge t the databse we are selecting through the URL
if($_GET['forced_db']){
	$forced_db = $_GET['forced_db'];
	$_SESSION['user']['selected_db'] = $_GET['forced_db'];
}elseif(empty($_SESSION['user']['selected_db'])){
	header('Location://'.ADMIN_DOMAIN.'/records.php');
	exit();
}else{
	$forced_db = $_SESSION['user']['selected_db'];
	$_SESSION['user']['selected_db'] = $forced_db;
}
switch($_SESSION['user']['selected_db']){
	case 'em':$a_style='.dbAcolor a,.dbAcolor label{color:#0393D9;}';break;
	case 'bs':$a_style='.dbAcolor a,.dbAcolor label{color:#F44336;}';break;
	case 'mt':$a_style='.dbAcolor a,.dbAcolor label{color:#409843;}';break;
	case 'ts':$a_style='.dbAcolor a,.dbAcolor label{color:#ff8000;}';break;
	default:break;
}


// Get connection to DB and other settings
require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz', $_SESSION['user']['selected_db']);

$page = $_GET['pg'];
if(empty($page)){
	$page=1;
}
# Include site functions
require_once('/var/www/shared_files/functions.v2.php');

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();
$records->city_info();
$listingsCache = $records->listings($_GET['loc_id'], $page, POST_PER_PAGE,$_GET['updatecache']);

// Get pagination html
if($listingsCache['summary']['city_total_records'] > POST_PER_PAGE){
	$base_link = "//".ADMIN_DOMAIN."/listings.php?loc_id=".$listingsCache['summary']['loc_id'];
	//Show previous link only if we are on page 2 or more
	if($page > 1){
		$paginationHTML .= "<a class='btn-sm' href='$base_link&pg=";$paginationHTML .=$page - 1; $paginationHTML .= "'>Prev</a>";
	}
	$p = $page-4;
	if($p<1){$p=1;}
	$stop = $page+4;
	if($p<4){$stop = $p+10;}
	while($p < $stop){
		if($p==$page){
			$paginationHTML .= "<a class='btn-sm activepg' href='$base_link&pg=$p'>$p</a>";
		}else{
			$this_pos = ($p*POST_PER_PAGE)-POST_PER_PAGE;
			if( $listingsCache['summary']['city_total_records'] > $this_pos ){
				$paginationHTML .= "<a class='btn-sm ' href='$base_link&pg=$p"; $paginationHTML .= "'>$p</a>";
			}
		}
		$p++;
	}

	if($listingsCache['summary']['city_total_records'] > $page+POST_PER_PAGE){
		$paginationHTML .= "<a class='btn-sm ' href='$base_link&pg="; $paginationHTML .= $page + 1; $paginationHTML .= "'>Next</a>";
	}
}


# Include the header
include_once(INCLUDE_PATH.'/header.php');
?>
<style>
	<?php print $a_style; ?>

	.pagination{
		color:#fff;
	}
	.pagination a{
		color:#fff;
	}
	.pagination a:hover{
	}
	.activepg{
		background-color:#efefef !important;
		color:#3498DB !important;
	}
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
		height:315px;

	}
	.listingDataCont a{
		display:inline;
	}
	.titletxt{
		font-size:0.8em;
	}
	.icontxt{
		font-size:4em;
	}
	.txtcenter{
		text-align:center;
		margin:10px;
	}
	.delLink{
		cursor:pointer;
	}
</style>
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
<div class="panel invoice-grid">
	<div class="row">
		<div class="col-sm-12 dbAcolor" style="text-align:center; padding:6px;">
			<ul class="list-inline text-center">
				<h1>
					<?php print $listingsCache['summary']['match_city_name']; ?>
				</h1>
				<li>
					<a href='/records.php'>Change Location</a> | <a href="//cron.blr.pw/scrape_cities.php?db=<?php print $_SESSION['user']['selected_db']; ?>&loc_id=<?php print $listingsCache['summary']['loc_id']; ?>' target='_blank'>Find New Ads</a>
				</li><br/>
				<li>
					<a href='<?php print $listingsCache['summary']['url_backpage_adult']; ?>' target="_blank">Backpage Link</a> |
					<a href='/listings.php?loc_id=<?php print $listingsCache['summary']['loc_id']; ?>&forced_db=<?php print $_SESSION['user']['selected_db']; ?>&updatecache=1&pg=<?php print $page; ?>'>Update Memcache for pg <?php print $page; ?></a>
				</li><br />
				<li class="text-muted">
					Scrape Cache: <?php print $listingsCache['summary']['cache_timeout']; ?> |
					Loc ID: <?php print $listingsCache['summary']['loc_id']; ?> |
					<?php print $listingsCache['summary']['city_total_records']; ?> Records
				</li><br/>
				<li class="text-muted">
					Memcached at <?php print date('g:ia',strtotime($listingsCache['summary']['date_cached']))." for ".$listingsCache['summary']['cache_time']." sec"; ?>, expires at <?php print date('g:ia',strtotime($listingsCache['summary']['date_cached_expire'])); ?>
				</li><br/>
				<li class="text-muted">
					Lon: <?php print $listingsCache['summary']['lon']." Lat: ".$listingsCache['summary']['lat']; ?>
				</li><br/>
				<li>
					<?php
					// get url and text for links to sites depending on the type of db we are using
					switch($_SESSION['user']['selected_db']){
						case 'em': $link1 = 'http://www.eroticmugshots.com/'.$listingsCache['summary']['city_key_name'].'-escorts';
								   $txt1='EroticMugshots.com';
								   $link2 = 'http://www.escortphone.review/'.$listingsCache['summary']['city_key_name'].'-escort-reviews';
								   $txt2='Escortphone.review';
								   $link3 = 'http://www.escortresume.com/'.$listingsCache['summary']['city_key_name'].'-resumes';
								   $txt3='EscortResume.com';
								   break;
						case 'bs': $link1 = 'http://www.boyscort.com/'.$listingsCache['summary']['city_key_name'].'-escorts';
								   $txt1='Boyscort.com';
								   $link2 = 'http://www.boyrentals.com/'.$listingsCache['summary']['city_key_name'].'-gay-reviews';
								   $txt2='BoyRentals.com';
								   $link3 = 'http://www.gayresumes.com/gay-'.$listingsCache['summary']['city_key_name'].'-resumes';
								   $txt3='GayResumes.com';
								   break;
						case 'ts': $link1 = 'http://www.tsphonesearch.com/'.$listingsCache['summary']['city_key_name'].'-ts-phone-numbers';
								   $txt1='TSPhoneSearch.com';
								   $link2 = 'http://www.tsescort.review/'.$listingsCache['summary']['city_key_name'].'-ts-reviews';
								   $txt2='TSEscort.review';
								   $link3 = 'http://www.transresumes.com/trans-'.$listingsCache['summary']['city_key_name'].'-resumes';
								   $txt3='TransResumes.com';
								   break;
						case 'mt': $link1 = 'http://www.massagetroll.com/'.$listingsCache['summary']['city_key_name'].'-massages';
								   $txt1='Massagetroll.com';
								   $link2 = 'http://www.bodyrubs.review/'.$listingsCache['summary']['city_key_name'].'-bodyrub-reviews';
								   $txt2='Bodyrubs.review';
								   $link3 = 'http://www.bodyrubresumes.com/bodyrub-'.$listingsCache['summary']['city_key_name'].'-ratings';
								   $txt3='BodyRubResumes.com';
								   break;
						default:   break;
					}
					?>
					<a href='<?php print $link1; ?>' target="_blank"><?php print $txt1 ?></a> |
					<a href='<?php print $link2; ?>' target="_blank"><?php print $txt2 ?></a> |
					<a href='<?php print $link3; ?>' target="_blank"><?php print $txt3 ?></a>
				</li><br />
				<li>
   					<a style='color:#F44336;' class="<?php if($_SESSION['user']['selected_db']=='bs'){print 'text-bold';}?>" href='/records.php?forced_db=bs'>BS</a>&nbsp;&nbsp;|&nbsp;&nbsp;
					<a style='color:#0393D9;' class="<?php if($_SESSION['user']['selected_db']=='em'){print 'text-bold';}?>" href='/records.php?forced_db=em'>EM</a>&nbsp;&nbsp;|&nbsp;&nbsp;
					<a style='color:#409843;' class="<?php if($_SESSION['user']['selected_db']=='mt'){print 'text-bold';}?>" href='/records.php?forced_db=mt'>MT</a>&nbsp;&nbsp;|&nbsp;&nbsp;
					<a style='color:#ff8000;' class="<?php if($_SESSION['user']['selected_db']=='ts'){print 'text-bold';}?>" href='/records.php?forced_db=ts'>TS</a>
				</li>

			</ul>
		</div>
	</div>
</div>

<div class="panel invoice-grid">
	<div class="row" style='margin-left:10px;'>
		<div class="col-xs-12 col-md5 dbAcolor" style='margin:auto;'>
			<div class='txtcenter'>
				<?php print $paginationHTML; ?>
			</div>
			<?php
			foreach($listingsCache['posts'] as $listingCount=>$listing){
				?>

				<div class='galleryHolder listingsHolder thumbnail smallfont' id='<?php print $listing['post_id']; ?>'>
					<a href='/overview.php?forced_db=<?php print $_SESSION['user']['selected_db']; ?>&phone=<?php print $listing['phone']; ?>'>
						<img src="<?php print "/cities_".$_SESSION['user']['selected_db'].'/'.$listing['loc_id']."/thumbnail/".$listing['image1'];?>" alt='<?php print "Image for ".$listing['phone'];?>' />
					</a>
					<div class='listingDataCont'>
						<span class='titletxt'><?php print dots(30,$listing['title']); ?></span>
						<br /> <strong><?php print $listing['phone']; ?></strong> <?php print $listing['age']; ?> yrs
						<br /> <a href='/post.php?forced_db=<?php print $_SESSION['user']['selected_db'];?>&post_id=<?php print $listing['post_id']; ?>&phone=<?php print $listing['phone']; ?>'>Post</a> |
							   <a href='/overview.php?forced_db=<?php print $_SESSION['user']['selected_db'];?>&phone=<?php print $listing['phone']; ?>'>Overview</a>
						<br /> <?php print date('M j @ g:ia',strtotime($listing['date_posted'])); ?>
						<br /> <?php print date('M j @ g:ia',strtotime($listing['date_updated'])); ?>
						<br /> <?php print $listing['page_views'] ?> Views
						<br /> <span class='delLink' onclick="deletePhone('<?php print $listing['phone']; ?>','<?php print $listing['post_id']; ?>');">Del Phone</span> |
							   <span class='delLink' onclick="deletePost('<?php print $listing['phone']; ?>','<?php print $listing['post_id']; ?>');">Del Post</span>

						<br /> TER:
								<?php
								if($listing['ter_reviews']){ ?>
									<a href='http://coop.theeroticreview.com/hit.php?s=1&p=2&w=101908&t=0&c=74&u=http://www.theeroticreview.com/reviews/newreviewsList.asp?phone=<?php print $listing['phone']; ?>' target="_blank">YES</a>
								<?php }else{ ?>
									No
								<?php } ?>
							    | Star Rating: <?php print $listing['star_rating']; ?>
						<br />Post ID: <?php print $listing['post_id']; ?>
						<br /> BP ID: <?php print $listing['bp_id']; ?>
					</div>
				</div>
				<?php
				$ppcount++;if($ppcount>POST_PER_PAGE){break;}
			}
			?>
			<div class='txtcenter'>
				<br style='clear:both;'>
				<?php print $paginationHTML; ?>
			</div>
		</div>
	</div>
</div>




    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>