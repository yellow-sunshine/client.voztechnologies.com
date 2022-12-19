<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once(__DIR__.'/functions.v2.php');
require_once(__DIR__.'/tda.class.php');
include_once(__DIR__.'/get_settings.php');


//$twitter_ids = FALSE;



if($_GET['quotes']){
    $quotes_to_get = $_GET['quotes'];
}else{
    $quotes_to_get = TRADE_SYMBOLS_CS;
}

$key = $quotes_to_get;
$current_quote_data = $memcache->get($key);

if(!$current_quote_data){
        $current_quote_data = $tda->quote_info_multi($quotes_to_get,1);
        if($current_quote_data){
            $memcache->set($key, $current_quote_data, 0, 2); // Store for 2 sec
        }else{
            print "failed to retrieve quote data";
            exit();
        }
}
if($_GET['pre']){
    print "<pre>";
    print_r($current_quote_data);
    print "</pre>";
    print json_encode($current_quote_data);
}else{
    print json_encode($current_quote_data);

}
/*
    [assetType] => EQUITY
    [symbol] => AMZN
    [description] => Amazon.com, Inc. - Common Stock
    [bidPrice] => 1834.1
    [bidSize] => 200
    [bidId] => P
    [askPrice] => 1834.95
    [askSize] => 100
    [askId] => P
    [lastPrice] => 1834.3
    [lastSize] => 0
    [lastId] => Q
    [openPrice] => 1788.77
    [highPrice] => 1836.56
    [lowPrice] => 1786
    [bidTick] =>
    [closePrice] => 1797.17
    [netChange] => 37.13
    [totalVolume] => 4354681
    [quoteTimeInLong] => 1533254388212
    [tradeTimeInLong] => 1533254388212
    [mark] => 1834.33
    [exchange] => q
    [exchangeName] => NASDAQ
    [marginable] => 1
    [shortable] => 1
    [volatility] => 0.019754197
    [digits] => 4
    [52WkHigh] => 1880.05
    [52WkLow] => 931.75
    [nAV] => 0
    [peRatio] => 137.04
    [divAmount] => 0
    [divYield] => 0
    [divDate] =>
    [securityStatus] => Normal
    [regularMarketLastPrice] => 1834.33
    [regularMarketLastSize] => 1054
    [regularMarketNetChange] => 37.16
    [regularMarketTradeTimeInLong] => 1533240000406
    [delayed] =>
    */
?>