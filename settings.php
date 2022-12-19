<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=settings');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

# Get all of the voz settings from the DB
include_once(INCLUDES.'/voz_settings.php');

$PAGE_NAME = 'Settings';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

?>
<script type="text/javascript" src="/assets/js/core/voz.js"></script>
<script language="javascript">
function alertContents() {
	if(http_request.readyState == 4) {
		if(http_request.status == 200) {
			remresponse = http_request.responseText.split("|@|");
			if(remresponse[0] == 'success'){
				colorSuccess(remresponse[1]);
			}else{
				alert("Update failed");
				colorFail(remresponse[1]);
			}
		}
	}
}


function updateVariable(idName){
	var val = document.getElementById(idName);
	var poststr = "&variable=" + encodeURI(idName) + "&value=" + encodeURI(val.value);
	makePOSTRequest('<?php print BASE_URL; ?>/process_settings.php', poststr);
}
</script>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#left-icon-tab1" data-toggle="tab"><i class="icon-menu7 position-left"></i> Scraping</a></li>
                        <li><a href="#left-icon-tab2" data-toggle="tab"><i class="icon-menu7 position-left"></i> Old Post Removal</a></li>
                        <li><a href="#left-icon-tab3" data-toggle="tab"><i class="icon-menu7 position-left"></i> Alerts</a></li>
                        <li><a href="#left-icon-tab4" data-toggle="tab"><i class="icon-menu7 position-left"></i> General Settings</a></li>
                        <li><a href="#left-icon-tab5" data-toggle="tab"><i class="icon-menu7 position-left"></i> Memcache</a></li>
                    </ul>



                    <div class="tab-content">
                    	<div class="tab-pane active" id="left-icon-tab1">
                            <div class="row">
                            	<div class="col-md-3">
                            		
                                </div>
                                <div class="col-md-3">
                                	<?php
										if(date("Y-m-d H:i:s", strtotime(BP_SCRAPING_PAUSE_DATE) + BP_SCRAPING_PAUSE_TIME) > date("Y-m-d H:i:s")){
											print "Scraping currently paused untill ".date("Y-m-d H:i:s", strtotime(BP_SCRAPING_PAUSE_DATE) + BP_SCRAPING_PAUSE_TIME);
											$pauseNum = BP_SCRAPING_PAUSE_TIME;
										}
									?>
                                </div>
                            </div>
                            
                             <div class="row">
                            	<div class="col-md-3">
                            		<strong>Pause all scraping for X seconds</strong>
                                    <br />(Starts on submit)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print $pauseNum; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="bp_scraping_pause_time" placeholder="86400 = 24hr">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                    <button type="button" onclick="updateVariable('bp_scraping_pause_time');" id='updateButton' class="btn btn-primary"> Pause Now</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Max threads threshold</strong><br />
                                    (Max db connections allowed b4 scraping)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('no_scrape_thread_count_threshold');" id="no_scrape_thread_count_threshold" value='<?php print NO_SCRAPE_THREAD_COUNT_THRESHOLD; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Ban proxy IP after 1st RSS scrape fail</strong><br />
                                    (IP tried twice before ban)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('scrape_fail_rss_1st');" id="scrape_fail_rss_1st" value='<?php print SCRAPE_FAIL_RSS_1ST; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Ban proxy IP after 2nd RSS scrape fail</strong><br />
                                    (IP tried only once before ban)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('scrape_fail_rss_2nd');" id="scrape_fail_rss_2nd" value='<?php print SCRAPE_FAIL_RSS_2ND; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Ban proxy IP when blacklisted by BP</strong><br />
                                    (When BP returns an error, not content)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('scrape_fail_bp_blacklist');" id="scrape_fail_bp_blacklist" value='<?php print SCRAPE_FAIL_BP_BLACKLIST; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Ban proxy when image page download fail</strong><br />
                                    (This is the image page with img links)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('scrape_fail_image_page');" id="scrape_fail_image_page" value='<?php print SCRAPE_FAIL_IMAGE_PAGE; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Ban proxy when image file download fail</strong><br />
                                    (Img files are downloaded in binary mode)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('scrape_fail_image_file');" id="scrape_fail_image_file" value='<?php print SCRAPE_FAIL_IMAGE_FILE; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <strong>Minimum scraped image size</strong><br />
                                    (Image size in bytes, not kbs)
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('min_scraped_img_size');" id="min_scraped_img_size" value='<?php print MIN_SCRAPED_IMG_SIZE; ?>'>
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-floppy-disk"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            

                            <div class="row">
                            	<div class="col-md-4">
                                    <mark>EM</mark><br />
                                    <div class="row">
                                        <div class="col-md-5">
                                            Max scrape count
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_maximum_scrape');" id="em_maximum_scrape" value='<?php print EM_MAXIMUM_SCRAPE; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-5">
                                            Scrape Chance (%)
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_scrape_load');" id="em_scrape_load" value='<?php print EM_SCRAPE_LOAD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                
                            	<div class="col-md-4">
                                    <mark>MT</mark><br />
                                    <div class="row">
                                        <div class="col-md-5">
                                            Max scrape count
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_maximum_scrape');" id="mt_maximum_scrape" value='<?php print MT_MAXIMUM_SCRAPE; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-5">
                                            Scrape Chance (%)
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_scrape_load');" id="mt_scrape_load" value='<?php print MT_SCRAPE_LOAD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-4">
                                    <mark>BS</mark><br />
                                    <div class="row">
                                        <div class="col-md-5">
                                            Max scrape count
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_maximum_scrape');" id="bs_maximum_scrape" value='<?php print BS_MAXIMUM_SCRAPE; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-5">
                                            Scrape Chance (%)
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_scrape_load');" id="bs_scrape_load" value='<?php print BS_SCRAPE_LOAD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                
                            	<div class="col-md-4">
                                    <mark>TS</mark><br />
                                    <div class="row">
                                        <div class="col-md-5">
                                            Max scrape count
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_maximum_scrape');" id="ts_maximum_scrape" value='<?php print TS_MAXIMUM_SCRAPE; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-5">
                                            Scrape Chance (%)
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_scrape_load');" id="ts_scrape_load" value='<?php print TS_SCRAPE_LOAD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    
						
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                       
                        
                        <div class="tab-pane" id="left-icon-tab2">
                            <div class="row">
                            	<div style='text-align:center'>
									<h2>Auto delete posts with the least amount of visits for it's age</h2>
									<span style='font-size:0.8em;'>
										Each hour the system attempts to delete ads. Over a day, thousands can be deleted. 
										<br />
										<div style='text-align:left; width:80%; margin: 0 auto;'>
											<h3>To find out how many ads are being deleted you can:</h3>
											<script>
												function showhidediagram(){
													if(document.getElementById("recordsSS").style.display=='none'){
														document.getElementById("recordsSS").style.display='block';
													}else{
														document.getElementById("recordsSS").style.display='none';
													}
												}
											</script>
											<ol>
												<li>See "Old Posts Deleted Today" in the sidebar for a list of ALL posts deleted from <strong>ALL databases</strong></li>
												<li>Go to <a href="//<?php print DOMAIN; ?>/records.php">records</a> (<span style='cursor:pointer; cursor:hand; color:#0393D9;' onclick='showhidediagram();'>Show/Hide Diagram</span>)
													<ol>
														<li>Note the number of posts and the date.</li>
														<li>Click the "Update Memcache" link.</li>
														<li>See the number of posts, and subtract it from the number of posts in the 1st step.</li>
														<li>This is the amount of posts deleted since the date in the 1st step</li>
														<li>Change Databases and repeat steps 1-4</li>
													</ol>
													<img style='display:none;' id='recordsSS' src='/recordsSS.jpg'>
												</li>
											</ol>
										</div>
										<div style='text-align:left; width:80%; margin: 0 auto;'>
											<h3>To delete more or less posts:</h3>
											<script>
												function showhidediagram(){
													if(document.getElementById("recordsSS").style.display=='none'){
														document.getElementById("recordsSS").style.display='block';
													}else{
														document.getElementById("recordsSS").style.display='none';
													}
												}
											</script>
											<ul>
												<li>Days Not viewed</li>
												<ol>
													<li>Deletes postings not viewed in the number of days <strong>AND</strong> has less pageviews than specified</li>
													<li>Raise the number to delete less, lower to delete more</li>
												</ol>
												<li>In ___ months</li>
												<ol>
													<li>Deletes postings viewed less than the number specified <strong>AND</strong> has not been viewed as specified by "Days not viewed"</li>
													<li>Raise the number to delete more, lower to delete less</li>
												</ol>
												</li>
											</ol>
										</div>
									</span>
								</div>
                           		<hr>
                            	<div class="col-md-3">
                                    <mark>EM</mark> Delete posts with pageviews less than<br />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_last_view_is_old');" id="em_last_view_is_old" value='<?php print EM_LAST_VIEW_IS_OLD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Days Not Viewed</strong>
                                        </div>
                                     </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete336');" id="em_delete336" value='<?php print EM_DELETE336; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 weeks
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete672');" id="em_delete672" value='<?php print EM_DELETE672; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 month
                                        </div>
                                    </div>
                                    <div class="row">
                                         <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete1460');" id="em_delete1460" value='<?php print EM_DELETE1460; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete2160');" id="em_delete2160" value='<?php print EM_DELETE2160; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 3 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete3600');" id="em_delete3600" value='<?php print EM_DELETE3600; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 5 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete5760');" id="em_delete5760" value='<?php print EM_DELETE5760; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 8 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete7200');" id="em_delete7200" value='<?php print EM_DELETE7200; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 10 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete8640');" id="em_delete8640" value='<?php print EM_DELETE8640; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 year
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete10080');" id="em_delete10080" value='<?php print EM_DELETE10080; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1yr 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('em_delete17280');" id="em_delete17280" value='<?php print EM_DELETE17280; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 years
                                        </div>
                                    </div>
                                </div>
                                
                                
                            	<div class="col-md-3">
                                    <mark>MT</mark>  Delete posts with pageviews less than<br />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_last_view_is_old');" id="mt_last_view_is_old" value='<?php print MT_LAST_VIEW_IS_OLD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Days Not Viewed</strong>
                                        </div>
                                     </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete336');" id="mt_delete336" value='<?php print MT_DELETE336; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 weeks
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete672');" id="mt_delete672" value='<?php print MT_DELETE672; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 month
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete1460');" id="mt_delete1460" value='<?php print MT_DELETE1460; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete2160');" id="mt_delete2160" value='<?php print MT_DELETE2160; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 3 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete3600');" id="mt_delete3600" value='<?php print MT_DELETE3600; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 5 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete5760');" id="mt_delete5760" value='<?php print MT_DELETE5760; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 8 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete7200');" id="mt_delete7200" value='<?php print MT_DELETE7200; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 10 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete8640');" id="mt_delete8640" value='<?php print MT_DELETE8640; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 year
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete10080');" id="mt_delete10080" value='<?php print MT_DELETE10080; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1yr 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('mt_delete17280');" id="mt_delete17280" value='<?php print MT_DELETE17280; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 years
                                        </div>
                                    </div>
                            </div>
                            
                            <div class="row">
                            	<div class="col-md-3">
                                    <mark>BS</mark> Delete posts with pageviews less than<br />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_last_view_is_old');" id="bs_last_view_is_old" value='<?php print BS_LAST_VIEW_IS_OLD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Days Not Viewed</strong>
                                        </div>
                                     </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete336');" id="bs_delete336" value='<?php print BS_DELETE336; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 weeks
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete672');" id="bs_delete672" value='<?php print BS_DELETE672; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 month
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete1460');" id="bs_delete1460" value='<?php print BS_DELETE1460; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete2160');" id="em_scrape_load" value='<?php print BS_DELETE2160; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 3 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete3600');" id="bs_delete3600" value='<?php print BS_DELETE3600; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 5 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete5760');" id="bs_delete5760" value='<?php print BS_DELETE5760; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 8 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete7200');" id="bs_delete7200" value='<?php print BS_DELETE7200; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 10 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete8640');" id="bs_delete8640" value='<?php print BS_DELETE8640; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 year
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete10080');" id="bs_delete10080" value='<?php print BS_DELETE10080; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1yr 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('bs_delete17280');" id="bs_delete17280" value='<?php print BS_DELETE17280; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 years
                                        </div>
                                    </div>
                                </div>
                                
                                
                            	<div class="col-md-3">
                                    <mark>TS</mark> Delete posts with pageviews less than<br />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_last_view_is_old');" id="ts_last_view_is_old" value='<?php print TS_LAST_VIEW_IS_OLD; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Days Not Viewed</strong>
                                        </div>
                                     </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete336');" id="ts_delete336" value='<?php print TS_DELETE336; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 weeks
                                        </div>
                                     </div>
                                     <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete672');" id="ts_delete672" value='<?php print TS_DELETE672; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 month
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete1460');" id="ts_delete1460" value='<?php print TS_DELETE1460; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete2160');" id="ts_delete2160" value='<?php print TS_DELETE2160; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 3 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete3600');" id="ts_delete3600" value='<?php print TS_DELETE3600; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 5 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete5760');" id="ts_delete5760" value='<?php print TS_DELETE5760; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 8 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete7200');" id="ts_delete7200" value='<?php print TS_DELETE7200; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 10 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete8640');" id="ts_delete8640" value='<?php print TS_DELETE8640; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1 year
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete10080');" id="ts_delete10080" value='<?php print TS_DELETE10080; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 1yr 2 months
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('ts_delete17280');" id="ts_delete17280" value='<?php print TS_DELETE17280; ?>'>
                                                <div class="form-control-feedback" onkeyup="AcceptDigits(this);"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            in 2 years
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                    </div>
                    
                    

					<div class="tab-pane" id="left-icon-tab3">
						<div class="row">
							<div class="col-md-3">
								<strong>Alert Email</strong><br />
								(For alerts when the system is not working correctly)
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<input type="text" class="form-control input" onblur="updateVariable('alertemail');" id="alertemail" value='<?php print ALERTEMAIL; ?>'>
									<div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-envelope"></i></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<strong>Alert Phone 1</strong><br />
								(For alerts when the system is not working correctly)
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('alertphone1');" id="alertphone1" value='<?php print ALERTPHONE1; ?>'>
									<div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-phone"></i></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<strong>Alert Phone 2</strong><br />
								(For alerts when the system is not working correctly)
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('alertphone2');" id="alertphone2" value='<?php print ALERTPHONE2; ?>'>
									<div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-phone"></i></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<strong>Notification Frequency</strong><br />
								(In Minutes)
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<input type="text" class="form-control input" onkeyup="AcceptDigits(this);" onblur="updateVariable('notification_interval');" id="notification_interval" value='<?php print NOTIFICATION_INTERVAL; ?>'>
									<div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
								</div>
							</div>
						</div>
					</div>

						
               

						
              
              
              

					<div class="tab-pane" id="left-icon-tab4">
						<div class="row">
							<div class="col-md-3">
								<strong>Disable All websites</strong><br />
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<select id='disable_all_sites' onChange="updateVariable('disable_all_sites');">
										<?php if(DISABLE_ALL_SITES==1){$selectedOn='selected';}else{$selectedOff='selected';} ?>
										<option value="1" <?php print $selectedOn; ?>>YES</option>
										<option value="0" <?php print $selectedOff; ?>>NO</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<strong>Disable Message</strong><br />
								(Displayed on ALL websites)
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<input style='width:550px;'  type="text" class="form-control input" onblur="updateVariable('disable_all_sites_msg');" id="disable_all_sites_msg" value='<?php print DISABLE_ALL_SITES_MSG; ?>'>
									<div class="form-control-feedback"><i class="icon-pencil6"></i></div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<strong>Throttle Traffic</strong><br />
								Disables a % of traffic when load is high
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<select id='throttle_traffic' onChange="updateVariable('throttle_traffic');">
										<?php if(THROTTLE_TRAFFIC==1){$selectedsipOn='selected';}else{$selectedsipOff='selected';} ?>
										<option value="1" <?php print $selectedsipOn; ?>>YES</option>
										<option value="0" <?php print $selectedsipOff; ?>>NO</option>
									</select>								
								</div>
							</div>
						</div>
						<br />
						<div class="row">
							<div class="col-md-3">
								<strong>Ban removal IP's from all websites</strong><br />
								Removal IP bans last 1 month.
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<select id='ban_removal_ips' onChange="updateVariable('ban_removal_ips');">
										<?php if(BAN_REMOVAL_IPS==1){$selectedripOn='selected';}else{$selectedripOff='selected';} ?>
										<option value="1" <?php print $selectedripOn; ?>>YES</option>
										<option value="0" <?php print $selectedripOff; ?>>NO</option>
									</select>								
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<strong>Ban spam IP's from all websites</strong><br />
								Spam IP bans last 4 months.
							</div>
							<div class="col-md-2">
								<div class="form-group has-feedback has-feedback-left">
									<select id='ban_spam_ips' onChange="updateVariable('ban_spam_ips');">
										<?php if(BAN_SPAM_IPS==1){$selectedsipOn='selected';}else{$selectedsipOff='selected';} ?>
										<option value="1" <?php print $selectedsipOn; ?>>YES</option>
										<option value="0" <?php print $selectedsipOff; ?>>NO</option>
									</select>								
								</div>
							</div>
						</div>
					</div> 
             
             
             
             
             
             
             
             
             
             
             
             
             
             
             
             
					<div class="tab-pane" id="left-icon-tab5">
						<div class="row">
							<?php
								function getMemcacheKeys() {
									$memcache = new Memcache;
									$memcache->connect('127.0.0.1', 11211) 
									   or die ("Could not connect to memcache server");
									$list = array();
									$allSlabs = $memcache->getExtendedStats('slabs');
									$items = $memcache->getExtendedStats('items');
									foreach($allSlabs as $server => $slabs) {
										foreach($slabs AS $slabId => $slabMeta) {
											$cdump = $memcache->getExtendedStats('cachedump',(int)$slabId);
											foreach($cdump AS $keys => $arrVal) {
												if (!is_array($arrVal)) continue;
												foreach($arrVal AS $k => $v) {   
													$lines++;if($lines>1500){break 3;}
													print $k .'<br>';
												}
											}
										}
									}  
									print $lines;
								} 

							getMemcacheKeys();
							?>
						</div>
						
					</div> 
             
             
             
             
             
              
              
               
               
               

                </div>
            </div>
        </div>
	</div>
</div>


<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>