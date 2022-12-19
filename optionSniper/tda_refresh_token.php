<?php
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once(__DIR__.'/get_settings.php');
include_once(__DIR__.'/functions.v2.php');
include_once(INCLUDES.'/voz_settings.php');

if($tda->refresh_token()){
	if($_POST['p']=='jg26tUYG25ruyty4Fg6u6hb72buyDGjO'){
		# If p was passed then it is ajax requesting we update and it wants a response
		$rs=$conn->query("SELECT * FROM `twitter_sniper`.`settings` LIMIT 500");
		// Loop over the records found and store the phone number
		while($row = $rs->fetch_assoc()){
			$optionsniper_settings[$row['variable']] = array('variable'=>$row['variable'],
												 'value'=>$row['value'],
												 'date'=>$row['date'],
												 'id'=>$row['id']);
		}
		print "refreshsuccess|@|".$optionsniper_settings['tda_refresh_token']['value']."|@|".$optionsniper_settings['tda_access_token']['value'];
		$memcache->delete('optionsniper_settings'); // Delete the sniper settings so we will have to get it again
	}else{
		print "failed";
	}
}else{
	print "failed";
}
exit();
?>