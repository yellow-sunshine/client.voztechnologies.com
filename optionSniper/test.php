<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/optionSniper/get_settings.php');
include_once(INCLUDES.'/functions.v2.php');
setlocale(LC_MONETARY, 'en_US.UTF-8');
?>
<style>
    table{
        border-collapse:collapse;
    }
    th{
        font-weight:bold;
        padding:20px;
        background-color:#555;
        color:#fff;
        border-bottom:8px solid #333;
    }
    .results td{
        border:1px solid #ddd;
        padding:2px 10px;
    }
    tr{
        border:1px solid #000;
    }
    .gain{
        color: #379600;
        /*background-color: #DFF2BF;*/
    }
    .loss{
        color: #a50000;
        /*background-color: #FFD2D2;*/
    }
    .info{
        color: #1b7aba;
        /*background-color: #FFD2D2;*/
    }
    .balance{
        color:#000;
        font-weight:thin !important;
    }
    .input{
        padding-left:20px;
    }
    .frmButton{
        margin:10px;
    }
</style>
<?php
($_POST['interval'])?$interval=$_POST['interval']:$interval=60*4;
($_POST['days_ago'])?$days_ago=$_POST['days_ago']:$days_ago=27;
($_POST['equity'])?$equity=$_POST['equity']:$equity=7000;
($_POST['leverage'])?$leverage=$_POST['leverage']:$leverage=4;
(isset($_POST['compound']))?$compound=$_POST['compound']:$compound=1;

$price=$conn->query("SELECT usd FROM `ct`.`btc_history` WHERE date='$time' LIMIT 1")->fetch_object()->usd;

if(date('H')=='00' && date('i')=='00' && date('s')=='00'){
    sleep(3); // pause a little if it is exactly midnight since this script is based on this time
}
function sign($n) {
    return ($n > 0) - ($n < 0);
}

function process_time_block($time, $equity, $leverage, $interval, $compound){
    global $conn;
    global $price;
    if($interval % 5 !=0){
        $rounded_interval = round(($interval+5) / 5) * 5;
        $intevalMod = $interval % 5;
        print "<h2>Error: 5 Modulus \$interval must be 0. $interval modulus 5 = $intevalMod<br />\$interval has ben auto rounded to $rounded_interval for you.</h2>";
    }
    $table = "
            <table class='results'>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Profit</th>
                    <th>Position</th>
                    <th>Trading Equity</th>
                    <th>Gain/Loss</th>
                    <th>Unrealized BTC Gain/Loss</th>
                </tr>";
    while($time <= date('Y-m-d H:i:s',strtotime('-'.$interval.' minutes',strtotime(date("Y-m-d H:i:s"))))){
        $i++;
        $unrealized=0;
        $time = date('Y-m-d H:i:s',strtotime('+'.$interval.' minutes',strtotime($time)));

        // Control first loops and manage variable history
        switch($i){
            case 1:     $position='protect';
                        $position_twice = 'protect';
                        $position_last = 'protect';

                        $profit_twice = 0;
                        $profit_last = 0;

                        $price_twice = $price;
                        $price_last = $price;
                        break;

            case 2:
                        $position_twice = 'protect';
                        $position_last = $position;

                        $profit_twice = 0;
                        $profit_last = $profit;

                        $price_twice = $price;
                        $price_last = $price;
                        break;

            default:    $position_twice = $position_last;
                        $position_last = $position;

                        $profit_twice = $profit_last;
                        $profit_last = $profit;

                        $price_twice = $price_last;
                        $price_last = $price;
                        break;
        }

        $price=$conn->query("SELECT usd FROM `ct`.`btc_history` WHERE date='$time' LIMIT 1")->fetch_object()->usd;
        if($i==1){$start_price=$price;$equity_start=$equity;}
        // PROFIT
        if($position_last=='protect' && $i > 2){
            $profit = ($equity*(1+(-1*(($price_last-$price)/$price_last))))-$equity;
            if($profit < 0 && $price_last > $price){
                $profit = 1 * abs($profit);
                $unrealized=$profit;
            }elseif($profit > 0 &&  $price_last < $price){
                $profit = -1 * abs($profit);
                $unrealized=$profit;
            }

        }elseif($position_last=='leverage'){
            $profit = ($equity*(1+($leverage*($price_last-$price)/$price_last)))-$equity;
            if($profit <0 && $price_last < $price){
                $profit = 1 * abs($profit);
            }
            if($profit > 0 &&  $price_last > $price){
                $profit = -1 * abs($profit);
            }
        }else{
            $profit = 0;
        }

        // POSITION
        if($i>2){
            if($position_last=='holdBTC'){
                if($price > $price_last && $price_last > $price_twice ){
                    $position='leverage';
                }elseif($price < $price_last && $price_last < $price_twice ){
                    $position='protect';
                }else{
                    $position='holdBTC';
                }
            }elseif(sign($profit_twice) == sign($profit_last) ){
                if($profit_twice > 0 && $profit_last > 0){
                    $position='leverage';
                }else{
                    $position='protect';
                }
            }else{
                $position='holdBTC';
            }

        }else{
            $position='protect';
        }

        if(sign($profit_twice)>0){$sign_twice = "TRUE";}else{$sign_twice = "FALSE"; }
        if(sign($profit_last)>0){$sign_last = "TRUE";}else{$sign_last = "FALSE"; }
        if(sign($profit)>0){$sign = "TRUE";}else{$sign = "FALSE"; }


        if($compound){
            $equity = $equity + $profit;
        }


        $profit>0?$bg='gain': $bg='loss';
        $profit==0?$bg='info': $i=$i;
        if($unrealized !=0){
            $profitDisplay = '';
            $unrealized = $unrealized/$price;
            if($unrealized>0){
                $unrealized = '+'.abs($unrealized);
            }

        }else{
            $profitDisplay = money_format('%.2n', $profit);
            $balance = $balance+$profit;
        }
        $unrealizedCount = $unrealizedCount+$unrealized;
        $table .= "
                <tr class='results'>
                    <td class='results'>$i</td>
                    <td class='results'>".date("M jS y' @ H:i", strtotime($time))."</td>
                    <td class='results'>".money_format('%.2n', $price)."</td>
                    <td class='results $bg'>".$profitDisplay."</td>
                    <td class='results'>".$position."</td>
                    <td class='results'>".money_format('%.2n', $equity)."</td>
                    <td class='results balance'>".money_format('%.2n', $balance)."</td>
                    <td class='results $bg'>".$unrealized."</td>
                </tr>";

        if($time >= date("Y-m-d H:i:s")){
            break;
        }
    }
    $table .= "</table>";
    print "<h2>How did you do?</h2><hr>";
    $btc_diff = $price - $start_price;
    if($btc_diff > 0){
        $direction1 = 'went up';
        $direction2 = 'gain';
    }else{
        $direction1 = 'dropped';
        $direction2 = 'loss';
    }
    $percentChange = (1 - $start_price / $price) * 100;
    $percentChange = number_format($percentChange,2);
    $start_price = money_format('%.2n', $start_price);
    $btc_diff = money_format('%.2n', $btc_diff);

    $balance_end = $balance + $equity_start;

    $blanceEndpercentChange = (1 - $equity_start / $balance_end) * 100;
    if($blanceEndpercentChange > 0){
        $direction3 = 'went up';
        $direction4 = 'gain';
    }else{
        $direction3 = 'dropped';
        $direction4 = 'loss';
    }

    if($unrealizedCount>0){
        $unrealizedDirection = 'gain';
    }else{
        $unrealizedDirection = 'loss';
    }

    $btcUnrealizedValue = $unrealizedCount*$price;

    $blanceEndpercentChange = number_format($blanceEndpercentChange,2);
    $totalGains = $btcUnrealizedValue + $balance_end - $equity_start;
    $percentChangeUnrealized = (1 - $equity_start / ($totalGains+$equity_start)) * 100;
    print "During this time BTC $direction1 from $start_price to ".$price = money_format('%.2n', $price)." which is a change of $btc_diff, that would be a $percentChange% $direction2 if you had hodled BTC";
    print "Your trading equity started at ".money_format('%.2n', $equity_start)." and ended at ".money_format('%.2n', $balance_end)." which is a $direction4 of";

    print "<br /><br />If you used GorillaTech, you would have started with ".money_format('%.2n', $equity_start)." and ended with ".money_format('%.2n', $balance_end).", thats a $blanceEndpercentChange% $direction4 in leveraging alone.";
    print "<br />However, there was an unrealized $unrealizedCount $unrealizedDirection in BTC holdings, a current value of ".money_format('%.2n', $btcUnrealizedValue)." which makes the total $unrealizedDirection of ".money_format('%.2n', $totalGains);
    print " or a ".number_format($percentChangeUnrealized,2)."% $unrealizedDirection";
    return $table;

}




$x = date("Y-m-d 00:00:00"); // Start of day
?>
<html>
<head></head>
<body>
<form action='test.php' method='post' enctype="multipart/form-data">
    <table>
        <tr><td colspan='2'>Back test from <input name='days_ago' value='<?php print $days_ago; ?>' style='width:25px;'/> days ago</td></tr>
        <tr><td>Interval (min)</td><td class='input'><input name='interval' value='<?php print $interval; ?>'/></td></tr>
        <tr><td>Equity</td><td class='input'><input name='equity' value='<?php print $equity; ?>'/></td></tr>
        <tr><td>Leverage</td><td class='input'><input name='leverage' value='<?php print $leverage; ?>'/></td></tr>
        <tr><td>Compound</td><td class='input'><input name='compound' value='<?php print $compound; ?>'/></td></tr>
        <tr><td colspan='2'><button class='frmButton' name='submit' type='submit' value='submit'>Submit</button></td></tr>
    </table>
</form>

<?php
print process_time_block(date('Y-m-d H:i:s',strtotime('-'.$days_ago.' days',strtotime($x))), $equity, $leverage, $interval, $compound);
?>
</body>
</html>