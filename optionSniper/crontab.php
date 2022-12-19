<?php
/*
* * * * * /usr/bin/wget -nv -t 2 --connect-timeout=2 -w 1 -nd --no-cache --no-cookies --delete-after -U "VOZCRONOS" "http://client.voztechnologies.com/optionSniper/get_tweets.php?ta=1&cron=1&sec=0" >/dev/null 2>&1
* * * * * sleep 59; /usr/bin/wget -nv -t 2 --connect-timeout=2 -w 1 -nd --no-cache --no-cookies --delete-after -U "VOZCRONOS" "http://client.voztechnologies.com/optionSniper/get_tweets.php?ta=2&cron=1&sec=59" >/dev/null 2>&1
*/
$taccount[1]="0,     30,    15.5,   45.5";
$taccount[2]="1,     31,    16.5,   46.5";
$taccount[3]="2,     32,    17.5,   47.5";
$taccount[4]="3,     33,    18.5,   48.5";
$taccount[5]="4,     34,    19.5,   49.5";
$taccount[6]="5,     35,    20.5,   50.5";
$taccount[7]="6,     36,    21.5,   51.5";
$taccount[8]="7,     37,    22.5,   52.5";
$taccount[9]="8,     38,    23.5,   53.5";
$taccount[10]="9,    39,    24.5,   54.5";
$taccount[11]="10,   40,    25.5,   55.5";
$taccount[12]="11,   41,    26.5,   56.5";
$taccount[13]="12,   42,    27.5,   57.5";
$taccount[14]="13,   43,    28.5,   58.5";
$taccount[15]="14,   44,    29.5,   59.5";
$taccount[16]="15,   45,    30.5,   0.5";
$taccount[17]="16,   46,    31.5,   1.5";
$taccount[18]="17,   47,    32.5,   2.5";
$taccount[19]="18,   48,    33.5,   3.5";
$taccount[20]="19,   49,    34.5,   4.5";
$taccount[21]="20,   50,    35.5,   5.5";
$taccount[22]="21,   51,    36.5,   6.5";
$taccount[23]="22,   52,    37.5,   7.5";
$taccount[24]="23,   53,    38.5,   8.5";
$taccount[25]="24,   54,    39.5,   9.5";
$taccount[26]="25,   55,    40.5,   10.5";
$taccount[27]="26,   56,    41.5,   11.5";
$taccount[28]="27,   57,    42.5,   12.5";
$taccount[29]="28,   58,    43.5,   13.5";
$taccount[30]="29,   59,    44.5,   14.5";

foreach($taccount as $account => $seconds){
    $seconds=explode(',',$seconds);
    print "# ******** Account {$account} **********<br />";
    print "* * * * * sleep {$seconds[0]}; /usr/bin/wget -nv -t 2 --connect-timeout=2 -w 1 -nd --no-cache --no-cookies --delete-after -U 'VOZCRONOS' 'https://client.voztechnologies.com/optionSniper/get_tweets.php?ta={$account}&cron=1&sec={$seconds[0]}' >/dev/null 2>&1";
    print "<br />";
    print "* * * * * sleep {$seconds[1]}; /usr/bin/wget -nv -t 2 --connect-timeout=2 -w 1 -nd --no-cache --no-cookies --delete-after -U 'VOZCRONOS' 'https://client.voztechnologies.com/optionSniper/get_tweets.php?ta={$account}&cron=1&sec={$seconds[1]}' >/dev/null 2>&1";
    print "<br />";
    print "* * * * * sleep {$seconds[2]}; /usr/bin/wget -nv -t 2 --connect-timeout=2 -w 1 -nd --no-cache --no-cookies --delete-after -U 'VOZCRONOS' 'https://client.voztechnologies.com/optionSniper/get_tweets.php?ta={$account}&cron=1&sec={$seconds[2]}' >/dev/null 2>&1";
    print "<br />";
    print "* * * * * sleep {$seconds[3]}; /usr/bin/wget -nv -t 2 --connect-timeout=2 -w 1 -nd --no-cache --no-cookies --delete-after -U 'VOZCRONOS' 'https://client.voztechnologies.com/optionSniper/get_tweets.php?ta={$account}&cron=1&sec={$seconds[3]}' >/dev/null 2>&1";
    print "<br /><br /><br />";
}
?>