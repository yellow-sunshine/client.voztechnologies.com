<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
$start = microtime(true);
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once(__DIR__.'/functions.v2.php');
require_once(__DIR__.'/TwitterAPIExchange.php');
require_once(__DIR__.'/tda.class.php');
include_once(__DIR__.'/get_settings.php');









// if($_GET['cron']){
// 	exit(); // Stop cron from executing when uncommented
// }






# Check if tweet gathering is paused
if(date("Y-m-d H:i:s", strtotime(PAUSE_TWEET_COLLECTION_TIME_DATE) + PAUSE_TWEET_COLLECTION_TIME) > date("Y-m-d H:i:s")){

	updateLog($log_loc,'Tweet gathering paused until '.date("Y-m-d H:i:s", strtotime(PAUSE_TWEET_COLLECTION_TIME_DATE) + PAUSE_TWEET_COLLECTION_TIME).' PST',1);
	exit();
}










# Checks if the NYSE is open. PST hours for NYSE are 6:30 AM -	1:00 PM
if(!GATHER_TWEETS_MARKET_CLOSED){
	if(
		(date('G') == 6 && preg_replace('/^0/','',date('i')) < 25) // If hour 6, the minute is less than 25
		|| date('G') <= 5 // The hour is less than or equal to 5, 6am-6:25am is handled above
		|| (date('G') == 13 && preg_replace('/^0/','',date('i')) > 5) // If hour 14, the minute is greater than 5
		|| date('G') >= 14 // The hour is greater than or equal to 14, 1:05pm-2pm is handled above
		|| in_array(strtolower(date('D')),array('sat','sun')) // Check Weekends
		|| in_array(
						date('md'),
						array('0101','0115','0219','0330','0528','0704','0903','1225',date('md', strtotime('fourth thursday of november '.date('Y'))) )
					) // Checks for known days the NYSE is closed including Thanksgiving, the 4th Thursday in November
		){
		print "Not gathering Tweets when the market is closed";
		if($_GET['ta']==1 && date('s') < 2 ){
			updateLog($log_loc,"Market closed, continuing to check if market is open...</span>",1);
		}
		exit();
	}
}












# Get PVA Twitter Accounts from the DB and store them in memcache
$key = 'twitter_ids';
$twitter_ids = $memcache->get($key);
//$twitter_ids = FALSE;
if(!$twitter_ids){
	$rs=$conn->query("SELECT account_id, email, oauth_access_token, oauth_access_token_secret, consumer_key, consumer_secret FROM `twitter_sniper`.`twitter_accounts` WHERE enabled=1 LIMIT 200");
	# Put the pva accounts into an array
	while($row = $rs->fetch_assoc()){
		$twitter_ids[$row['account_id']] = array('account_id'=>$row['account_id'],
												'email'=>$row['email'],
												'oauth_access_token'=>$row['oauth_access_token'],
												'oauth_access_token_secret'=>$row['oauth_access_token_secret'],
												'consumer_key'=>$row['consumer_key'],
												'consumer_secret'=>$row['consumer_secret']);
	}
	$memcache->set($key, $twitter_ids, 0, 43200); // Store for 12 hours
	updateLog($log_loc,'Twitter PVAs refreshed in memcache',1);
}
$proxy = avail_proxy(); // Get a proxy to use
$twitter_account = array('proxy_ip'=>$proxy['ipport'],
						'email' => $twitter_ids[$_GET['ta']]['email'],
						'oauth_access_token' => $twitter_ids[$_GET['ta']]['oauth_access_token'],
						'oauth_access_token_secret' => $twitter_ids[$_GET['ta']]['oauth_access_token_secret'],
						'consumer_key' => $twitter_ids[$_GET['ta']]['consumer_key'],
						'consumer_secret' => $twitter_ids[$_GET['ta']]['consumer_secret']
);










# Get a list of all the tweet ID's so we do not process them more than once
$key = 'tweet_ids';
$tweet_ids = $memcache->get($key);
// $tweet_ids = FALSE;
if(!$tweet_ids){
	$rs=$conn->query("SELECT tweet_id FROM `twitter_sniper`.`tweets` LIMIT 200");
	# Put the tweets into an array
	$tweet_ids=array();
	while($row = $rs->fetch_assoc()){
		$tweet_ids[$row['tweet_id']] = "1";
	}
	$memcache->set($key, $tweet_ids, 0, 43200); // Store for 12 hours
	updateLog($log_loc,'Tweet id list refreshed in memcache',1);
}











# Use the TwitterAPIExchange class to authenticate and gather Twitter data
$tweets_requested=11; // It does not always return this amount. It skips over things, maybe it counts but skips quotes and retweets.
$twitter = new TwitterAPIExchange($twitter_account);
if($_GET['ta']==1 && date('s') < 2 ){
	updateLog($log_loc,"<span style='color:#eee;'>GorillaTech checking Twitter another 120 times in 60 sec...</span>",1);
}
$tweets = json_decode($twitter->setGetfield('?screen_name=option_snipper&include_entities=0&trim_user=1&include_rts=0&exclude_replies=1&count='.$tweets_requested)
								->buildOauth("https://api.twitter.com/1.1/statuses/user_timeline.json", "GET")
								->performRequest(),$assoc=TRUE); //include_rts excludes retweets, trim_user cuts out things like profile status and general info about the tweeter, include_entities is URL's hashtags etc
if(!is_array($tweets)){
	updateLog($log_loc,'Could not get tweets from Twitter',1);
	exit();
}else{
	//updateLog($log_loc,count($tweets).' tweets received from Twitter',1);
	$conn->query("UPDATE `twitter_sniper`.`twitter_accounts` SET times_used=times_used+1 WHERE account_id='".$conn->real_escape_string($_GET['ta'])."' LIMIT 1");
}






/*
# This is just to get data from the db as testing. Only un-comment when testing
$rs=$conn->query("SELECT * FROM `twitter_sniper`.`tweets` ORDER BY date_discovered DESC LIMIT 200");
while($tweet = $rs->fetch_assoc()){
	$tweet['id'] = $tweet['tweet_id'];
	$tweet['text'] = $tweet['tweet'];
	$DateTime = new DateTime($tweet['date_tweeted']);
	$DateTime->modify('-4 hours');
	$date_tweeted = $DateTime->format("Y-m-d H:i:s");
*/
# A list of symbols in tech and bio plus a few banks and large companies. This is used fopr searching for stock symbols in tweets if we can't find one with other means
$known_symbols = array('biib','fb','tsla','googl','nflx','amzn','xom','ge','msft','bp','pg','wmt','pfe','hbc','bac','jpm','csco','cvx','vod','tm','intc','wfc','ko','chl','pep','vz','dna','amgn','hd','nok','bcs','dell','axp','usb','caj','hmc','ba','mot','db','orcl','mmm','nsany','tgt','cat','yhoo','trip',
						'dis','sne','txn','ry','lmt','aapl','baba','nvda','crm','txn','sap','aaba','bidu','adbe','ibm','qcom','avgo','mu','vmw','adp','atvi','intu','ctsh','itw','amat','ea','infy','hpq','adi','etn','nxpi','twtr','fisv','sq','wday','lrcx','rht','dxc','hpe','wit','wdc','mchp','ntap','rhi',
						'info','anet','msi','panw','stm','wpp','vrsk','dvmt','vrsn','ca','xlnx','snap','swks','mxim','asml','shop','stx','lll','team','amd','omc','splk','anss','ctxs','ttwo','mrvl','snps','gddy','ssnc','cdns','ipgp','symc','yndx','dbx','ptc','athm','uri','dov','ftnt','mtch','ffiv','jnpr',
						'jnj','pfe','nvs','mrk','abbv','amgn','nvo','abt','gsx','sny','lly','gild','bmy','azn','syk','celg','agn','shpg','vrtx','ilmn','zts','regn','tyo','alxn','dsnky','myl','bmrn','incy','prgo','gen','teva','novo','gild','ig',
						'bidu','spx','jd','fslr','comp','dji','vix','iq');
print "running<br />";
$already_processed=0;$buy=0;$sell=0;$msg=0;$rant=0;
# Loop over all the tweets we found
foreach($tweets as $tweet){
	$DateTime = new DateTime($tweet['created_at']);
	$DateTime->modify('-4 hours');
	$date_tweeted = $DateTime->format("Y-m-d H:i:s");
	$tweet_id = $conn->real_escape_string($tweet['id']);

	# Check if we have already seen this tweet ID
	if(array_key_exists($tweet['id'],$tweet_ids)){
		$already_processed++;
		print "Already Processed tweet ".$tweet['id']."<br />";
		continue;
	}else{
		$tweet_ids[$tweet['id']] = "1";
		$memcache->set('tweet_ids', $tweet_ids, 0, 14400); // Store for 4 hours, not 12 to prevent infinite caching /never refreshing from DB
	}

	$bought=0; // So we can send different emails when a buy tweet is bought or ignored

	/***
	 *
	 *
	 *   Message Tweets
	 *   We will not do anything with these tweets at this time
	 *
	 ***/
	if(preg_match("/^@/",$tweet['text'])){
		$msg++;
		continue;




















	/***
	 *
	 *
	 *   Sell ALL Tweets
	 *   Determine if this is a sell all tweet
	 *
	 ***/
}elseif(preg_match("/(all|every|every single|each)\s(held|remaining\s|current\s)?(positions|position|options|option)\s(cleared|sold|liquidated|put)|out of\s(every|all|every single|each)\s(held|remaining\s)?(positions|position|option|options)|sold\s(all|every|every single|each|out of)\s(remaining\s|current\s)?(positions|position|options|option|everything)/i",$tweet['text'])
		|| preg_match('/(all|every|every single|each)\s(held|remaining\s|current\s)?(positions|position|options|option)\s(cleared|sold|liquidated|put)|out of\s(every|all|every single|each)\s(held|remaining\s)?(positions|position|option|options)/i',$tweet['text'])
		|| preg_match('/holding\s(no positions|nothing)|not holding\s(anything|any positions|options|any options)|(exited\s|got\s)?out of everything|sold everything|everything\s(sold|liquidated)/i',$tweet['text'])
		){
	$sell++;
	print "This is a sell ALL tweet <br />";
	include_once(__DIR__.'/twilio.php');
	foreach(ALERT_PHONES as $phone){
		send_sms($phone, "Option Sniper is SELLING ALL symbols! Tweet:'".$tweet['text']."'");
	}
	require_once($_SERVER['DOCUMENT_ROOT'].'vendor/autoload.php');
	$mgClient = new  \Mailgun\Mailgun('key-89b611c80c7ed2370249456c93fd2881');
	# Make the call to the client.
	$result = $mgClient->sendMessage("mg.gorillatech.app", array(
		'from'    => 'Gorilla Tech <noreply@gorillatech.app>',
		'to'      => ALERT_EMAILS_CS,
		'subject' => 'Option Sniper Just sold out of everything!',
		'html'    =>"<h1 style='color:#a91a1e'>GorillaTech Liquidate Alert!</h1>".
					"A tweet by Option Sniper leads us to believe he has sold out of all positions. Here is a copy of the tweet:".
					"<blockquote style='border-left:5px solid #a91a1e;padding:10px 4px;'>".$tweet['text']."</blockquote><br />".
					"You can view this tweet on <a href='http://client.voztechnologies.com/optionSniper/tweets.php'>Gorilla Tech</a> or on <a href='https://twitter.com/option_snipper/status/".$tweet['id']."'>Twitter</a>"
	));
















	/***
	 *
	 *
	 *   Sell Tweets
	 *   Extract the info from the tweet and insert it into the DB.¨
	 *
	 ***/
	}elseif(preg_match("/locked(\s|\W|$)/i",$tweet['text'])){
		$sell++;
		print "This is a sell <br />";

		# Extract the stock symbol
		preg_match("/\\$[a-z]{2,5}/i",$tweet['text'],$match2); // Cut off the $ sign in front of the symbol if it is there
		$symbol = preg_replace("/^\\$/i","",$match2[0]);
		if(!$symbol){ // Cut out all the filler words like some, a lot, tons, a ton and try to find the symbol again
			$symbol = preg_replace('/\s(this|those|lots|tons|luck|another|some|many|us|you|them|their|his|her|it|big|little|no|half|all|none|gains|profits|profit|averages|huge|massive|amazing|very)|[\.\?\)\(]/i',' ',$tweet['text']);
			preg_match_all('/these(\s)?\$?[a-z]{1,5}|\$[a-z]{1,5}|with\s[a-z]{2,5}/i',$symbol,$symbol);
			$symbol = preg_replace('/\s|with|these|\$/i','',$symbol[0][0]);
			if(!$symbol){
				foreach(TRADE_SYMBOLS as $tradeable_symbol){ // Search the array of allowed symbols to trade
					if(preg_match('/'.$tradeable_symbol.'/i',$tweet['text'])){
						$symbol = $tradeable_symbol;
						break;
					}
				}
				if(!$symbol){
					foreach($known_symbols as $known_symbol){ // Search the array of allowed symbols to trade
						if(preg_match('/(\b|\\$)'.$known_symbol.'\s/i',$tweet['text'])){
							$symbol = $known_symbols;
							break;
						}
					}
				}
			}
		}

		$symbol = strtolower(trim($symbol));
		if(!$symbol){$sell--;$rant++;continue;} // If there is no symbol then he is just ranting and used the word locked

		// A specific symbol, contract price, and strike price for sells can not be found and without pages of code and a little AI and a little luck, it is hard to pull out the contract price he is selling at.
		// All we can do here is for display purposes, show the best guess. So here we will remove anything that is from \d\d\ because that would be the original contract price
		$contract_price = preg_replace('/from\s+(\d|\.){1,6}/i','',$tweet['text']); // Take out text such as "from 134.25"
		$contract_price = preg_match('/at\s?(\d|\.){1,6}|@\s?(\d|\.){1,6}/i',$contract_price,$sell_match); // Match text that has a price with @ or at preceding it
		$contract_price = preg_replace('/[^0-9.]/i','',$sell_match[0]); // Remove the @ or at from the text and this should be the contract price

		# Extract the strike price
		preg_match_all("/\d{2,5}c/i",$tweet['text'],$matches); // When doing buys, the contract price always has a c after it for "call"
		$strike_price = preg_replace("/[^0-9.]/i","",$matches[0][0]);

		$current_quote = $tda->quote_info($symbol,'askPrice');

		$sql = "INSERT IGNORE INTO tweets SET
				tweet_id='$tweet_id',
				screen_name='option_sniper',
				entry_type='sell',
				symbol='$symbol',
				strike_price='$strike_price',
				contract_price='$contract_price',
				tweet_time_quote_price='".$current_quote."',
				tweet='".$conn->real_escape_string($tweet['text'])."',
				expiration_date='',
				date_tweeted='$date_tweeted'";
		$conn->query($sql);
		updateLog($log_loc, "Option Sniper tweeted a SELL for $symbol. Current Ask Price: $current_quote. Tweet ID is ".$tweet_id);

		foreach(TRADE_SYMBOLS as $tradeable_symbol){
			if($tradeable_symbol==$symbol){
				include_once(__DIR__.'/twilio.php');
				foreach(ALERT_PHONES as $phone){
					send_sms($phone, "Option Sniper tweeted a SELL for $symbol. Current Ask Price:".$current_quote.". ".$tweet['text']);
				}

				require_once($_SERVER['DOCUMENT_ROOT'].'vendor/autoload.php');
				$mgClient = new  \Mailgun\Mailgun('key-89b611c80c7ed2370249456c93fd2881');
				$result = $mgClient->sendMessage("mg.gorillatech.app", array(
					'from'    => 'Gorilla Tech <noreply@gorillatech.app>',
					'to'      => ALERT_EMAILS_CS,
					'subject' => 'Option Sniper sold '.$symbol,
					'html'    =>"<h1 style='color:#a91a1e'>GorillaTech Sell Alert!</h1>".
								"A tweet by Option Sniper leads us to believe he has sold just sold ".$symbol.". Here is a copy of the tweet:".
								"<blockquote style='border-left:5px solid #a91a1e;padding:10px 4px;'>".$tweet['text']."</blockquote><br />".
								"You can view this tweet on <a href='http://client.voztechnologies.com/optionSniper/tweets.php'>Gorilla Tech</a> or on <a href='https://twitter.com/option_snipper/status/".$tweet['id']."'>Twitter</a>"
				));

				break;
			}
		}




















	/***
	 *
	 *
	 *   Buy Tweets
	 *   Extract the info from the tweet and insert it into the DB.
	 *   Determine if we should buy options and buy them
	 *
	 ***/
	}elseif(preg_match_all("/\b(bot|got|bought|added|adding|to consider)\s((back (tons|many|a lot of|more)?|back|a few|some|a lot|massive amounts of|a good amount of|much more|many more|tons more|so many|so much|a few|many of|tons of|tons|a ton|many|more)\s)?\\$?[a-z]{2,5}\b|\\$?[a-z]{2,5}\b.*(good |great )?to consider/i",$tweet['text'],$match1)){
		$buy++;
		print "This is a buy!!! <br />";
		# Extract the stock symbol
		preg_match("/\\$[a-z]{2,5}/i",$match1[0][0],$match2); // Cut off the $ sign in front of the symbol if it is there
		$symbol = preg_replace("/^\\$/i","",$match2[0]);
		if(!$symbol){ // Cut out all the filler words like some, a lot, tons, a ton and try to find the symbol again
			//$symbol = preg_replace('/\s+/',' ',preg_replace('/\b(bot|got|bought|added)\s((back (tons|many|a ton|a lot of|so much)?|back|a few|some|a lot|massive amounts of|a good amount of|so many|so much|a few|tons of|tons|a ton|many)\s)?|(more|of|this|that|many|more|some|ystd|yesterday|today|last|first|night|tomorrow|from|only|avg|average|about|around|near|almost|total|huge|few|here|at|on|with|because|cause|and|it|the|like|an|great|good|amazing)\s/i',' ',$match1[0][0]));
			$symbol = preg_replace('/\s+/',' ',preg_replace('/\b(bot|got|bought|added|(good |great )?to consider)\s((back (tons|many|a ton|a lot of|so much)?|back|a few|some|a lot|massive amounts of|a good amount of|so many|so much|a few|tons of|tons|a ton|many)\s)?|(more|of|this|that|many|more|some|ystd|yesterday|today|last|first|night|tomorrow|from|only|avg|average|about|around|near|almost|total|huge|few|here|at|on|with|because|cause|and|it|the|like|an|great|good|amazing)\s/i',' ',$tweet['text']));
			if(!preg_match("/^[a-z]{2,5}$/i",$symbol)){
				foreach(TRADE_SYMBOLS as $tradeable_symbol){ // Search the array of allowed symbols to trade
					if(preg_match('/'.$tradeable_symbol.'/i',$tweet['text'])){
						$symbol = $tradeable_symbol;
						break;
					}
				}
			}
			if(!preg_match("/^[a-z]{2,5}$/i",$symbol)){
				foreach($known_symbols as $tradeable_symbol){ // Search the array of known symbols searching for a shot in the dark on finding a symbol
					if(preg_match('/(\b|\\$)'.$tradeable_symbol.'\s/i',$tweet['text'])){
						$symbol = $tradeable_symbol;
						break;
					}
				}
			}
		}
		$symbol = strtolower(trim($symbol));

		# Extract the strike price and determine if it is a call or a put
		preg_match_all("/\d{2,5}(c|p)/i",$tweet['text'],$matches); // When doing buys, the contract price always has a c after it for "call"
		if(substr(strtolower($matches[0][0]), -1)=='c'){
			$strike_price = preg_replace("/c$/i","",$matches[0][0]);
			$p_or_c = 'c';
		}elseif(substr(strtolower($matches[0][0]), -1)=='p'){
			$strike_price = preg_replace("/p$/i","",$matches[0][0]);
			$p_or_c = 'p';
		}else{
			$strike_price = $matches[0][0];
			$p_or_c = 'c';
		}

		# Get the contract price.   (at|\@)?(\s{0,2})(\d|\.){1,5}\% replaces any occurrence of at 38.2%         \s(at|\@)?(\s{1,2})([\d|\.]{1,6}) matches digits, with decimals which is preceded by an @ or "at"
		preg_match_all("/\s(at|\@)?(\s{1,2})([\d|\.]{1,6})/i",preg_replace("/(at|\@)?(\s{0,2})(\d|\.){1,6}\%/","",$tweet['text']),$matches);
		$contract_price = preg_replace("/(\\@|at|\s)/i","",$matches[0][0]);

		$contract_search_text = preg_replace("/(at\s|@\s)?".$contract_price."/i","",$tweet['text']);
		preg_match_all("/\s(at|\@)?(\s{1,2})([\d|\.]{1,6})/i",preg_replace("/(at|\@)?(\s{0,2})(\d|\.){1,6}\%/","",$contract_search_text),$new_matches);
		if(preg_replace("/(\\@|at|\s)/i","",$new_matches[0][0])){
			$second_contract_price = 1;
		}

		# Get the expiration date
		$months = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov', 'dec',
						'january','february','march','april','may','june','july','august','september','october','november', 'december',
						'agust','agst','augst','sptmbr','sept','octbr','novbr','decbr');
		$days = array(31,30,29,28,27,26,25,24,23,22,21,20,19,18,17,16,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1,'01','02','03','04','05','06','07','08','09');
		foreach($months as $month){ // Loop over the months then over each day trying to find a date
			foreach($days as $day){
				if(preg_match('/'.$month.$day.'/i',$tweet['text'])){
					$bad = array('agust','agst','augst','sptmbr','sept','octbr','novbr','decbr');
					$good  = array('august','august','august','september','september','october','noveber','december');
					$month = date('m', strtotime(str_replace($bad, $good, $month))); // Fix commonly misspelled months with str_replace
					if(preg_replace('/^0/','',$month) < 3 && (date('m')==12 || date('m')==11)){ // Check if we are in Nov or Dec now and the month of expire is Jan or Feb, if so the year needs to be next year
						$year = date('Y')+1;
					}else{
						$year = date('Y');
					}
					if(preg_replace('/^0/','',$day) < 10 ){ // Make sure days less than 10 start with a 0
						$day = '0'.preg_replace('/^0/','',$day); // Cut off a possible 0 at the beginning and add it on just to make sure it is there.
					}
					$expire_date=$year.'-'.$month.'-'.$day;
					$secs_till_ex = strtotime(date($expire_date.' G:i:s')) - strtotime(date('Y-m-d G:i:s')); // Get the number of sec until it expires
					$days_till_ex = $secs_till_ex / 86400;
					break 2;
				}
			}
		}

		$current_quote = $tda->quote_info($symbol,'askPrice',1); //Get the current quote but do it remotely on another TDA account so we do not tie up our API hits. TDA prohibits us from hitting them more than twice per second
		$sql = "INSERT IGNORE INTO tweets SET
				tweet_id='$tweet_id',
				screen_name='option_sniper',
				entry_type='Buy ".strtoupper($p_or_c)."',
				symbol='$symbol',
				strike_price='$strike_price',
				contract_price='$contract_price',
				tweet_time_quote_price='".$current_quote."',
				tweet='".$conn->real_escape_string($tweet['text'])."',
				expiration_date='$expire_date',
				date_tweeted='$date_tweeted'";
		print $sql."<br />";
		$conn->query($sql);
		updateLog($log_loc,"Inserted new tweet ID as a buy into DB. Tweet ID is ".$tweet_id);



		if($tda->is_trading_enabled()){
			if($symbol){ // Make sure a symbol was found
				if(preg_match_all('/'.$symbol.'/i',TRADE_SYMBOLS_CS)){ // Make sure this symbol is on the list of tradeable symbols
					$counter=0;
					foreach(TRADE_SYMBOLS as $tradeable_symbol){ // Search the array of allowed symbols to trade
						if(preg_match('/'.$tradeable_symbol.'/i',$tweet['text'])){
							$counter++;
						}
					}
					if($counter < 2){ // Make sure only one symbol was found in the tweet
						if($expire_date){ // Make sure an expire date was found
							if($secs_till_ex > MIN_DAYS_TILL_EXPIRE){ // Make sure that it does not expire in the next 0 days (Set to 1 to make it tomorrow)
								if($days_till_ex < MAX_DAYS_TILL_EXPIRE){ // Make sure the contracts don't expire in a long time from now
									if($strike_price){ // Make sure there was a strike price int he tweet
										if($strike_price>MIN_STRIKE_PRICE){ // Make sure the strike price is at least $5
											if($strike_price<MAX_STRIKE_PRICE){ // Make sure the strike price is not crazy expensive as there may be something wrong
												if(!$second_contract_price){ // Make sure there are not multiple contract prices found.
													if($contract_price){ // Make sure there is a contract price
														# Build the symbol (GOOGL_072018C1245, googl_072718C1205)
														#                Symbol  Underscore           ExpireDate                 Call or Put Call    Strike Price
														#	                |        |                   |                                 |         |
														$option_symbol = $symbol . '_' . $month.$day.preg_replace('/^20/','',$year) . $p_or_c . $strike_price;
														# Previous way of creating symbol which was deleted on July 20th 18 then recovered on July 26th 18: $optionSymbol = strtoupper($symbol).'_'.date('m',strtotime($expiration)).date('d',strtotime($expiration)).preg_replace('/^20/','',$year).'C'.$strike_price;
														# Now that we have all the required information to create an option quote we can make the symbol and get the current contract price then record it.
														$current_contract_price = $tda->quote_info($option_symbol,'askPrice'); // Get the current contract price
														$conn->query("UPDATE `twitter_sniper`.`tweets` SET tweet_time_contract_price='".$current_contract_price."' WHERE tweet_id='".$tweet_id."' LIMIT 1");
														if($contract_price > MIN_CONTRACT_PRICE){ // Make sure the contract price is not very very cheap
															if($contract_price < MAX_CONTRACT_PRICE){ // Make sure the contract price is not very expensive
																if($current_contract_price){// Make sure we have a current contract price... this is a second check to if our $option_symbol is correct. If it is not, a current contract price can not be found
																	if($current_contract_price > MIN_CONTRACT_PRICE){ // Make sure the min contract price is less than the current contract price
																		if($current_contract_price < MAX_CONTRACT_PRICE){ // Make sure the current contract price is less than the max allowed contract price
																			$secs_since_tweet = strtotime(date('Y-m-d H:i:s')) - strtotime($date_tweeted); // Get the number of sec since it was tweeted
																			if($secs_since_tweet < MAX_SEC_AFTER_TWEET){
																				$secs_till_market_close = strtotime(date('Y-m-d 13:00:0')) - strtotime(date('Y-m-d H:i:s')); // Get the number of sec until the market closes
																				if($secs_till_market_close > MIN_SEC_TILL_CLOSE){
																					# Determine if contract price has moved too high or too lower to continue trading.....
																					$diff = $current_contract_price - $contract_price;
																					if($diff >= 0){ # Went up or stayed the same
																						$percentChange = number_format(($diff/$contract_price)*100,2);
																						if($percentChange > MAX_OPTION_INCREASE){
																							# tries every 5 seconds to find a better entry point that is below the threshold.
																							$tries =1; $found_entry_point=0;
																							while($tries < 6){
																								if($tries==1){
																									updateLog($log_loc,"<span style='color:#ffae00;'>$current_contract_price is a $percentChange% increase which is more than the allowed threshold.</span>",1);
																									updateLog($log_loc,"<span style='color:#ffae00;'>Searching for lower entry point every 5 seconds...</span>",1);
																								}
																								updateLog($log_loc,"<span style='color:#ffae00;'>Buy attempt $tries. $current_contract_price is a $percentChange% increase. Searching for lower entry point</span>",1);
																								sleep(5);
																								$tries++;
																								$current_contract_price = $tda->quote_info($option_symbol,'askPrice');
																								$diff = $current_contract_price - $contract_price;
																								$percentChange = number_format(($diff/$contract_price)*100,2);
																								if($percentChange < MAX_OPTION_INCREASE){
																									$found_entry_point=1;
																									updateLog($log_loc,"<span style='color:#ffae00;'>Buy attempt $tries found contract price $current_contract_price is a $percentChange% increase. This is lower than the threshold</span>",1);
																									break;
																								}
																							}
																							if(!$found_entry_point){
																								$conn->query("UPDATE `twitter_sniper`.`tweets` SET action_reason='Contract increased $percentChange% (currently:$current_contract_price, was:$contract_price) and is more than the allowed threshold' WHERE tweet_id='".$tweet_id."' LIMIT 1");
																								continue;
																							}
																						}
																					}else{ # Contract price went down
																						$diff = abs($diff); # Turn it into a positive number
																						$percentChange = number_format(($diff/$contract_price)*100,2);
																						if($percentChange > MAX_OPTION_DECREASE){
																							updateLog($log_loc,'Contract has decreased '.$percentChange.'% (currently:'.$current_contract_price.', was:'.$contract_price.') and is more than the allowed threshold',1); $conn->query("UPDATE `twitter_sniper`.`tweets` SET action_reason='Contract has decreased $percentChange% (currently:$current_contract_price, was:$contract_price) and is more than the allowed threshold' WHERE tweet_id='".$tweet_id."' LIMIT 1");
																							continue;
																						}
																					}
																					$account_balance = $tda->account_balance();
																					if($account_balance > RESERVE_FUNDS_USD+TRADE_AMOUNT_USD-200){ # Make sure we are not impeeding on our reserve funds. Leave 100 for trades and fees
																						if(preg_match('/bot\s((many|some|tons|a ton)\s)?more|added|to consider|adding\s/i',$tweet['text'])){ # If we are adding to a position, then we are going to put half the amount in
																							$contracts= floor((TRADE_AMOUNT_USD/2) / ($current_contract_price*100));
																							updateLog($log_loc,'Trade amount reduced in half based on to consider or added or bot more key words',1);
																						}else{
																							$contracts= floor(TRADE_AMOUNT_USD / ($current_contract_price*100));
																						}
																						$buy_response = $tda->option_buy($option_symbol, $contracts); # This is the buy order here
																						# Check if we had a true or false response from the option_buy method
																						if($buy_response){
																							updateLog($log_loc,"<span style='color:#ff0000;'>A POSITION ENTERED FOR $symbol ($option_symbol). $contracts contracts around ".$current_contract_price."</span>",1);
																						}else{
																							updateLog($log_loc,"<span style='background-color:#ff0000; color:#FFF;'>ERROR BUYING POSITION FOR $symbol ($option_symbol). $contracts contracts around ".$current_contract_price."</span>",1);
																						}
																						$conn->query("UPDATE `twitter_sniper`.`tweets` SET  action_taken='Bought', action_reason='Option completed validation', contract_count='".$contracts."' WHERE tweet_id='".$tweet_id."' LIMIT 1");
																						$bought=1;
																						# Send out the alerts
																						include_once(__DIR__.'/twilio.php');
																						foreach(ALERT_PHONES as $phone){
																							send_sms($phone, "Attempted Market buy $p_or_c. $symbol ".$strike_price.$p_or_c." $contracts contracts @ $current_contract_price for $month-$day Current quote:".$current_quote);
																						}
																						require_once($_SERVER['DOCUMENT_ROOT'].'vendor/autoload.php');
																						$mgClient = new  \Mailgun\Mailgun('key-89b611c80c7ed2370249456c93fd2881');
																						$result = $mgClient->sendMessage("mg.gorillatech.app", array(
																							'from'    => 'Gorilla Tech <noreply@gorillatech.app>',
																							'to'      => ALERT_EMAILS_CS,
																							'subject' => "Position ($p_or_c) entered on ".$symbol,
																							'html'    =>"<h1 style='color:#a91a1e'>GorillaTech Position Entered!</h1>".
																										"A tweet by Option Sniper leads us to believe he has entered a position ($p_or_c) on ".$symbol.". Here is a copy of the tweet:".
																										"<blockquote style='border-left:5px solid #a91a1e;padding:10px 4px;'>".$tweet['text']."</blockquote><br />".
																										"You can view this tweet on <a href='http://client.voztechnologies.com/optionSniper/tweets.php'>Gorilla Tech</a> or on <a href='https://twitter.com/option_snipper/status/".$tweet['id']."'>Twitter</a><br /><br />".
																										"Based on the Gorilla Tech settings, we have automatically entered the following position ($p_or_c) with ".$symbol.".<br />".
																										"<b>Strike Price:</b>$strike_price.$p_or_c. <b>Contract Price:</b>$current_contract_price <b>Number of Contracts:</b>$contracts <b>Expiring on:</b>$month - $day. The current quote for $symbol is $current_quote ".
																										"Manage this position on <a href='https://invest.ameritrade.com/grid/p/site#r=jPage/cgi-bin/apps/u/ConsolidatedOrderStatus'>TD Ameritrade</a>"
																						));
																					}else{$reason="Trade will deplete reserve funds and can not be made.";}
																				}else{$reason="Market is closed or closes within ".MIN_SEC_TILL_CLOSE." sec, trading prevented";}
																			}else{$reason="Tweet was created more than ".MAX_SEC_AFTER_TWEET." sec ago, we took too long to find it and settings wont permit it to be traded";}
																		}else{$reason="Current contract price ($current_contract_price) is over $".MAX_CONTRACT_PRICE.", that is more than settings allow";}
																	}else{$reason="Current contract price ($current_contract_price) is under $".MIN_CONTRACT_PRICE.", that is less than settings allow";}
																}else{$reason = "No Current contract price was found.";}
															}else{$reason="Contract price ($contract_price) is over $".MAX_CONTRACT_PRICE.", that is more than settings allow";}
														}else{$reason="Contract price ($contract_price) is under $".MIN_CONTRACT_PRICE.", that is less than settings allow";}
													}else{$reason="Contract price was not found in tweet";}
												}else{$reason="More than one contract price was found";}
											}else{$reason="Strike price ($strike_price) is over $".MAX_STRIKE_PRICE.", that is more than settings allow";}
										}else{$reason="Strike price ('.$strike_price.') is under $'.MIN_STRIKE_PRICE.', that is less than settings allow";}
									}else{$reason="Strike price was not found in tweet";}
								}else{$reason="Contracts expire in more than ".MAX_DAYS_TILL_EXPIRE.", that is longer than settings allow";}
							}else{$reason="Contracts expire in less than '.MIN_DAYS_TILL_EXPIRE.' days or have already expired, that is less than settings allow";}
						}else{$reason="Expiration date was not found in tweet";}
					}else{$reason="More than one symbol found in Tweet. Limiting risk of taking a position on the wrong symbol";}
				}else{$reason="Symbol ($symbol) is not in the list of traded symbols";}
			}else{$reason="Symbol was not found in tweet";}
		}else{$reason="Trading is not currently enabled in the settings";}

		if($bought == 0){

			print "Symbol:$symbol ContractPrice:$contract_price StrikePrice:$strike_price <br />\n";
			updateLog($log_loc,$reason,1);
			$conn->query("UPDATE `twitter_sniper`.`tweets` SET action_reason='$reason' WHERE tweet_id='".$tweet_id."' LIMIT 1");

			if(in_array($symbol,TRADE_SYMBOLS)){
				include_once(__DIR__.'/twilio.php');
				foreach(ALERT_PHONES as $phone){
					send_sms($phone, "A buy ".$p_or_c." tweet for $symbol was found but not bought b/c: $reason." );
				}
				require_once($_SERVER['DOCUMENT_ROOT'].'vendor/autoload.php');
				$mgClient = new  \Mailgun\Mailgun('key-89b611c80c7ed2370249456c93fd2881');
				$result = $mgClient->sendMessage("mg.gorillatech.app", array(
					'from'    => 'Gorilla Tech <noreply@gorillatech.app>',
					'to'      => ALERT_EMAILS_CS,
					'subject' => "Buy ".$p_or_c." Tweet found for $symbol but no position was taken ",
					'html'    =>"<h1 style='color:#a91a1e'>GorillaTech Buy Alert!</h1>".
								"A tweet by Option Sniper leads us to believe he has entered a position ($p_or_c) on ".$symbol.". Here is a copy of the tweet:".
								"<blockquote style='border-left:5px solid #a91a1e;padding:10px 4px;'>".$tweet['text']."</blockquote><br />".
								"You can view this tweet on <a href='http://client.voztechnologies.com/optionSniper/tweets.php'>Gorilla Tech</a> or on <a href='https://twitter.com/option_snipper/status/".$tweet['id']."'>Twitter</a> for more information<br /><br />".
								"Based on the Gorilla Tech settings, we have not entered a position for the following reason: $reason.<br />"
				));
			}
		}


















	/***
	 *
	 *
	 *   Rants
	 *   We will not do anything with these tweets at this time.
	 *   These are tweets that mean nothing and he is just ranting about something. They never got caught in the buys, sells, or msgs
	 *
 	***/
	}else{
		$rant++;
		print "Just a rant <br />";


		$sql = "INSERT IGNORE INTO tweets SET
				tweet_id='$tweet_id',
				screen_name='option_sniper',
				entry_type='rant',
				symbol='',
				strike_price='',
				contract_price='',
				tweet_time_quote_price='',
				tweet='".$conn->real_escape_string($tweet['text'])."',
				expiration_date='',
				date_tweeted='$date_tweeted'";
		$conn->query($sql);
		updateLog($log_loc,"Inserted new tweet ID as a rant into DB. Tweet ID is ".$tweet_id);
		continue;

	}






	// Reset Variables for next loop
	$output=NULL;
	$tweet_id=NULL;
	$entry_type=NULL;
	$symbol=NULL;
	$contracts=NULL;
	$price=NULL;
	$tweet=NULL;
	$date_tweeted=NULL;
	$expire_date =NULL;
	$month=NULL;
	$day=NULL;
	$year=NULL;
	$percent_change_ok=NULL;
}
//updateLog($log_loc,"Buys:$buy Sells:$sell Msgs:$msg Rants:$rant Already Processed:$already_processed");
$time_elapsed_secs = microtime(true) - $start;
//updateLog($log_loc,"<span style='border-bottom:1px solid #777'>Finished in $time_elapsed_secs seconds</span>");
?>