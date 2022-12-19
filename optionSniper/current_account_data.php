<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once(__DIR__.'/functions.v2.php');
require_once(__DIR__.'/tda.class.php');
include_once(__DIR__.'/get_settings.php');

$key = 'account_current_data';
$current_data = $memcache->get($key);
//$twitter_ids = FALSE;
if(!$current_data){
        $current_data = $tda->account_balance('all');

        if($current_data){
            $memcache->set($key, $current_data, 0, 2); // Store for 2 sec
        }else{
            print "failed to retrieve current data";
            exit();
        }
}

if($_GET['pre']){
    print "<pre>";
    print_r($current_data);
    print "</pre>";
}else{
  print json_encode($current_data);

}
exit();
?>
{
  "securitiesAccount": {
    "type": "MARGIN",
    "accountId": "490477794",
    "roundTrips": "9",
    "isDayTrader": "1",
    "isClosingOnlyRestricted": "",
    "positions": [
          {
        "shortQuantity": "0",
        "averagePrice": "2.46",
        "currentDayProfitLoss": "270",
        "currentDayProfitLossPercentage": "0.05",
        "longQuantity": "20",
        "settledLongQuantity": "0",
        "settledShortQuantity": "0",
        "instrument": {
          "assetType": "OPTION",
          "cusip": "0NFLX.HA80350000",
          "symbol": "NFLX_081018C350",
          "description": "NFLX 100 (Weeklys) 10 AUG 18 350 CALL",
          "putCall": "CALL",
          "underlyingSymbol": "NFLX"
        },
        "marketValue": "5190"
      },
       {
        "shortQuantity": "0",
        "averagePrice": "6.565",
        "currentDayProfitLoss": "64",
        "currentDayProfitLossPercentage": "0.02",
        "longQuantity": "4",
        "settledLongQuantity": "0",
        "settledShortQuantity": "0",
        "instrument": {
          "assetType": "OPTION",
          "cusip": "0TSLA.HA80355000",
          "symbol": "TSLA_081018C355",
          "description": "TSLA 100 (Weeklys) 10 AUG 18 355 CALL",
          "putCall": "CALL",
          "underlyingSymbol": "TSLA"
        },
        "marketValue": "2690"
      },
      {
        "shortQuantity": "0",
        "averagePrice": "1",
        "currentDayProfitLoss": "0",
        "currentDayProfitLossPercentage": "0",
        "longQuantity": "41233.68",
        "settledLongQuantity": "41233.68",
        "settledShortQuantity": "0",
        "instrument": {
          "assetType": "CASH_EQUIVALENT",
          "cusip": "9ZZZFD104",
          "symbol": "MMDA1",
          "description": "FDIC INSURED DEPOSIT ACCOUNT CORE NOT COVERED BY SIPC",
          "type": "MONEY_MARKET_FUND"
        },
        "marketValue": "41233.68"
      }
    ],
    "initialBalances": {
      "accruedInterest": "0",
      "availableFundsNonMarginableTrade": "42164.24",
      "bondValue": "0",
      "buyingPower": "84328.48",
      "cashBalance": "930.56",
      "cashAvailableForTrading": "0",
      "cashReceipts": "0",
      "dayTradingBuyingPower": "3722.24",
      "dayTradingBuyingPowerCall": "0",
      "dayTradingEquityCall": "0",
      "equity": "42164.24",
      "equityPercentage": "42164.24",
      "liquidationValue": "42164.24",
      "longMarginValue": "0",
      "longOptionMarketValue": "0",
      "longStockValue": "0",
      "maintenanceCall": "0",
      "maintenanceRequirement": "0",
      "margin": "930.56",
      "marginEquity": "930.56",
      "moneyMarketFund": "41233.68",
      "mutualFundValue": "0",
      "regTCall": "0",
      "shortMarginValue": "0",
      "shortOptionMarketValue": "0",
      "shortStockValue": "0",
      "totalCash": "42164.24",
      "isInCall": "",
      "pendingDeposits": "0",
      "marginBalance": "0",
      "shortBalance": "0",
      "accountValue": "42164.24"
    },
    "currentBalances": {
      "accruedInterest": "0",
      "cashBalance": "0",
      "cashReceipts": "0",
      "longOptionMarketValue": "7880",
      "liquidationValue": "43180.76",
      "longMarketValue": "0",
      "moneyMarketFund": "41233.68",
      "savings": "0",
      "shortMarketValue": "-0",
      "pendingDeposits": "0",
      "availableFunds": "0",
      "availableFundsNonMarginableTrade": "35300.76",
      "buyingPower": "70601.52",
      "buyingPowerNonMarginableTrade": "35300.76",
      "dayTradingBuyingPower": "-26356.88",
      "equity": "35300.76",
      "equityPercentage": "0",
      "longMarginValue": "0",
      "maintenanceCall": "0",
      "maintenanceRequirement": "0",
      "marginBalance": "-5932.92",
      "regTCall": "0",
      "shortBalance": "0",
      "shortMarginValue": "0",
      "shortOptionMarketValue": "-0",
      "sma": "35300.76",
      "bondValue": "0"
    },
    "projectedBalances": {
      "availableFunds": "35300.76",
      "availableFundsNonMarginableTrade": "35300.76",
      "buyingPower": "70601.52",
      "dayTradingBuyingPower": "0",
      "dayTradingBuyingPowerCall": "0",
      "maintenanceCall": "0",
      "regTCall": "0",
      "isInCall": "",
      "stockBuyingPower": "70601.52"
    }
  }
}
