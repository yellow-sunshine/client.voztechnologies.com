<?php

	


/*
* 
*    Truncates a string to the passed length and adds 3 dots
* 
*/
# Cut string down to the passed number of character and add 3 dots at the end
function dots($shownum=10, $string, $replacement='...'){
	if(strlen($string) > $shownum){
		$string = substr_replace($string, $replacement, $shownum);
	}
	return $string;
}






function loc2city($find_key_name, $with_val, $array){
	if(is_array($array)){
		foreach($array as $d => $a){
			if($a[$find_key_name] == $with_val){
				$a['key'] = $d;
				return $a;
			}
		}
	}else{
		print "array not valid";
	}
}








# Deletes a single image from an add bassed on the loc ID and the image name
function doDelete($loc_id, $imgName){
	@unlink(DOWNLOADED_IMAGES_PATH."/".$loc_id."/large/".$imgName);
	@unlink(DOWNLOADED_IMAGES_PATH."/".$loc_id."/thumbnail/".$imgName);
}








function getRemoteIPAddress(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$ip=preg_replace("@,.*$@","",$ip);
	return $ip;
}




?>