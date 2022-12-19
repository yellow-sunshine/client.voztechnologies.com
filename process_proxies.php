<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once(INCLUDES.'/functions.php');
switch($_POST['action']){
	case 'disenable': 	
					if($_POST['current_state'] == 'checked'){ $newstate=0; } else { $newstate=1; }
					$sql = "UPDATE `voz`.`proxy` SET enabled='".$newstate."' WHERE proxy_id='".mysqli_real_escape_string($connect,$_POST['proxyID'])."' LIMIT 1";
				 	break;
	default: 		break;
}

if(mysqli_query($connect,$sql)){
	print "Success ".$sql;	
}else{
	print "Fail ".$sql;	
}
?>