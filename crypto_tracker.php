<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
session_start();
/**
	Gets live data from coin market cap and inserts it into a DB. 
	If a new coin makes it way to the top, it will add it to the coins and also make a record in coin stats
	This will not delete old coins. If a coin is deleted from the DB, a contraint will delete all stats for that coin also.
	This should be set up on a cron job to auto archive this data every day/hour/12h or whatever else is deemed sufficient.
**/

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
# Get the TD Ameritrade object for using the API
include_once($_SERVER['DOCUMENT_ROOT'].'/optionSniper/tda.class.php');
$tda = new tda($conn);

/* 
$host = "localhost";
$dbUser = "voz";
$password = "2r5sT6bjeCGVbeUe";
$database="ct";

$dbConn = new mysqli($host,$dbUser,$password,$database);
 
if($dbConn->connect_error){
	die("Database Connection Error, Error No.: ".$dbConn->connect_errno." | ".$dbConn->connect_error);
}
*/





if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location:http://'.ADMIN_DOMAIN.'/?r=crypto_tracker');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

$PAGE_NAME = 'Crypto Tracker';

# Include the header
include_once(INCLUDE_PATH.'/header.php');


// Get a list of coins from the URL
$coins = explode(',', strtolower($_GET['coins']));
$count_count = count($coins);
if(!$_GET['coins']){
	$count_count = 0;
}
$coinSQL = "SELECT 
			c.symbol_id, c.symbol, c.available_supply, c.total_supply, c.max_supply,
			s.rank, s.price_usd, s.price_btc, s.24h_volume_usd, s.market_cap_usd, s.percent_change_1h, 			    
			s.percent_change_24h, s.percent_change_7d, s.last_updated,
			u.update_date, u.update_id, 
			(s.24h_volume_usd / s.market_cap_usd) volumMC 
			FROM `ct`.`update_groups` u
			INNER JOIN `ct`.`coin_stats` s ON s.update_id = u.update_id
			INNER JOIN `ct`.`coins` c ON c.symbol_id = s.symbol_id
			ORDER BY u.update_id DESC
			LIMIT 500";
$rs = $conn->query($coinSQL);

// Loop and create an array for the coins we found in the db
while ($record = $rs->fetch_assoc()){
	$stats[$record['symbol_id']]['symbol_id'] = $record['symbol_id'];
	$stats[$record['symbol_id']]['symbol'] = $record['symbol'];
	$stats[$record['symbol_id']]['available_supply'] = $record['available_supply'];
	$stats[$record['symbol_id']]['total_supply'] = $record['total_supply'];
	$stats[$record['symbol_id']]['max_supply'] = $record['max_supply'];
	$stats[$record['symbol_id']]['rank'] = $record['rank'];
	$stats[$record['symbol_id']]['price_usd'] = $record['price_usd'];
	$stats[$record['symbol_id']]['price_btc'] = $record['price_btc'];
	$stats[$record['symbol_id']]['24h_volume_usd'] = $record['24h_volume_usd'];
	$stats[$record['symbol_id']]['market_cap_usd'] = $record['market_cap_usd'];
	$stats[$record['symbol_id']]['percent_change_1h'] = $record['percent_change_1h'];
	$stats[$record['symbol_id']]['percent_change_24h'] = $record['percent_change_24h'];
	$stats[$record['symbol_id']]['percent_change_7d'] = $record['percent_change_7d'];
	$stats[$record['symbol_id']]['last_updated'] = $record['last_updated'];
	$stats[$record['symbol_id']]['update_id'] = $record['update_id'];
	$stats[$record['symbol_id']]['volumMC'] = $record['volumMC'];
	$update_date = $record['update_date'];
	if($record['percent_change_1h'] > 0){$pos1hr++;}else{$neg1hr++;}
	if($record['percent_change_24h'] > 0){$pos24hr++;}else{$neg24hr++;}
	if($record['percent_change_7d'] > 0){$pos7day++;}else{$neg7day++;}
	$tpc = $tpc+$record['percent_change_7d']; // Keep a running total of % change on the 7day
	$tpc24 = $tpc24+$record['percent_change_24h']; // Keep a running total of % change on the 24hr
	$tpc1 = $tpc1+$record['percent_change_1h']; // Keep a running total of % change on the 7day
	$tmc = $tmc + $record['market_cap_usd'];
	$i++;
}
$apc = $tpc/$i; $apc24 = $tpc24/$i; $apc1 = $tpc1/$i;
?>
    <div class="panel panel-flat">
		<div class="panel-heading" style='text-align:center'>
			<h2><?php print date('D M jS Y - g:i a', strtotime($update_date) - 4 * 3600);?></h2>
			<style>
				#overview{
					text-align: center;
					margin:0 auto;
				}
				#overview th{
					color:#888;
					text-align: center;
				}
				#overview td{
					font-size: 12px;
					padding:3px 15px;
					text-align: center;
					border:1px solid #f0f0f0;
				}
				#overview tr{
					border-bottom:1px solid #aaa;
				}
				#overview tfoot td{
					padding:20px 10px;
					border:1px solid #f9f9f9;
					font-weight: bold;
				}
				#myTable a{
					font-weight:bold;
				}
				#myTable tr:hover, #myTable tr:hover a, .highlightCrypto, .highlightCrypto a{
					color:#000;
					background-color:#F0F0F0;
					cursor:pointer;
				}
				#myTable th{
					text-align: center;
				}
				.HighlightStatic{
					color:#000;
					background-color:#BEFF7A !important;
				}
			</style>

			<table id='overview'>
				<tbody>
					<tr>
						<th></th>
						<th>7 Day</th>
						<th>24 hr</th>
						<th>1 hr</th>
					</tr>
					<tr>
						<td>Average % Changes</td>
						<td><?php print number_format($apc, 2); ?></td>
						<td><?php print number_format($apc24, 2); ?></td>
						<td><?php print number_format($apc1, 2); ?> </td>
					</tr>
					<tr>
						<td>+ Growth</td>
						<td><?php print $pos7day; ?></td>
						<td><?php print $pos24hr; ?></td>
						<td><?php print $pos1hr; ?></td>
					</tr>
					<tr>
						<td>- Growth</td>
						<td><?php print $neg7day; ?></td>
						<td><?php print $neg24hr; ?></td>
						<td><?php print $neg1hr; ?></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td>Total Market Cap</td>
						<td colspan="3"><?php print number_format($tmc); ?></td>
					</tr>
					<!--tr>
						<td colspan="4"><a href='http://lcmc.blr.pw/get_coin_data.php' target='_blank'>Force Update Here</a></td>
					</tr-->
				</tfoot>
			</table>
		</div>


		<table id="myTable" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th> Name</th>
					<th> Symbol</th>
					<th> Rank</th>
					<th> Supply</th>
					<th> Price</th>
					<th> 24h Vol</th>
					<th> Market Cap</th>
					<th> % Change 1hr</th>
					<th> % Change 24hr</th>
					<th> % Change 7 Day</th>
					<th> volumMC</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($stats as $key=>$val){ 
				# Dont show coins that were not passed in the URL
				if(!in_array(strtolower($val['symbol']),$coins) && $count_count > 0){
					continue; 
				}
			?>
					<tr>
						<td>
							<a href='http://coinmarketcap.com/currencies/<?php print $val['symbol_id']; ?>' target='_blank' rel='nofollow'>
								<?php print ucwords($val['symbol_id']); ?>
							</a>
					   </td>
						<td><?php print $val['symbol']; ?></td>
						<td><?php print $val['rank']; ?></td>
						<td><?php print $val['available_supply']; ?></td>
						<td><?php print $val['price_usd']; ?></td>
						<td><?php print $val['24h_volume_usd']; ?></td>
						<td><?php print $val['market_cap_usd']; ?></td>
						<td><?php print $val['percent_change_1h']; ?></td>
						<td><?php print $val['percent_change_24h']; ?></td>
						<td><?php print $val['percent_change_7d']; ?></td>
						<td><?php print $val['volumMC']; ?></td>
					</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
</body>
<script>
	
$(document).ready(function() {
    var lastIdx = null;
    var table = $('#myTable').DataTable({        
		"order": [[ 2, "asc" ]],
			responsive: true,
			paging: true,
			info:false,
			fixedColumns: {
			heightMatch: 'none'
			},			
			"order": [[ 2, "asc" ]],
			"pageLength": 20,
			"iDisplayLength": 20,
		"aLengthMenu": [[10, 20, 50, 100,500,-1], [10, 20, 50,100,500, "All"]]
	});
    $('#myTable tbody').on('mouseover', 'td', function() {
        var colIdx = table.cell(this).index().column;
        if (colIdx !== lastIdx) {
            $(table.cells().nodes()).removeClass('highlightCrypto');
            /*$(table.column(colIdx).nodes()).addClass('highlightCrypto');*/
        }
    }).on('mouseleave', function() {
        $(table.cells().nodes()).removeClass('highlight');
    });

    $('#myTable tbody').on('click', 'tr', function() {
		$(this).toggleClass('HighlightStatic');
	});    
	
	
	
});
</script>
</html>