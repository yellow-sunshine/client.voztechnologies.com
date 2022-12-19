<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);

session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once(__DIR__.'/get_settings.php');
# Include site functions
include_once(__DIR__.'/functions.v2.php');

# Get all of the voz settings from the Database
include_once(INCLUDES.'/voz_settings.php');

// Check and see if we are comming back with a new token after authorizing the app.
if($_GET['code'] && !$_GET['authComplete'] ){


	# This is TD Ameritrade's auth code coming back to us
	$code = $conn->real_escape_string($_GET['code']);
	$rs=$conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$code."' WHERE variable='tda_auth_code' LIMIT 1");
	if(!$rs){
		print "Failed to update DB with new tda_auth_code. Could not continue authorization";
		exit();
	}

	$tda->auth_token($code); # Get the Auth and refresh token. This will also refresh memcache for us and update our DB with the new info


	# Function to get the URL without the query string
	function currentUrl( $trim_query_string = false ) {
		$pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		if( ! $trim_query_string ) {
			return $pageURL;
		} else {
			$url = explode( '?', $pageURL );
			return $url[0];
		}
	}

	# Fully Authenticated. Let's go back to the app
 	header('Location: '.currentUrl($_SERVER['REQUEST_URI']).'?authComplete=1');
	exit();
}

# Make sure only authenticated users can view this page.
# Trading platforms should already be done above and the url should have redirected to itself without the code variables in order to get here
if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=tweets');
	exit();
}

$PAGE_NAME = 'Authentication';

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
			}else if(remresponse[0] == 'refreshsuccess'){
				document.getElementById('tda_refresh_token').value = remresponse[1];
				document.getElementById('tda_access_token').value = remresponse[2];
				colorSuccess('tda_refresh_token');
				colorSuccess('tda_access_token');
				document.getElementById('tda_refresh_now_Btn').innerHTML="<i class='icon-spinner9'></i>";
			}else{
				alert("Update failed"+remresponse);
				colorFail(remresponse[1]);
			}
		}
	}
}
function tda_refresh_token(){
	document.getElementById('tda_refresh_now_Btn').innerHTML="<span class='spinner'><i class='icon-spinner6'></i></span>";
	var p = 'jg26tUYG25ruyty4Fg6u6hb72buyDGjO';
	var poststr = "&p=" + encodeURI(p);
	makePOSTRequest('<?php print BASE_URL; ?>/optionSniper/tda_refresh_token.php', poststr);
}

function updateVariable(idName){
	var val = document.getElementById(idName);
	var poststr = "&variable=" + encodeURI(idName) + "&value=" + encodeURI(val.value);
	makePOSTRequest('<?php print BASE_URL; ?>/optionSniper/process_settings.php', poststr);
}

function showPass(token='tda_access_token') {
	if(token == 'tda_access_token'){
		var x = document.getElementById("tda_access_token");
		if (x.type === "password") {
			x.type = "text";
			document.getElementById("tda_access_tokenBtn").innerHTML='Hide';
		} else {
			x.type = "password";
			document.getElementById("tda_access_tokenBtn").innerHTML='Show';
		}
	}else{
		var x = document.getElementById("tda_refresh_token");
		if (x.type === "password") {
			x.type = "text";
			document.getElementById("tda_refresh_tokenBtn").innerHTML='Hide';
		} else {
			x.type = "password";
			document.getElementById("tda_refresh_tokenBtn").innerHTML='Show';
		}
	}
}
</script>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">

						<?php $active='auth'; require_once($_SERVER['DOCUMENT_ROOT'].'optionSniper/tab_nav.php'); ?>

                    </ul>


                    <div class="tab-content">











					<div class="col-md-6" id="cont-<?php print $sitesrow['site_id']; ?>">
						<div class="panel invoice-grid" id="site-<?php print $sitesrow['site_id']; ?>">
							<h6 class="text-semibold no-margin-top" style='background-color:#ECEDF0; padding:4px 4px 4px 10px; border-top:5px solid #DFE1E6; width:100%; border-bottom:1px solid #DFE1E6;'>
								<div style='text-align:left; width:65%; display:inline-block;'>
									<img width='220px' height='30px' src='/optionSniper/td-ameritrade.png'>
								</div>
								<div style='display:inline-block; float:right;'>
									<?php
									if($_GET['authComplete']==1){
										# We got an auth code and have done some redirecting. Here we can display a message that it was a success
										print "<h2 style='color:#458640;'>Auth was updated successfully!</h2>";
									}
									?>
								</div>
							</h6>
							<div class="panel-body">
							<div class="row">
									<div class="col-sm-3 mt-10">
										Auth Expiration
									</div>
									<div class="col-sm-6 mt-5">
										<span class='badge badge-warning position-left'>
											<?php
											//Convert them to timestamps.
											$expireDay= new DateTime(TDA_AUTH_CODE_DATE);
											$expireDay->modify('+90 day');
											$expireDay->modify('-1 min');
											$theDay=new DateTime('now');
											$timeToEnd=$expireDay->diff($theDay);
											print ltrim($timeToEnd->format('%R%a days'),"-");
											?>
										</span>
									</div>
									<div class="col-sm-3">
										<script language="javascript">
											$( document ).ready(function() {
												$("button#tda_UpdateBTN").on('click',function(){
													window.open('https://auth.tdameritrade.com/auth?response_type=code&redirect_uri=<?php print urlencode(TDA_AUTH_CALLBACK_URI); ?>&client_id=<?php print urlencode(TDA_AUTH_CLIENT_ID); ?>', '_self');
												});
											});
										</script>
										<button type="button" id='tda_UpdateBTN' class="btn bg-primary btn-xs">
											Sign In
										</button>
										<i class="icon-info22 position-right cursor-pointer text-primary"
											data-popup="popover" data-html="true" data-placement="bottom"
											data-content="Clicking Sign in will bring you to a TD Ameritrade login where you can log into the account that will be used for trading">
										</i>
									</div>
								</div>




								<div class="row mt-15">
									<div class="col-sm-3 mt-10">
										Auth Token
										<i class="icon-info22 position-right cursor-pointer text-primary"
											data-popup="popover" data-html="true" data-placement="bottom"
											data-content="This is required to bring the TD Ameritrade API, the Account used to trade, and this trading platform together. It is auto created after logging to TD Ameritrade using the sign in button">
										</i>
									</div>
									<div class="col-sm-6">
										<div class="form-group has-feedback has-feedback-left">
											<input type="password" value='<?php print TDA_ACCESS_TOKEN; ?>' class="form-control input" id="tda_access_token" >
											<div class="form-control-feedback" ><i class="icon-key"></i></div>
										</div>
									</div>
									<div class="col-sm-3">
										<button type="button" id='tda_access_tokenBtn' onclick="showPass('tda_access_token');" class="btn btn-primary">
											Show
										</button>
									</div>
								</div>





								<div class="row">
									<div class="col-sm-3 mt-10">
										Refresh Token
										<i class="icon-info22 position-right cursor-pointer text-primary"
											data-popup="popover" data-html="true" data-placement="bottom"
											data-content="This is required to bring the TD Ameritrade API, the Account used to trade, and this trading platform together automatically by the system as needed. It will be auto filled after Authentication  is completed. We can manual refresh it here if we need to.">
										</i>
									</div>
									<div class="col-sm-6">
										<div class="form-group has-feedback has-feedback-left">
											<input type="password" value='<?php print TDA_REFRESH_TOKEN; ?>' class="form-control input" id="tda_refresh_token" >
											<div class="form-control-feedback" ><i class="icon-key"></i></div>
										</div>
									</div>
									<div class="col-sm-3">
										<button type="button" id='tda_refresh_tokenBtn' onclick="showPass('tda_refresh_token');" class="btn btn-primary">
											Show
										</button>
										<button type="button" id='tda_refresh_now_Btn' onclick="tda_refresh_token();" class="btn btn-primary btn-icon">
											<i class="icon-spinner9"></i>
										</button>
									</div>
								</div>






								<div class="row">
									<div class="col-sm-3 mt-10">
										Callback URI
										<i class="icon-info22 position-right cursor-pointer text-primary"
											data-popup="popover" data-html="true" data-placement="bottom"
											data-content="The the location of the auth script TD Ameritrade will deliver our Auth Token to. It should be changed unless the TD Ameritrade app is also updated">
										</i>
									</div>
									<div class="col-sm-6">
										<div class="form-group has-feedback has-feedback-left">
											<input type="text" value='<?php print TDA_AUTH_CALLBACK_URI; ?>' class="form-control input" id="tda_auth_callback_uri" placeholder="e.g http://trade.example.com/myFile.php">
											<div class="form-control-feedback"><i class="icon-link2"></i></div>
										</div>
									</div>
									<div class="col-sm-3">
										<button type="button"onclick="updateVariable('tda_auth_callback_uri');" id='tda_updateButton' class="btn btn-primary">
											Update
										</button>
									</div>
								</div>




								<div class="row">
									<div class="col-sm-3 mt-10">
										Client ID
										<i class="icon-info22 position-right cursor-pointer text-primary"
											data-popup="popover" data-html="true" data-placement="bottom"
											data-content="The username of the TD Ameritrade API app that we are using. You can get it under 'My Apps' on the TD Ameritrade developer portal <a href='https://developer.tdameritrade.com/user/me/apps' target='_blank'>here</a> ">
										</i>
									</div>
									<div class="col-sm-6">
										<div class="form-group has-feedback has-feedback-left">
											<input type="text" value='<?php print TDA_AUTH_CLIENT_ID; ?>' class="form-control input" id="tda_auth_client_id" placeholder="e.g CAPTINHAPPY@AMER.OAUTHAP">
											<div class="form-control-feedback"><i class="icon-user-check"></i></div>
										</div>
									</div>
									<div class="col-sm-3">
										<button type="button" onclick="updateVariable('tda_auth_client_id');" id='updateButton' class="btn btn-primary">
											Update
										</button>
									</div>
								</div>

								<div class="row">
									<div class="col-sm-3 mt-10">
										Account #
										<i class="icon-info22 position-right cursor-pointer text-primary"
											data-popup="popover" data-html="true" data-placement="bottom"
											data-content="This is the TD Ameritrade account number that will be used to buy all trades. You can get it on the TD Ameritrade website by clicking 'Show' next to the account <a href='https://invest.ameritrade.com/grid/p/site#r=home' target='_blank'>here</a>">
										</i>
									</div>
									<div class="col-sm-6">
										<div class="form-group has-feedback has-feedback-left">
											<input type="text" value='<?php print TDA_ACCOUNT_NUMBER; ?>' class="form-control input" id="tda_account_number" placeholder="e.g 100444096">
											<div class="form-control-feedback"><i class="icon-user-check"></i></div>
										</div>
									</div>
									<div class="col-sm-3">
										<button type="button" onclick="updateVariable('tda_account_number');" id='updateButton' class="btn btn-primary">
											Update
										</button>
									</div>
								</div>


								<div class="row">
									<div class="col-sm-12 mt-12">
										<h3><a href='https://quotes.voztechnologies.com/optionSniper/auth.php?p=sdcyh87347y9hbecIJHBuhygbd'>Update Remote Quote API Auth</a></h3>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- End TD Ameritrade  -->



















					<!--div class="col-md-6" id="cont-<?php print $sitesrow['site_id']; ?>">
						<div class="panel invoice-grid" id="site-<?php print $sitesrow['site_id']; ?>">
							<h6 class="text-semibold no-margin-top" style='background-color:#ECEDF0; padding:4px 4px 4px 10px; border-top:5px solid #DFE1E6; width:100%; border-bottom:1px solid #DFE1E6;'>
								<div style='text-align:left; width:65%; display:inline-block;'>
									<img height='30px' width='160px' src='/optionSniper/etrade.png?refresh=force'>
								</div>
								<div style='display:inline-block; float:right;'>
									<?php
									if($_GET['authComplete']==1){
										# We got an auth code and have done some redirecting. Here we can display a message that it was a success
										print "<h2 style='color:#458640;'>Auth was updated successfully!</h2>";
									}
									?>
								</div>
							</h6>
							<div class="panel-body">
							<div class="row">
									<div class="col-sm-3 mt-10">
										Auth Expiration
									</div>
									<div class="col-sm-6 mt-5">

									</div>
									<div class="col-sm-3">

									</div>
								</div>


								<div class="row">
									<div class="col-sm-3 mt-10">
										Account #
									</div>
									<div class="col-sm-6">
										<div class="form-group has-feedback has-feedback-left">
											<input type="text" value='<?php print ETRADE_ACCOUNT_NUMBER; ?>' class="form-control input" id="etrade_account_number" placeholder="e.g 100444096">
											<div class="form-control-feedback"><i class="icon-user-check"></i></div>
										</div>
									</div>
									<div class="col-sm-3">
										<button type="button" onclick="updateVariable('etrade_account_number');" id='updateButton' class="btn btn-primary">
											Update
										</button>
									</div>
								</div>


							</div>
						</div>
					</div--> <!-- End TD Etrade  -->



























                </div>
            </div>
        </div>
	</div>
</div>


<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>