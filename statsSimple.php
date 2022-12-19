<html>
	<head>
		
	</head>
	<body>
<div>
	Stats
</div>
	<div>
<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');

# Include site functions
include_once(INCLUDES.'/functions.php');


session_start();
$nowday = date('t');
$nowhour = date('G');
$nowmin = preg_replace('/^0/','',date('i'));
if(date('j') < 16){
	$totalday=15-date('j');
}else{
	$totalday=date('t')-date('j');
}	
$totalhour = 24-($nowhour+1);
$totalmin = 60-$nowmin;
$timeleft = $totalday.' days '.$totalhour.' hrs and '.$totalmin.' min';


		
		

            $datetime = new DateTime; // current time = server time
            //$lat  = new DateTimeZone('America/Los_Angeles');
            //$cht  = new DateTimeZone('America/Chicago');
            //$nyct  = new DateTimeZone('America/New_York');
            //$datetime->setTimezone($lat); // calculates with new TZ now
            

            
            $earnings_todaysql = "SELECT 
                                    sum(`aff_payout` + `tdn_payout` +  `lc_payout` + `ht_payout` + `ter_payout`) AS payout, 
                                    sum(`aff_hits` + `tdn_hits` + `lc_hits` + `ht_hits` + `ter_hits`) AS hits
                                    FROM `voz`.`stats` WHERE date = '".$datetime->format('Y-m-d')."'";
            $earnings_todayresult = mysqli_query($connect,$earnings_todaysql);
            $earnings = $earnings_todayresult->fetch_assoc();
			
			$ydate = new DateTime();
			$ydate->sub(new DateInterval('P1D'));
            $earnings_yesterdaysql = "SELECT 
                                    sum(`aff_payout` + `tdn_payout` +  `lc_payout` + `ht_payout` + `ter_payout`) AS payout, 
                                    sum(`aff_hits` + `tdn_hits` + `lc_hits` + `ht_hits` + `ter_hits`) AS hits
                                    FROM `voz`.`stats` WHERE date = '".$ydate->format('Y-m-d')."'";
            $earnings_yesterdayresult = mysqli_query($connect,$earnings_yesterdaysql);
            $yesterdayearnings = $earnings_yesterdayresult->fetch_assoc(); 
	

			print "THits: ".number_format($earnings['hits'],0); 
		    print " YPay: $".number_format($yesterdayearnings['payout'],0);
			print " YHits: ".number_format($yesterdayearnings['hits'], 0, '', ',');
			$totalPostsDS = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as em30min FROM `em`.`links` WHERE date_added > DATE_SUB(NOW(), INTERVAL 30 MINUTE)"));
			$dup = sprintf('%.2f',((disk_total_space("/") - disk_free_space("/")) / disk_total_space("/")) * 100);
			$load = sys_getloadavg();
			print " Disk: ".$dup."%";
			print " Load: ".$load[1]."";
			print " Posts: ".$totalPostsDS['em30min'];
		
			print " TimeNow: ".date("g:ia");
		
		
			print " TimeLeft: ";
			date_default_timezone_set('America/Halifax'); // Set timezone to when TDN stats change over
			$hours = 24-date('G');
			$minutes = 60-date('i');
			date_default_timezone_set('America/Los_Angeles'); // Change timezone back to LA	

            if($hours < 10){
				print "0".$hours.":";
			}elseif($hours==24){
				print "";
			}else{
				print $hours."h ";
			} 
		
		    if($minutes < 10){
				print "0".$minutes."m";
			}else{
				print $minutes."m";
			}
		
		
		
?>
                
                
</div>

</body>
</html>
<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>