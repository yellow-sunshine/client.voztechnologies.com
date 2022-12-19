<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
require_once(__DIR__.'/tda.class.php');
include_once(__DIR__.'/get_settings.php');
sleep(1);
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
    print "Not gathering quote history when the market is closed";
    exit();
}

$quotes = $tda->quote_info_multi(HISTORIC_SYMBOLS_CS,1);

foreach($quotes as $key => $quote){
    $values .= " ('$key', '".$quote['askPrice']."', '".$quote['totalVolume']."', '".date('Y-m-d H:i:s')."' ),";
    print " ('$key', '".$quote['askPrice']."', '".$quote['totalVolume']."', '".date('Y-m-d H:i:s')."' ),"."<br /><pre>";
    print_r($quote);
    print "</pre>";
}

$sql = "
INSERT INTO quote_history
    (`quote`,`ask`,`totalVolume`,`date`)
VALUES
    ".rtrim($values,',')."
";

$conn->query($sql);
exit();
?>