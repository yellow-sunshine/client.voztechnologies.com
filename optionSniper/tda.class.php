<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/optionSniper/get_settings.php');
class tda{
	private $conn;
	private $log_loc;





	public function __construct($conn,$log_loc){
		$this->conn = $conn;
		$this->log_loc = $log_loc;
	}








	public function auth_token($code){
		global $memcache;
		$host = "https://api.tdameritrade.com/v1/oauth2/token";
		$headers = array('Content-Type: application/x-www-form-urlencoded',
							'Connection: Keep-Alive');
		$post_fields = array(
							'grant_type'=>'authorization_code',
							'refresh_token'=>'',
							'access_type'=>'offline',
							'code'=>$code,
							'client_id'=>TDA_AUTH_CLIENT_ID,
							'redirect_uri'=>TDA_AUTH_CALLBACK_URI);
		$response = json_decode(cURL($host,'POST',$headers,$post_fields),true); # Response is in json
		if(!$response){
			return false;
		}
		$this->conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$response['access_token']."' WHERE variable='tda_access_token' LIMIT 1");
		$this->conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$response['refresh_token']."' WHERE variable='tda_refresh_token' LIMIT 1");
		$memcache->delete('optionsniper_settings');
		return true;
	}












	public function refresh_token(){
		global $memcache;
		$host = "https://api.tdameritrade.com/v1/oauth2/token";
		$headers = array('Content-Type: application/x-www-form-urlencoded',
						'Connection: Keep-Alive');
		$post_fields = array('grant_type'=>'refresh_token',
							'refresh_token'=>TDA_REFRESH_TOKEN,
							'access_type'=>'offline',
							'code'=>'',
							'client_id'=>TDA_AUTH_CLIENT_ID,
							'redirect_uri'=>'');
		$response = json_decode(cURL($host,'POST',$headers,$post_fields),true); # Response is in json
		if(!$response['access_token'] || !$response['refresh_token']){
			//mail('mailbrent@gmail.com','refresh failed','refresh failed in tda obj');
			return false;
		}
		//mail('mailbrent@gmail.com','refresh works','refresh works in tda obj');
		$this->conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$response['access_token']."' WHERE variable='tda_access_token' LIMIT 1");
		$this->conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$response['refresh_token']."' WHERE variable='tda_refresh_token' LIMIT 1");
		$memcache->delete('optionsniper_settings');
		return true;
	}












	# is_trading_enabled determines if trading has been paused right now or not.
	/*
		Returns true if trading is not paused and is enabled
		Returns false if trading has been paused or is not enabled for some other reason not yet coded at the time of this writing
	*/
	public function is_trading_enabled(){
		if(date("Y-m-d H:i:s", strtotime(PAUSE_TRADING_TIME_DATE) + PAUSE_TRADING_TIME) > date("Y-m-d H:i:s")){
			return false;
		}
		return true;
	}













/*
Array
(
    [securitiesAccount] => Array
        (
            [type] => MARGIN
            [accountId] => 490477794
            [roundTrips] => 3
            [isDayTrader] =>
            [isClosingOnlyRestricted] =>
            [positions] => Array
                (
                    [0] => Array
                        (
                            [shortQuantity] => 0
                            [averagePrice] => 1
                            [currentDayProfitLoss] => 0
                            [currentDayProfitLossPercentage] => 0
                            [longQuantity] => 20265.57
                            [settledLongQuantity] => 20265.57
                            [settledShortQuantity] => 0
                            [instrument] => Array
                                (
                                    [assetType] => CASH_EQUIVALENT
                                    [cusip] => 9ZZZFD104
                                    [symbol] => MMDA1
                                    [description] => FDIC INSURED DEPOSIT ACCOUNT  CORE  NOT COVERED BY SIPC
                                    [type] => MONEY_MARKET_FUND
                                )

                            [marketValue] => 20265.57
                        )

                )

            [initialBalances] => Array
                (
                    [accruedInterest] => 0.03
                    [availableFundsNonMarginableTrade] => 41233.71
                    [bondValue] => 0
                    [buyingPower] => 82467.42
                    [cashBalance] => 20968.11
                    [cashAvailableForTrading] => 0
                    [cashReceipts] => 0
                    [dayTradingBuyingPower] => 0
                    [dayTradingBuyingPowerCall] => 0
                    [dayTradingEquityCall] => 0
                    [equity] => 41233.71
                    [equityPercentage] => 41233.71
                    [liquidationValue] => 41233.69
                    [longMarginValue] => 0
                    [longOptionMarketValue] => 0
                    [longStockValue] => 0
                    [maintenanceCall] => 0
                    [maintenanceRequirement] => 0
                    [margin] => 20968.11
                    [marginEquity] => 20968.11
                    [moneyMarketFund] => 20265.6
                    [mutualFundValue] => 0
                    [regTCall] => 0
                    [shortMarginValue] => 0
                    [shortOptionMarketValue] => 0
                    [shortStockValue] => 0
                    [totalCash] => 41233.71
                    [isInCall] =>
                    [pendingDeposits] => 0
                    [marginBalance] => 0
                    [shortBalance] => 0
                    [accountValue] => 41233.72
                )

            [currentBalances] => Array
                (
                    [accruedInterest] => 0.03
                    [cashBalance] => 20968.11
                    [cashReceipts] => 0
                    [longOptionMarketValue] => 0
                    [liquidationValue] => 41233.68
                    [longMarketValue] => 0
                    [moneyMarketFund] => 20265.57
                    [savings] => 0
                    [shortMarketValue] => -0
                    [pendingDeposits] => 0
                    [availableFunds] => 0
                    [availableFundsNonMarginableTrade] => 41233.68
                    [buyingPower] => 82467.36
                    [buyingPowerNonMarginableTrade] => 41233.68
                    [dayTradingBuyingPower] => 0
                    [equity] => 41233.68
                    [equityPercentage] => 100
                    [longMarginValue] => 0
                    [maintenanceCall] => 0
                    [maintenanceRequirement] => 0
                    [marginBalance] => 0
                    [regTCall] => 0
                    [shortBalance] => 0
                    [shortMarginValue] => 0
                    [shortOptionMarketValue] => -0
                    [sma] => 41233.68
                    [bondValue] => 0
                )

            [projectedBalances] => Array
                (
                    [availableFunds] => 41233.68
                    [availableFundsNonMarginableTrade] => 41233.68
                    [buyingPower] => 82467.36
                    [dayTradingBuyingPower] => 0
                    [dayTradingBuyingPowerCall] => 0
                    [maintenanceCall] => 0
                    [regTCall] => 0
                    [isInCall] =>
                    [stockBuyingPower] => 82467.36
                )

        )

)
*/
	# account_balance Gets the account balance of the account that we are trading
	/*
		There are no arguments and the return is the buyingPowerNonMarginableTrade only which may not be the actual balance of the account
		buyingPowerNonMarginableTrade is the balance TDA will use to determine if there are funds to make an option trade
	*/
	public function account_balance($all=0){
		$host = "https://api.tdameritrade.com/v1/accounts/".TDA_ACCOUNT_NUMBER.'?fields=positions';
		$headers = array('Authorization: Bearer '.TDA_ACCESS_TOKEN);
		$response = json_decode(cURL($host,'GET',$headers),true); # Response comes back in json
		if(!$response){
			return false;
		}
		if($all){
			return $response;
		}else{
			return $response['securitiesAccount']['currentBalances']['buyingPowerNonMarginableTrade'];
		}
	}






















	# quote_info Gets information on a single quote
	# Specify the quote as the first argument and an optional value for the type of return as the second argument
	/*
	POSSIBLE arguments will return the value shown in this example:
	"assetType": "EQUITY",
	"symbol": "NFLX",
	"description": "Netflix, Inc. - Common Stock",
	"bidPrice": 375.95,
	"bidSize": 300,
	"bidId": "P",
	"askPrice": 375.99,
	"askSize": 100,
	"askId": "Q",
	"lastPrice": 375.99,
	"lastSize": 0,
	"lastId": "Q",
	"openPrice": 381.24,
	"highPrice": 383.13,
	"lowPrice": 372.3552,
	"bidTick": " ",
	"closePrice": 379.48,
	"netChange": -3.49,
	"totalVolume": 21746266,
	"quoteTimeInLong": 1531958386459,
	"tradeTimeInLong": 1531958387012,
	"mark": 375.95,
	"exchange": "q",
	"exchangeName": "NASDAQ",
	"marginable": true,
	"shortable": true,
	"volatility": 0.014108075,
	"digits": 4,
	"52WkHigh": 423.2056,
	"52WkLow": 160.0201,
	"nAV": 0,
	"peRatio": 258.37,
	"divAmount": 0,
	"divYield": 0,
	"divDate": "",
	"securityStatus": "Normal",
	"regularMarketLastPrice": 375.13,
	"regularMarketLastSize": 2574,
	"regularMarketNetChange": -4.35,
	"regularMarketTradeTimeInLong": 1531944000183,
	"delayed": false
	*/
	public function quote_info($quote,$requested_info='all',$remote=0){
		if($remote){
			# Here we will hit another server that is authenticated with TDA using another API key.
			/*
				TDA limits 2 API calls per second, any more produces an error.
				To get around this, I proxy through another instance of this script setup strictly for auth and quotes
				Example remote request: $response = $tda->quote_info('amzn','openPrice',1);
										$response = $tda->quote_info('googl','all',1); # To get all fields for the quote
										$response = $tda->quote_info('googl','bidPrice',1);
			*/
			$host = "https://quotes.voztechnologies.com/optionSniper/quote.php";
			$post_fields = array('quote'=>$quote,'requested_info'=>$requested_info);
			$data = json_decode(cURL($host,'POST','',$post_fields),1);
		}else{
			$host='https://api.tdameritrade.com/v1/marketdata/'.$quote.'/quotes';
			$headers = array('Authorization: Bearer '.TDA_ACCESS_TOKEN,
							'Content-Type: application/json',
							'Connection: Keep-Alive');
			$response = json_decode(cURL($host, 'GET',$headers),true); # Decode the json response
			$data = array();
			foreach($response[strtoupper($quote)] as $var=>$val){
				$data[$var]=$val;
			}
		}
		if(!$data){
			return false;
		}
		if(strtolower($requested_info)=='all'){
			return $data;
		}else{
			return $data[$requested_info];
		}
	}











	public function quote_history($quote,$frequencyType='minute',$frequency=5){
		$host='https://api.tdameritrade.com/v1/marketdata/'.$quote.'/pricehistory';
		$headers = array('Authorization: Bearer '.TDA_ACCESS_TOKEN,
						'Content-Type: application/json',
						'Connection: Keep-Alive');
		$get_fields = array('periodType'=>'day',
							'period'=>1,
							'frequencyType'=>$frequencyType,
							'frequency'=>$frequency,
							'needExtendedHoursData'=>'false');
		return cURL($host, 'GET',$headers,$get_fields); # Decode the json response
	}










	//https://api.tdameritrade.com/v1/marketdata/chains?apikey=COINLOCK%40AMER.OAUTHAP&symbol=GOOGL&contractType=CALL&strikeCount=1&strike=1245&range=OTM
	public function option_chain($symbol,$contractType,$strike){
		$host='https://api.tdameritrade.com/v1/marketdata/chains?'.
				'apikey='.urlencode(TDA_AUTH_CLIENT_ID).
				'&symbol='.strtoupper($symbol).
				'&contractType='.strtoupper($contractType).
				'&strikeCount=1&strike='.$strike.
				'&range=OTM&optionType=S'.
				'&includeQuotes=FALSE';
		$headers = array('Authorization: Bearer '.TDA_ACCESS_TOKEN);

		print cURL($host,'GET',$headers); # Response comes back in json
	}










	# quote_info_multi Gets information on up to 25 quotes at a time
	/*
		Returns an array of all the quotes. the first dimension of the array is the quote and the second dimension is the key for each type of data.
		example: $quote_data = $tda->quote_info_multi('googl,nflx,amzn');
	*/
	public function quote_info_multi($quotes,$remote=0){
		if($remote){
			# Here we will hit another server that is authenticated with TDA using another API key.
			/*
				TDA limits 2 API calls per second, any more produces an error.
				To get around this, I proxy through another instance of this script setup strictly for auth and quotes
				Example: $tda->quote_info_multi('amzn,googl',1)
			*/
			$host = "https://quotes.voztechnologies.com/optionSniper/quotes.php";
			$post_fields = array('quotes'=>$quotes);
			$response = json_decode(cURL($host,'POST','',$post_fields),1); # Response is in json
		}else{
			$host='https://api.tdameritrade.com/v1/marketdata/quotes?symbol='.urlencode($quotes);
			$headers = array('Authorization: Bearer '.TDA_ACCESS_TOKEN,
							'Content-Type: application/json',
							'Connection: Keep-Alive');
			$response = json_decode(cURL($host, 'GET',$headers),true); # Decode the json response
		}

		if(!$response){
			return false;
		}
		$response = array_change_key_case($response,CASE_LOWER); # make the array keys for the symbols lower case
		return $response;
	}











	# Buys contracts for the passed symbol
	public function option_buy($symbol, $contracts){
		if(!is_numeric($contracts) || !preg_match('/[a-z]{2,5}_\d{6}(C|P)\d{2,4}/i',$symbol)){
			updateLog($this->log_loc,"Validation failed for values (symbol:$symbol, contracts:$contracts) passed to ".__FUNCTION__." on line ".__LINE__." of ".__FILE__);
			print "Validation failed for values (symbol:$symbol, contracts:$contracts) passed to ".__FUNCTION__." on line ".__LINE__." of ".__FILE__;
			return false;
		}

		$host = "https://api.tdameritrade.com/v1/accounts/".TDA_ACCOUNT_NUMBER."/orders";

		$data='{
			"orderType": "MARKET",
			"orderStrategyType": "SINGLE",
			"duration": "DAY",
			"session": "NORMAL",
			"orderLegCollection": [
				{
				"instruction": "BUY_TO_OPEN",
				"quantity": '.$contracts.',
				"instrument": {
					"symbol": "'.$symbol.'",
					"assetType": "OPTION"
					}
				}
			]
		}';


		$headers = array('Authorization: Bearer '.TDA_ACCESS_TOKEN,
						'Connection: Keep-Alive',
						'Content-Type: application/json',
						'Content-Length: '.strlen($data));

		$response = cURL($host,'POST',$headers,$data, 1, 1);

		$response_headers = $response[0];
		/* This is an example response header. $response_headers['cache-control'][0] should print 'no-cache,no-store' if these were the headers returned in the response
		Array(
			[0] => Authorization: Bearer vEfAGFMdIu+en/smb7+Us18K[[[[   EXAMPLE AUTH CODE FOR THIS COMMENT   ]]]kAYBCYKKRqEx19z9sWBHDJACbC00B75E
			[1] => Connection: Keep-Alive
			[2] => Content-Type: application/json
			[3] => Content-Length: 298
			[x-api-version] => Array([0] => 1.5.2)
			[content-type] => Array([0] => application/json)
			[content-length] => Array([0] => 36)
			[date] => Array([0] => Thu, 26 Jul 2018 23:12:23 GMT)
			[cneonction] => Array([0] => close)
			[cache-control] => Array([0] => no-cache,no-store)
			[access-control-allow-origin] => Array([0] => )
			[access-control-allow-headers] => Array([0] => origin, x-requested-with, accept, authorization, content-type)
			[access-control-max-age] => Array([0] => 3628800)
			[access-control-allow-methods] => Array([0] => GET, PUT, POST, DELETE, OPTIONS, HEAD, PATCH)
			[connection] => Array([0] => keep-alive)
			[strict-transport-security] => Array([0] => max-age=31536000)
		)

		*/
		# Handle errors from the response
		if(!$response){
			//updateLog($this->log_loc,"No response received from ".$host." in ".__FUNCTION__." on line ".__LINE__." of ".__FILE__);
			//print "No response received from ".$host." in ".__FUNCTION__." on line ".__LINE__." of ".__FILE__;
			//return false;
		}
		// For some reason I was getting stdClass object. I found this, tried it, and it worked. I tried just decoding it once and that did not work. This was the only way I could get the error out
		// https://stackoverflow.com/a/19495142
		// Also, for some reason, the error message was coming back with invalid json data. The response had a \ at the beginning of the json when there was an auth error so preg_replace takes care of that here
		$response_body = json_decode(json_encode(json_decode(preg_replace("/\\\\/","",$response[1]))), True);
		if($response_body['error']){
			updateLog($this->log_loc,"Error received in response from ".$host." in ".__FUNCTION__." on line ".__LINE__." of ".__FILE__);
			updateLog($this->log_loc,$response_body['error']);
			//print "An error was received in the response from ".$host." in ".__FUNCTION__." on line ".__LINE__." of ".__FILE__;
			print $response_body['error'];
			return false;
		}else{
			return true;
		}
	}







}




?>