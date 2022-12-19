<?php
session_start();
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if($_GET['forced_db']){
	$forced_db=preg_replace("/[^A-Za-z0-9 ]/", '', $_GET['forced_db']);
}elseif($_SESSION['user']['selected_db']){
	$forced_db=$_SESSION['user']['selected_db'];
}else{
	$forced_db='em';
}
$_SESSION['user']['selected_db'] = $forced_db;
switch($_SESSION['user']['selected_db']){
	case 'em':$a_style='.dbAcolor a,.dbAcolor label{color:#0393D9;}';break;
	case 'bs':$a_style='.dbAcolor a,.dbAcolor label{color:#F44336;}';break;
	case 'mt':$a_style='.dbAcolor a,.dbAcolor label{color:#409843;}';break;
	case 'ts':$a_style='.dbAcolor a,.dbAcolor label{color:#ff8000;}';break;
	default:break;	
}

require_once('/var/www/shared_files/site.init.v2.php');
site_init('client.voztechnologies.com', 'voz', $forced_db);

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=records');
	exit();
}

# Include site functions
require_once('/var/www/shared_files/functions.v2.php');

# Include the records object
require_once(INCLUDES.'/records.class.php');
$records = new recordManager();
$records->city_info($forced_db);
$records->locations($_GET['updatecache']);

$PAGE_NAME = 'Records';

# Include the header
include_once(INCLUDE_PATH.'/header.php');
?>
<style>
<?php print $a_style; ?>

#pgcont {
	padding: 5px;
	width: 960px;
	margin: 20px auto;
}

#content {
	width: 270px;
	float: left;
	padding: 5px 15px;
}

#middle {
	width: 274px; /* Account for margins + border values */
	float: left;
	padding: 5px 15px;
	margin: 0px 5px 5px 5px;
}

/* for 980px or less */
@media screen and (max-width: 980px){
	#pgcont{width: 94%;}
	#content{width: 41%; padding: 1% 4%;}
	#middle{width: 41%; padding: 1% 4%; margin: 0px 0px 5px 5px; float: right;}
}

/* for 700px or less */
@media screen and (max-width: 600px){
	#content{width: auto; float: none;}
	#middle{width: auto; float: none; margin-left: 0px;}
}



	

header, #content, #middle{margin-bottom: 5px;}

.half {
  float: left;
  width: 95%;
  padding: 0 1em;
}
/* Acordeon styles */
.tab {
  position: relative;
  margin-bottom: 1px;
  width: 100%;
  color: #000;
  overflow: hidden;
}
.newinput {
  position: absolute;
  opacity: 0;
  z-index: -1;
}
label {
  position: relative;
  display: block;
  padding: 0 0 0 1em;
  background: #f2f2f2;
  font-weight: bold;
  line-height: 2;
  cursor: pointer;
  width: 100%;
  color:#3498DB;
}

.tab-content {
  max-height: 0;
  overflow: hidden;
  background: #e5e5e5;
  -webkit-transition: max-height .35s;
  -o-transition: max-height .35s;
  transition: max-height .35s;
}

.tab-content p {
  margin: .6em;
}

.tab-content p:hover {
	cursor:pointer;
}
/* :checked */
input:checked ~ .tab-content {
  max-height: 2.2em;
}
/* Icon */
label::after {
  position: absolute;
  right: 0;
  top: 0;
  display: block;
  width: 3em;
  height: 2em;
  line-height: 2;
  text-align: center;
  -webkit-transition: all .35s;
  -o-transition: all .35s;
  transition: all .35s;
}
input[type=checkbox] + label::after {
  content: "+";
}
input[type=checkbox]:checked + label::after {
  transform: rotate(315deg);
}
</style>
<div class="panel invoice-grid">
	<div class="row">
		<div class="col-sm-12 dbAcolor" style="text-align:center; padding:6px;">
		<h1>Current <?php print strtoupper($_SESSION['user']['selected_db']); ?> Locations<h1>
			<ul class="list-inline text-center">
				<li>
					<a href='http://backpage.com/' target="_blank">Backpage Link</a> | <a href='/records.php?forced_db=<?php print $_SESSION['user']['selected_db']; ?>&updatecache=1'>Update Memcache</a>
				</li><br />
				<li>
					Counted
					<?php print $records->db_record_count($_GET['updatecache'])['with_image']; print " ".strtoupper($_SESSION['user']['selected_db']); ?> posts in
					<?php print $locationsCache['summary']['count']; ?> cities on <?php print date('l g:ia',strtotime($dbCountCacheTime['summary']['date_cached'])); ?> PST
				</li><br />
				<li>
					Locations Memcached on <?php print date('l g:ia',strtotime($locationsCache['summary']['date_cached'])); ?> PST, expires <?php print date('l g:ia',strtotime($locationsCache['summary']['date_cached_expire'])); ?> PST
				</li><br/>
				<li class="text-muted">
					Change Database:
				</li><br />
				<li>
					<a style='color:#F44336;' class="<?php if($_SESSION['user']['selected_db']=='bs'){print 'text-bold';}?>" href='/records.php?forced_db=bs'>BS (Men)</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
					<a style='color:#0393D9;' class="<?php if($_SESSION['user']['selected_db']=='em'){print 'text-bold';}?>" href='/records.php?forced_db=em'>EM (Females)</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
					<a style='color:#409843;' class="<?php if($_SESSION['user']['selected_db']=='mt'){print 'text-bold';}?>" href='/records.php?forced_db=mt'>MT (Massages)</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
					<a style='color:#ff8000;' class="<?php if($_SESSION['user']['selected_db']=='ts'){print 'text-bold';}?>" href='/records.php?forced_db=ts'>TS (Transexual)</a>
				</li><br />
			</ul>
		</div>
	</div>
</div>
        
<div class="panel invoice-grid">
	<div class="row">
		<div class="col">



			<div id="pgcont" class='dbAcolor'>	
			<?php
			foreach($locationsCache['locations'] as $row){
				if(in_array($row['country'],array('uk','au','mx')) ){continue;}
					$state = $row['state'];
					if($state != $last_state){
						$last_state = $state;
						($state == 'dc')?$print_state = "DC":$print_state = ucwords(preg_replace('/zzz|zz|zzzz/','',$state));
						if($i){print "</div></div>\n\n\n\n\n\n";}
						if($row['state']=='alabama'){print "<section id='content'>";}
						elseif($row['state']=='mississippi'){print "</section><section id='middle'>";}
						elseif($row['state']=='zzalberta'){print "</section><section id='middle'>";}
						?>
							<div class="half">
								<div class="tab">
									<input class='newinput' id="tab-<?php print $i;?>" type="checkbox" name="tabs">
									<label for="tab-<?php print $i;?>"><?php print $print_state; ?></label>
					<?php
					}
					?>
									<div class="tab-content">
										<p>
											<a href='/listings.php?loc_id=<?php print $row['loc_id'];?>'>
												<?php print ucwords($row['display_name'])." - ".$row['loc_id']; ?>
											</a>
										</p>
									</div>
				<?php
					$i++;
				}
				?>
								</div>
							</div>
						</section>
				<br style='clear:both;'>
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