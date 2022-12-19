<div id='sidebar1' class="sidebar sidebar-main sidebar-default">
    <div class="sidebar-content">
        <div class="">
                    <div class="panel panel-flat">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-watch position-left"></i> Time Left Today</h6>
                        </div>
                        <div class="panel-body">
                            <ul class="timer-weekdays mb-10">
                            	<?php
									date_default_timezone_set('America/Halifax'); // Set timezone to when TDN stats change over
									$dayA = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
									foreach($dayA as $day=>$abv){
										if(date('D') == $abv){
											print "<li> <span style='padding:4px; font-size:9px; margin-left:2px;' class='label label-danger'> $abv </span> </li>";
										}else{
											print "<li> <span style='padding:4px; font-size:9px; margin-left:2px;' class='label label-default'> $abv </span> </li>";
										}
									}
									$hours = 24-date('G');
									$minutes = 60-date('i');
									$seconds = 60-date('s');
									date_default_timezone_set('America/Los_Angeles'); // Change timezone back to LA
								?>
                            </ul>
							<div id="defaultCountdown"></div>
                            <style type="text/css">
								#remaininghr, #remainingmin, #remainingsec, .colon{
									font-size:26px;
									font-weight:bold;
									display:inline-block;
									margin-right:none;
									padding-right:none;
								}
								#timeLablehrs, #timeLablemin, #timeLablesec{
									font-size:10px;
									color:#BCBCBC;
									display:inline-block;	
									margin-left:none;
									padding-left:none;
								}
								.colon{
									width:10px;
									text-decoration:blink;
								}
							</style>
                            <ul class="timer mb-10">
                                <li>
                                    <div><span id='remaininghr'><?php if($hours < 10){print "0";print $hours;}else{print $hours;} ?></span><div id='timeLablehrs'>Hrs </div></div>
                                </li>
                                <li>
                                    <div><span id='remainingmin'><?php if($minutes < 10){print "0";print $minutes;}else{print $minutes;} ?></span><div id='timeLablemin'>Min </div></div>
                                </li>
                                <li>
                                    <div><span id='remainingsec'><?php if($seconds < 10){print "0";print $seconds;}else{print $seconds;} ?></span><div id='timeLablesec'>Sec </div></div>
                                </li>
                            </ul>
                            <?php print date("l, M jS Y"); ?><br />
                            <?php print date("g:i a").' LAX'; ?>
                        </div>
                    </div>
					<script language="javascript">

                    (function() {
                        var counter = <?php print $seconds; ?>;
                        var remainings = document.getElementById("remainingsec");
                        var remainingm = document.getElementById("remainingmin");
                        var remainingh = document.getElementById("remaininghr");

                        remainings.innerHTML = <?php print $seconds; ?>;
                        remainingm.innerHTML = <?php print $minutes; ?>;
                        remainingh.innerHTML = <?php if($hours < 10){print "0";print $hours;}elseif($hours==24){print 0;}else{print $hours;} ?>;
						<?php if($hours==24){ ?>
						document.getElementById('timeLablehrs').innerHTML = "";
						document.getElementById('remaininghr').style.display='none';
						<?php
						}
						?>
                        var id;
                        
                        
                        id = setInterval(function() {
                            counter--;
                            if(counter < 0) {
                                counter = 59;
                                remainings.innerHTML = counter.toString();
                                if(remainingm.innerHTML==0){
                                    if(remainingh.innerHTML==0){
                                        remainingh.innerHTML = 23;
                                        remainingm.innerHTML = 59;
                                    }else{
										if(parseInt(remainingh.innerHTML)-1 < 10){ /* Make sure if hour changes to 10 or below that it has a leading 0 */
											remainingh.innerHTML= '0' + String(parseInt(remainingh.innerHTML)-1);
										}else{
											remainingh.innerHTML=parseInt(remainingh.innerHTML)-1;
										}
									    remainingm.innerHTML = 59;
                                    }
                                }else{
									if(parseInt(remainingm.innerHTML)-1 < 10){
										remainingm.innerHTML= '0' + String(parseInt(remainingm.innerHTML)-1);
									}else{
										remainingm.innerHTML=parseInt(remainingm.innerHTML)-1;
									}
                                }
								
								remaininghval = document.getElementById("remaininghr").innerHTML
								remaininghval.replace(/^0/,""); 
								if(remaininghval  < 10){
									document.getElementById("remaininghr").innerHTML = '0' + remaininghval.replace(/^0/,"");
								}
								
								remainingmval = document.getElementById("remainingmin").innerHTML
								remainingmval.replace(/^0/,""); 
								if(remainingmval  < 10){
									document.getElementById("remainingmin").innerHTML = '0' + remainingmval.replace(/^0/,"");
								}
								
								remainingsval = document.getElementById("remainingsec").innerHTML
								remainingsval.replace(/^0/,""); 
								if(remainingsval  < 10){
									document.getElementById("remainingsec").innerHTML = '0' + remainingsval.replace(/^0/,"");
								}
								
								
                            } else {
								
                                remainings.innerHTML = counter.toString();
								remaininghval = document.getElementById("remaininghr").innerHTML
								remaininghval.replace(/^0/,""); 
								if(remaininghval  < 10){
									document.getElementById("remaininghr").innerHTML = '0' + remaininghval.replace(/^0/,"");
								}
																
								remainingmval = document.getElementById("remainingmin").innerHTML
								remainingmval.replace(/^0/,""); 
								if(remainingmval  < 10){
									document.getElementById("remainingmin").innerHTML = '0' + remainingmval.replace(/^0/,"");
								}
								
								remainingsval = document.getElementById("remainingsec").innerHTML
								remainingsval.replace(/^0/,""); 
								if(remainingsval  < 10){
									document.getElementById("remainingsec").innerHTML = '0' + remainingsval.replace(/^0/,"");
								}	
                            }
                        }, 1000);
                    })();
                    </script>
			<?php
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
	
            ?>
            <div class="sidebar-category">
                     <div class="category-content">
                        <div class="row row-condensed">
                            <div class="col-xs-6">
                                <button type="button" class="btn bg-success-600 btn-block btn-float"><i class="icon-coins"></i> <span>$<?php print number_format($earnings['payout'],0); ?></span></button>
                                <button type="button" class="btn bg-blue-600 btn-block btn-float"><i class="icon-coins"></i> <span>$<?php print number_format($yesterdayearnings['payout'],0); ?></span></button>
                            </div>
                            <div class="col-xs-6">
                                <button type="button" class="btn bg-success-600 btn-block btn-float"><i class="icon-mouse"></i> <span><?php print number_format($earnings['hits'],0); ?></span></button>
                                <button type="button" class="btn bg-blue-600 btn-block btn-float"><i class="icon-mouse"></i> <span><?php print number_format($yesterdayearnings['hits'], 0, '', ','); ?></span></button>
                            </div>
                    	</div>
                    </div>
				<?php
					$totalPostsDS = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as em30min FROM `em`.`links` WHERE image1 IS NOT NULL AND ( date_added > DATE_SUB(NOW(), INTERVAL 30 MINUTE) OR date_updated > DATE_SUB(NOW(), INTERVAL 30 MINUTE) )     "));
					$totalPostsDS2 = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as em1day FROM `em`.`links` WHERE image1 IS NOT NULL AND ( date_updated > DATE_SUB(NOW(), INTERVAL 1 DAY)   OR  date_updated > DATE_SUB(NOW(), INTERVAL 1 DAY)  )   "));
					$total404t = mysqli_fetch_array(mysqli_query($connect,"SELECT SUM(`count`) as t404 FROM `voz`.`http_errors` WHERE date = CURDATE()"));
					$total404y = mysqli_fetch_array(mysqli_query($connect,"SELECT SUM(`count`) as t404 FROM `voz`.`http_errors` WHERE date = DATE_SUB(curdate(), INTERVAL 1 DAY)"));
					$totalRemovals = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as removalCount FROM `voz`.`banned_phone` WHERE `date_added` > CURDATE()"));
					$totalOldPosts = mysqli_fetch_array(mysqli_query($connect,"SELECT count(*) as oldCount FROM `voz`.`old_posts_removed` WHERE `date_removed` >= CURDATE()"));
					$dup= sprintf('%.2f',((disk_total_space("/") - disk_free_space("/")) / disk_total_space("/")) * 100);
					$load = sys_getloadavg();
	
	
					$AutResult = exec("apachectl status | grep uptime | sed 's/Server uptime://g'");
					$AutResult = preg_replace('/minutes.*/','min',$AutResult);
					$AutResult = preg_replace('/hours/','hr',$AutResult);
	
	
					$ctr = mysqli_query($connect,"SHOW STATUS WHERE `variable_name` = 'Threads_connected'");
					$currrent_threads = $ctr->fetch_assoc();
					$currrent_threads = $currrent_threads['Value'];
	
					//if($memcache->get('currentthreads') < 1){
					if($currrent_threads < 1){
						$currrent_threads = 'No';
					}
	
				?>
                <div class="sidebar-category">
                    <div class="category-content no-padding">
                        <ul class="navigation navigation-alt navigation-accordion">
                            <li><a href="#"><i class="icon-floppy-disk"></i> <strong>M5 Disk: </strong><?php print $dup; ?>% Full</a></li>
                            <li><a href="#"><i class="icon-tux"></i> <strong>M5 Load:</strong> <?php print $load[0].", ".$load[1].", ".$load[2]; ?></a></li>
                            <li><a href="#"><i class="icon-stack3"></i> <strong>Thread Count:</strong> <?php print $currrent_threads; ?> Threads</a></li>
                            <li><a href="#"><i class="fa fa-hourglass-3"></i> <strong>Apache Uptime:</strong> <?php print $AutResult; ?></a></li>
                            <li><a href="#"><i class="fa fa-tags"></i> <strong>EM Posts last 30min:</strong> <?php print $totalPostsDS['em30min'];?></a></li>
                            <li><a href="#"><i class="fa fa-tags"></i> <strong>EM Posts last 24 hours:</strong> <?php print $totalPostsDS2['em1day'];?></a></li>
                            <li><a href="#"><i class="fa fa-exclamation-triangle"></i> <strong>404 Y:</strong> <?php print $total404y['t404'];?>/ <strong> T:</strong> <?php print $total404t['t404'];?></a></li>
                            <li><a href="#"><i class="fa fa-ban"></i> <strong>Removal requests today:</strong> <?php print $totalRemovals['removalCount'];?></a></li>
                            <li><a href="#"><i class="fa fa-ban"></i> <strong>Old posts deleted today:</strong> <?php print $totalOldPosts['oldCount'];?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="category-content no-padding">
                    <ul class="navigation navigation-main">
                    <li>
                        <a href="#"><i class="icon-stack2"></i> <span>Internal</span></a>
                        <ul>
                            <li><a href="//a.blr.pw/" target="_blank"><i class="fa fa-line-chart"></i> Piwik</a></li>
                            <li><a href="//<?php print ADMIN_DOMAIN; ?>/server-status" target="_blank"><i class="fa fa-tasks"></i> Apache Top</a></li>
                            <li><a href="//<?php print ADMIN_DOMAIN; ?>/phpMemcachedAdmin1.3.0/" target="_blank"><i class="fa fa-tasks"></i> phpMCA 1.3.0</a></li>
                            <li><a href="//<?php print ADMIN_DOMAIN; ?>/phpMemcachedAdmin/" target="_blank"><i class="fa fa-tasks"></i> phpMCA 1.2.2</a></li>                            
                            <li class="navigation-divider"></li>
                            <li>
                                <a href="#"><i class="fa fa-database"></i> phpMyAdmin</a>
                                <ul>
                                    <li><a href="/phpmyadmin4voz" target="_blank">M5</a></li>
                                    
                                    <li><a href="//vpsd.blr.pw/phpmyadmin/" target="_blank">VPSD</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#"><i class="fa fa-database"></i> php Info</a>
                                <ul>
                                    <li><a href="/phpinfofile.php" target="_blank">M5</a></li>
                                    <li><a href="//vpsd.blr.pw/phpinfofile.php" target="_blank">VPSD</a></li>
                                    <li><a href="//aws.blr.pw/phpinfofile.php" target="_blank">AWS</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#"><i class="fa fa-database"></i> Restart Services</a>
                                <ul>
                                    <li><a href="//aws.blr.pw/restart/services.php" target="_blank">From AWS</a></li>
                                    <li><a href="//vpsd.blr.pw/restart/services.php" target="_blank">From VPSD</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#"><i class="fa fa-database"></i> Sites</a>
                                <ul>
                                    <?php
                                    $sitelistsql = "SELECT * FROM `voz`.`sites` WHERE site_type !='adm' ORDER BY `site_type`, `site_name`";
                                    $sitelistresult = mysqli_query($connect,$sitelistsql);
                                    $sc=0;$lastType='';
                                    while($siterow=mysqli_fetch_array($sitelistresult)){
                                        if($siterow['site_type'] != $lastType ){
                                            if($sc > 1){
                                                print "</ul></li>";
                                            }
                                            print "<li><a href='#'>".strtoupper($siterow['site_type'])."</a><ul>";
                                            
                                        }
                                        print "<li><a href='//www.".$siterow['site_name']."/'  target='_blank'>".ucwords($siterow['site_name'])."</a></li>";
                                        
                                        $lastType = $siterow['site_type']; $sc++;
                                    }
                                    print "<li><a href='//www.exgirlfriendimages.com/' target='_blank'>Exgirlfriendimages.com</a></li>
										   <li><a href='//www.sextravelersguide.com/' target='_blank'>Sextravelersguide.com</a></li>
										   <li><a href='//www.voztechnologies.com/' target='_blank'>VozTechnologies.com</a></li>";
                                    print "</ul></li>";
                                    ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="icon-stack2"></i> <span>External</span></a>
                        <ul>
                            <li><a href="https://service.m5hosting.com/" target="_blank"><i class="fa fa-server"></i> M5 Hosting</a></li>
                            <li><a href="https://signin.aws.amazon.com/" target="_blank"><i class="fa fa-server"></i> AWS</a></li>
                            <li><a href="https://www.cloudflare.com/login" target="_blank"><i class="fa fa-cloud"></i> CloudFlare</a></li>
                            <li><a href="https://www.namecheap.com/myaccount/login-only.aspx" target="_blank"><i class="fa fa-check-square"></i> NameCheap</a></li>
                            <li><a href="https://mailgun.com/app/dashboard" target="_blank"><i class="fa fa-envelope"></i> MailGun</a></li>
                            <li><a href="https://dashboard.nexmo.com/sign-in" target="_blank"><i class="fa fa-mobile"></i> Nexmo</a></li>
                            <li><a href="https://vpsdime.com/clientarea.php?action=productdetails&id=33553" target="_blank"><i class="fa fa-server"></i> VPS Dime</a></li>
                            <li class="navigation-divider"></li>
                            <li>
                                <a href="#"><i class="fa fa-database"></i> Proxies</a>
                                <ul>
                                    <li><a href="http://www.sharedproxies.com/user.php" target="_blank"> Shared Proxies</a></li>
                                    <li><a href="https://panel.limeproxies.com/login" target="_blank"> Lime Proxies (Not Used)</a></li>
                                    <li><a href="https://actproxy.com/clientarea.php" target="_blank"> ACT Proxy (Not Used)</a></li>
                                </ul>
                            </li> 
                        </ul>
                    </li>
                    
                    <li>
                        <a href="#"><i class="icon-alarm-check"></i> Cron/Scripts</a>
                        <ul>
                            <li><a href="//vpsd.blr.pw/check_connectivity.php" target="_blank"><strong><i class='glyphicon glyphicon-resize-horizontal'></i></strong> Check Con from VPSD</a></li>
                            <li><a href="//cron.blr.pw/removals.php?db=voz" target="_blank"><i class='icon-folder-remove'></i> Process  Removals</a></li>
                            <li><a href="//cron.blr.pw/update_stats.php?db=voz" target="_blank"><i class='icon-chart'></i> Update Stats</a></li>
                            
                            <li>
                                <a href="#"><i class="icon-history"></i>Delete Old Postings</a>
                                <ul>
                                    <li><a href="//cron.blr.pw/del_old_posts.php?db=em" target="_blank"> From EM</a></li>
                                    <li><a href="//cron.blr.pw/del_old_posts.php?db=bs" target="_blank"> From BS</a></li>
                                    <li><a href="//cron.blr.pw/del_old_posts.php?db=mt" target="_blank"> From MT</a></li>
                                    <li><a href="//cron.blr.pw/del_old_posts.php?db=ts" target="_blank"> From TS</a></li>
                                    <li><a href="//docbin.escortstats.com/cleanup-old-resumes.php" target="_blank"> Delete Old Docbin Resumes</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="#"><i class="icon-alarm-check"></i></i> Scrape Cities</a>
                                <ul>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=13" target="_blank"> EM Phoenix</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=26" target="_blank"> EM Fresno</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=127" target="_blank"> EM Des Moines</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=213" target="_blank"> EM Las Vegas</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=209" target="_blank"> EM Grand Island</a></li>
                                    <li class="navigation-divider"></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=13" target="_blank"> MT Phoenix</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=26" target="_blank"> MT Fresno</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=127" target="_blank"> MT Des Moines</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=213" target="_blank"> MT Las Vegas</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=209" target="_blank"> MT Grand Island</a></li>
                                    <li class="navigation-divider"></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=13" target="_blank"> BS Phoenix</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=26" target="_blank"> BS Fresno</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=127" target="_blank"> BS Des Moines</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=213" target="_blank"> BS Las Vegas</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=209" target="_blank"> BS Grand Island</a></li>
                                    <li class="navigation-divider"></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=13" target="_blank"> TS Phoenix</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=26" target="_blank"> TS Fresno</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=127" target="_blank"> TS Des Moines</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=213" target="_blank"> TS Las Vegas</a></li>
                                    <li><a href="//cron.blr.pw/scrape_cities.php?db=em&loc_id=209" target="_blank"> TS Grand Island</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    </ul>
                </div>
            </div>
		</div>
	</div>
</div>