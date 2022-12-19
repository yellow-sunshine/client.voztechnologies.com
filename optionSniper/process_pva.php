<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
switch($_POST['action']){
	case 'disenable':
					if($_POST['current_state'] == 'checked'){ $newstate=0; } else { $newstate=1; }
					$sql = "UPDATE `twitter_sniper`.`twitter_accounts` SET enabled='".$newstate."' WHERE account_id='".mysqli_real_escape_string($connect,$_POST['account_id'])."' LIMIT 1";
				 	break;
	default: 		break;
}

if(mysqli_query($connect,$sql)){
	print "Success ".$sql;
}else{
	print "Fail ".$sql;
}
?>