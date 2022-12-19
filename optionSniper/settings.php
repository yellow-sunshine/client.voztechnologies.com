<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once(__DIR__.'/get_settings.php');

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=tweets');
	exit();
}

# Include site functions
include_once(__DIR__.'/functions.v2.php');

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

$(function() {
// Switchery toggles
if (Array.prototype.forEach) {
    var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
    elems.forEach(function(html) {
        var switchery = new Switchery(html);
    });
}
else {
    var elems = document.querySelectorAll('.switchery');

    for (var i = 0; i < elems.length; i++) {
        var switchery = new Switchery(elems[i]);
    }
}
});

function updateVariable(idName){
    var val = document.getElementById(idName);
    if(idName=='gather_tweets_market_closed'){
        if($(val).prop("checked")){
            val.value = 1;
        }else{
            val.value = 0;
        }
    }

	var poststr = "&variable=" + encodeURI(idName) + "&value=" + encodeURI(val.value);
	makePOSTRequest('<?php print BASE_URL; ?>/optionSniper/process_settings.php', poststr);
}
</script>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
						<?php $active='settings'; require_once($_SERVER['DOCUMENT_ROOT'].'optionSniper/tab_nav.php'); ?>

                    </ul>

                    <div class="tab-content">
                    	<div class="tab-pane active" id="left-icon-tab1">






							<h2>General Settings</h2>

                            <!-- ############# SETTING ############# -->
                            <div class="row">
                                <div class="col-md-12">
                                	<?php
										if(date("Y-m-d H:i:s", strtotime(PAUSE_TWEET_COLLECTION_TIME_DATE) + PAUSE_TWEET_COLLECTION_TIME) > date("Y-m-d H:i:s")){
											print "Tweet collection currently paused untill ".date("Y-m-d H:i:s", strtotime(PAUSE_TWEET_COLLECTION_TIME_DATE) + PAUSE_TWEET_COLLECTION_TIME);
											$pauseNum = PAUSE_TWEET_COLLECTION_TIME;
										}
									?>
                                </div>
                            </div>

                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Pause all tweet collection for X seconds</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="You can pause tweet collection here. Tweet collection will auto start when the number of seconds has ran out. To start up tweet collection before the end enter 1 and click pause now. 300=5min, 1800=30min, 10800=3hrs, 86400=1day, 259200=3days.">
                                    </i>
                                    <br />(Starts on submit)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print $pauseNum; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="pause_tweet_collection_time" placeholder="86400 = 24hr">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button" onclick="updateVariable('pause_tweet_collection_time');" id='updateButton' class="btn btn-primary"> Pause Now</button>
                                    </div>
                                </div>
                            </div>





                            <!-- ############# SETTING ############# -->
                            <div class="row">
                                <div class="col-md-12">
                                	<?php
										$pauseNum = NULL;
										if(date("Y-m-d H:i:s", strtotime(PAUSE_TRADING_TIME_DATE) + PAUSE_TRADING_TIME) > date("Y-m-d H:i:s")){
											print "Trading currently paused untill ".date("Y-m-d H:i:s", strtotime(PAUSE_TRADING_TIME_DATE) + PAUSE_TRADING_TIME);
											$pauseNum = PAUSE_TRADING_TIME;
										}
									?>
                                </div>
                            </div>

                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Pause trading for X seconds</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="You can pause all trading here. Trading will auto start when the number of seconds has ran out and a trade become available. To start up trading again before the end enter 1 and click pause now. 300=5min, 1800=30min, 10800=3hrs, 86400=1day, 259200=3days.">
                                    </i>
                                    <br />(Starts on submit)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print $pauseNum; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="pause_trading_time" placeholder="86400 = 24hr">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button" onclick="updateVariable('pause_trading_time');" id='updateButton' class="btn btn-primary"> Pause Now</button>
                                    </div>
                                </div>
                            </div>







                            <!-- ############# SETTING ############# -->

                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Trade Amount</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="This is the amount that will be used for each buy order. No other metrics are used other than this in deciding how much to spend on a trade. If the account balance is less than this number, the trade will not proceed.">
                                    </i>
                                    <br />(USD to use for each trade)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print TRADE_AMOUNT_USD; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="trade_amount_usd" placeholder="i.g. 2000">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-coins"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('trade_amount_usd');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>







                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Reserve Funds</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="THis amount must remain in the account after a trade or the trade will not be allowed to proceed. Do not enter commas or $.">
                                    </i>
                                    <br />(Min funds not in a position in USD. No $ or commas)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print RESERVE_FUNDS_USD; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="reserve_funds_usd" placeholder="i.g. 17500">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-piggy-bank"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('reserve_funds_usd');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>








                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Symbols to trade</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="All symbols in tweets will be collected however, these will be the only symbols that will be traded. Lowercase, no spaces option but it must be comma separated values">
                                    </i>
                                    <br />(Comma separated values: tyc, wcom, ene, hls)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print $optionsniper_settings['trade_symbols']['value']; ?>' class="form-control input" id="trade_symbols" placeholder="tyc, wcom, ene, hls">
                                        <div class="form-control-feedback"><i class="icon-cabinet"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button" onclick="updateVariable('trade_symbols');" id='updateButton' class="btn btn-primary"> Update</button>
                                    </div>
                                </div>
                            </div>











                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Symbols for historic data</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="Symbols to gather historic data for. Only for data mining purposes.">
                                    </i>
                                    <br />(Comma separated values: tyc, wcom, ene, hls)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print $optionsniper_settings['historic_symbols']['value']; ?>' class="form-control input" id="historic_symbols" placeholder="tyc, wcom, ene, hls">
                                        <div class="form-control-feedback"><i class="icon-cabinet"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button" onclick="updateVariable('historic_symbols');" id='updateButton' class="btn btn-primary"> Update</button>
                                    </div>
                                </div>
                            </div>











                            <!-- ############# SETTING ############# -->

                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Gather tweets when market is closed</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="If it is on, tweets will be gathered even when the market is closed. This uses resources we don't need. It is abusing the twitter API even more so if it is left on there should be a good reason">
                                    </i>
                                    <br />(Attempt to get tweets even though they will not be traded)
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <form class="heading-form" action="#">
                                        <div class="form-group">
                                            <label class="checkbox-switchery">
                                                <input type="checkbox" id='gather_tweets_market_closed' onclick="updateVariable('gather_tweets_market_closed');" class="switchery" <?php if(GATHER_TWEETS_MARKET_CLOSED){print "checked='checked'";}?>>
                                            </label>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-7 col-sm-6">
                                </div>
                            </div>








                            <hr>
							<h2>Buy Thresholds</h2>

                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Max % increase</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="If the option's contract price goes up by this % between the time it was tweeted and the time the trade is being entered then the trade will not go through. This is to prevent us from getting in too late and entering a trade at the top. This should theoretically never trigger because we trade so fast after a tweet.">
                                    </i>
                                    <br />(Max increase allowed before trade)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MAX_OPTION_INCREASE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="max_option_increase" placeholder="e.g 3">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-percent"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('max_option_increase');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Max % Decrease</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="If the option's contract price goes down by this % between the time it was tweeted and the time the trade is being entered then the trade will not go through. Must be a positive number even though it is a decrease. This is to prevent us from getting in on a bad trade that we expected to go up but it shot down seconds after. This should theoretically never trigger because we trade so fast after a tweet.">
                                    </i>
                                    <br />(Max decrease allowed before trade)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MAX_OPTION_DECREASE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="max_option_decrease" placeholder="e.g 3">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-percent"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('max_option_decrease');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>


                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Max strike price allowed</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="Very simple. All strike prices should be less than this. If they are more than this value something is prob wrong and won't work anyways">
                                    </i>
                                    <br />(Strike price must be under this for a trade to be entered)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MAX_STRIKE_PRICE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="max_strike_price" placeholder="e.g 9850">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-coin-dollar"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('max_strike_price');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>



                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Min strike price allowed</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="Very simple. All strike prices should be more than this. If a strike price is very cheap it is prob unstable">
                                    </i>
                                    <br />(Strike price must be over this for a trade to be entered)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MIN_STRIKE_PRICE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="min_strike_price" placeholder="e.g 12">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-coin-dollar"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('min_strike_price');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>


                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Max contract price allowed</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover"
                                        data-html="true"
                                        data-content="The maximum price we are willing to pay for a contract regardless of the situation or symbol. This is to prevent outragously high trades.">
                                    </i>
                                    <br />(Contract price must be under this for a trade to be entered)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MAX_CONTRACT_PRICE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="max_contract_price" placeholder="e.g 100">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-coin-dollar"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('max_contract_price');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>


                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Min contract price allowed</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="The minimum price a contrct can be in order to be traded. This si to prevent us from chasing dirt cheap unstable and risky contracts that are crashing.">
                                    </i>
                                    <br />(Contract price must be over this for a trade to be entered)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MIN_CONTRACT_PRICE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="min_contract_price" placeholder="e.g 0.25">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-coin-dollar"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('min_contract_price');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>



                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Seconds till market close</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="We will not trade if there are only these amount of seconds or less remaining untill the market closes. We want to prevent trades being entered at the end of day and becoming bag holders over night. 300=5min, 900=15min, 1800=30min">
                                    </i>
                                    <br />(No trading if market closes within these amount of sec)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MIN_SEC_TILL_CLOSE; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="min_sec_till_close" placeholder="e.g 1800">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('min_sec_till_close');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>


                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Max seconds to buy after tweet</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="Maximum number of seconds we have to enter a buy after a tweet has gone live. If more than 30 sec there could be some serious movement already. 10 sec or less should be ok however, our system should be much faster">
                                    </i>
                                    <br />(No trading if this number of sec have passed since tweet went live)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print MAX_SEC_AFTER_TWEET; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="max_sec_after_tweet" placeholder="e.g 30">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-watch2"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('max_sec_after_tweet');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>























                            <hr>
							<h2>Auto Sell Thresholds</h2>

                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Auto sell when % gains reach</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="Automatically market sell options that have increased by this percent. This is to lock in our position no matter what happens.">
                                    </i>
                                    <br />(Experimental, currently not implemented)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print AUTO_SELL_MAX_GAIN; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="auto_sell_max_gain" placeholder="e.g 27">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-percent"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('auto_sell_max_gain');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>





                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Auto sell when % losses reach</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="Automatically market sell options that have decreased by this percent. This is to limit in our losses.">
                                    </i>
                                    <br />(Experimental, currently not implemented)
                                </div>
                                <div class="col-md-5 col-sm-5">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print AUTO_SELL_MAX_LOSS; ?>' class="form-control input" onkeyup="AcceptDigits(this);" onblur="AcceptDigits(this);" id="auto_sell_max_loss" placeholder="e.g 3">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);"><i class="icon-percent"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('auto_sell_max_loss');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>





                            <hr>
							<h2>Alerts</h2>

                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Alert emails</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="All emails on this list will receive trade alerts. Must be comma separated and valid emails">
                                    </i>
                                    <br />(Comma Separated Values)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?=ALERT_EMAILS_CS; ?>' class="form-control input" id="alert_emails" placeholder="e.g donaldtrump@whitehouse.gov,OsamBinLaden@caveHotel.com">
                                        <div class="form-control-feedback" ><i class="icon-envelop5"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                        <button type="button"
                                                onclick="updateVariable('alert_emails');"
                                                id='updateButton'
                                                class="btn btn-primary">
                                                Update
                                        </button>
                                    </div>
                                </div>
                            </div>




                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Alert phone numbers</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="All phone numbers on this list will receive trade alerts. They must include the country code in fron of the number. No dashes, must be comma separated">
                                    </i>
                                    <br />(Comma Separated Values, no dashes)
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print ALERT_PHONES_CS; ?>' class="form-control input" id="alert_phones" placeholder="e.g 5552128585,3105556238">
                                        <div class="form-control-feedback" ><i class="icon-phone"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                        <button type="button"
                                                onclick="updateVariable('alert_phones');"
                                                id='updateButton'
                                                class="btn btn-primary">
                                                Update
                                        </button>
                                    </div>
                                </div>
                            </div>





                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Twilio Account SID</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="The SID of the Twilio account we are using to send alerts. <A href='https://www.twilio.com/console' target='_blank'>Find it on twilio here<a>">
                                    </i>
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" value='<?php print TWILIO_ACCOUNT_SID; ?>' class="form-control input" id="twilio_account_sid" placeholder="e.g 5552128585,3105556238">
                                        <div class="form-control-feedback" ><i class="icon-user-check"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button"
                                    		onclick="updateVariable('twilio_account_sid');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>

                            <script>
                            function showPass(token='twilio_auth_token') {
                                if(token == 'twilio_auth_token'){
                                    var x = document.getElementById("twilio_auth_token");
                                    if (x.type === "password") {
                                        x.type = "text";
                                        document.getElementById("twilio_auth_tokenBtn").innerHTML='Hide';
                                    } else {
                                        x.type = "password";
                                        document.getElementById("twilio_auth_tokenBtn").innerHTML='Show';
                                    }
                                }
                            }
                            </script>
                            <!-- ############# SETTING ############# -->
                            <div class="row">
                            	<div class="col-md-4 col-sm-5">
                            		<strong>Twilio Auth Token</strong>
                                    <i class="icon-info22 position-right cursor-pointer text-primary"
                                        data-popup="popover" data-html="true" data-placement="bottom"
                                        data-content="The Auth token of the Twilio account we are using to send alerts. <A href='https://www.twilio.com/console' target='_blank'>Find it on twilio here<a>">
                                    </i>
                                </div>
                                <div class="col-md-5 col-sm-4">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="password" value='<?php print TWILIO_AUTH_TOKEN; ?>' class="form-control input" id="twilio_auth_token" placeholder="e.g 5552128585,3105556238">
                                        <div class="form-control-feedback" ><i class="icon-key"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-2">
                                    <div class="form-group">
                                    <button type="button" id='twilio_auth_tokenBtn' onclick="showPass('twilio_auth_token');" class="btn btn-primary">
											Show
									</button>
                                    <button type="button"
                                            onclick="updateVariable('twilio_auth_token');"
                                    		id='updateButton'
                                    		class="btn btn-primary">
                                    		Update
                                    </button>
                                    </div>
                                </div>
                            </div>


                            <hr>
							<h3><a href='/optionSniper/auth.php'>Update broker and auth settings here</a></h3>
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