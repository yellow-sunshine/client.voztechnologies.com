<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
if (!(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' ||
	$_SERVER['HTTPS'] == 1) ||
	isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
	$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
{
	$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $redirect);
	exit();
}
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once(__DIR__.'/db.class.php');

$conn = new connection();

$log_loc = __DIR__.'/optionSniper.log';
function get_optionsniper_settings($conn){
	global $memcache;
	#Create memcache key and retrieve/set data if available/not available
	global $optionsniper_settings;
	$key = 'optionsniper_settings';
	$optionsniper_settings = $memcache->get($key);
	//$optionsniper_settings = FALSE;
	if(!$optionsniper_settings){
			$rs=$conn->query("SELECT * FROM `twitter_sniper`.`settings` LIMIT 500");
			// Loop over the records found and store the phone number
			while($row = $rs->fetch_assoc()){
				$optionsniper_settings[$row['variable']] = array('variable'=>$row['variable'],
															'value'=>$row['value'],
															'date'=>$row['date'],
															'id'=>$row['id']);
			}
		$memcache->set($key, $optionsniper_settings, 0, 3600);
	}
}

get_optionsniper_settings($conn);

#General settings
define('PAUSE_TWEET_COLLECTION_TIME', $optionsniper_settings['pause_tweet_collection_time']['value']);
define('PAUSE_TWEET_COLLECTION_TIME_DATE', $optionsniper_settings['pause_tweet_collection_time']['date']);
define('GATHER_TWEETS_MARKET_CLOSED', $optionsniper_settings['gather_tweets_market_closed']['value']);
define('PAUSE_TWEET_COLLECTION_TIME_DATE', $optionsniper_settings['pause_tweet_collection_time']['date']);
define('PAUSE_TRADING_TIME', $optionsniper_settings['pause_trading_time']['value']);
define('PAUSE_TRADING_TIME_DATE', $optionsniper_settings['pause_trading_time']['date']);
define('PAUSE_ALERTS_TIME', $optionsniper_settings['pause_alerts_time']['value']);
define('PAUSE_ALERTS_TIME_DATE', $optionsniper_settings['pause_alerts_time']['date']);
define('TRADE_SYMBOLS', explode(",",strtolower(preg_replace('/ /','',$optionsniper_settings['trade_symbols']['value']))));
define('TRADE_SYMBOLS_CS', strtolower(preg_replace('/ /','',$optionsniper_settings['trade_symbols']['value']))); # Comma separated value list
define('HISTORIC_SYMBOLS', explode(",",strtolower(preg_replace('/ /','',$optionsniper_settings['historic_symbols']['value']))));
define('HISTORIC_SYMBOLS_CS', strtolower(preg_replace('/ /','',$optionsniper_settings['historic_symbols']['value']))); # Comma separated value list

define('TRADE_AMOUNT_USD', $optionsniper_settings['trade_amount_usd']['value']);
define('RESERVE_FUNDS_USD', $optionsniper_settings['reserve_funds_usd']['value']);

# Buying thresholds
define('MAX_OPTION_INCREASE', $optionsniper_settings['max_option_increase']['value']);
define('MAX_OPTION_DECREASE', $optionsniper_settings['max_option_decrease']['value']);
define('MAX_DAYS_TILL_EXPIRE', $optionsniper_settings['max_days_till_expire']['value']); // Max days allowed between now and the time contracts expire.
define('MIN_DAYS_TILL_EXPIRE', $optionsniper_settings['min_days_till_expire']['value']); // Max days allowed between now and the time contracts expire.
define('MIN_STRIKE_PRICE', $optionsniper_settings['min_strike_price']['value']);
define('MAX_STRIKE_PRICE', $optionsniper_settings['max_strike_price']['value']);
define('MIN_CONTRACT_PRICE', $optionsniper_settings['min_contract_price']['value']);
define('MAX_CONTRACT_PRICE', $optionsniper_settings['max_contract_price']['value']);
define('MIN_SEC_TILL_CLOSE', $optionsniper_settings['min_sec_till_close']['value']); //Min number of sec allowed till market close. E.g. if set to 1800, market closes in 30 min
define('MAX_SEC_AFTER_TWEET', $optionsniper_settings['max_sec_after_tweet']['value']); // Maximum sec allowed to pass after tweet_created_date before attempting a trade

# Sell thresholds
define('AUTO_SELL_MAX_GAIN', $optionsniper_settings['auto_sell_max_gain']['value']);
define('AUTO_SELL_MAX_LOSS', $optionsniper_settings['auto_sell_max_loss']['value']);

# TD Ameritrade Constants
define('TDA_AUTH_CALLBACK_URI', $optionsniper_settings['tda_auth_callback_uri']['value']);
define('TDA_AUTH_CLIENT_ID', $optionsniper_settings['tda_auth_client_id']['value']);
define('TDA_AUTH_CODE', $optionsniper_settings['tda_auth_code']['value']);
define('TDA_AUTH_CODE_DATE', $optionsniper_settings['tda_auth_code']['date']);
define('TDA_ACCESS_TOKEN', $optionsniper_settings['tda_access_token']['value']);
define('TDA_REFRESH_TOKEN', $optionsniper_settings['tda_refresh_token']['value']);
define('TDA_ACCOUNT_NUMBER', $optionsniper_settings['tda_account_number']['value']);
define('TDA_ACCOUNT_NUMBER_DATE', $optionsniper_settings['tda_account_number']['date']);

# Etrade constants
define('ETRADE_OAUTH_CONSUMER_KEY', $optionsniper_settings['etrade_oauth_consumer_key']['value']);
define('ETRADE_OAUTH_CONSUMER_SECRET', $optionsniper_settings['etrade_consumer_secret']['value']);
define('ETRADE_ACCOUNT_NUMBER', $optionsniper_settings['etrade_account_number']['value']);

# Twilio constants
define('TWILIO_ACCOUNT_SID', $optionsniper_settings['twilio_account_sid']['value']);
define('TWILIO_AUTH_TOKEN', $optionsniper_settings['twilio_auth_token']['value']);
define('TWILIO_PHONE', $optionsniper_settings['twilio_phone']['value']);

# Alerts
define('ALERT_EMAILS', explode(",",preg_replace('/ /','',$optionsniper_settings['alert_emails']['value'])));
define('ALERT_EMAILS_CS', $optionsniper_settings['alert_emails']['value']);
define('ALERT_PHONES', explode(",",preg_replace('/ /','',$optionsniper_settings['alert_phones']['value'])));
define('ALERT_PHONES_CS', $optionsniper_settings['alert_phones']['value']);



# Make functions that will be used everywhere
function cURL($host,$type='get',$headers='',$post_fields='',$response_headers=false,$send_json=0){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL,$host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6); //Time to wait to establish connection
	curl_setopt($ch, CURLOPT_TIMEOUT, 6); //timeout in seconds to wait for response
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // So that curl does not have tpo verify ssl certs


	if(strtolower($type)=='post' && !$send_json){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
	}elseif(strtolower($type)=='post' && $send_json){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	}


	// How to get return headers : https://linuxprograms.wordpress.com/2010/08/06/get-http-headers-curl-response/
	if($response_headers){
		curl_setopt($curlHandle, CURLOPT_HEADER, true);
		// Using an anonymous header function to get Curl Headers : https://stackoverflow.com/a/41135574
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header) use (&$headers){
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if(count($header) < 2){ // ignore invalid headers
					return $len;
				}
				$name = strtolower(trim($header[0]));
				if(!array_key_exists($name, $headers)){
					$headers[$name] = [trim($header[1])];
				}else{
					$headers[$name][] = trim($header[1]);
				}
				return $len;
			}
		);
	}

	$server_output = curl_exec ($ch);
	curl_close ($ch);
	if($server_output){
		if($response_headers){ // If we have asked for response headers, then return them in the array
			return array($headers,$server_output);
		}
		return $server_output;
	}else{
		return false;
	}
}


# Get the TD Ameritrade object for using the API
include_once(__DIR__.'/tda.class.php');
$tda = new tda($conn,$log_loc);
?>