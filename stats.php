<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=stats');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

# Include an array containing all data about each city
include_once(INCLUDE_PATH.'/city_info.php');

$PAGE_NAME = 'Stats';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

session_start();
$nowday = date('t');
$nowhour = date('G');
$nowmin = preg_replace('/^0/','',date('i'));
if(date('j') < 16){
	$totalday=15-date('j');
}else{
	$totalday=date('t')-date('j');
}	
$totalhour = 24-($nowhour+1);
$totalmin = 60-$nowmin;
$timeleft = $totalday.' days '.$totalhour.' hrs and '.$totalmin.' min';

#time loaded
date('g:i A');

if(date('d')>=16){
	$FromDay=16;
	$previousFrom = date('Y')."-".date('m')."-1";
	$previousTo = date('Y')."-".date('m')."-15";
}else{
	$FromDay=1;
	$PreviousFromMonth=date('m', strtotime("last day of previous month"));
	$PreviousFromYear=date('Y', strtotime("last day of previous month"));
	$previousFrom = date('Y', strtotime("last day of previous month"))."-".date('m', strtotime("last day of previous month"))."-16";
	$previousTo = date('Y', strtotime("last day of previous month"))."-".date('m', strtotime("last day of previous month"))."-".date('d', strtotime("last day of previous month"));
}

if(date('G') > 14){
	$ToDay = date('Y-m-d', strtotime(date('')." + 6 hours"));
}else{
	$ToDay = date('Y-m-d');
}

$sql_two_weeks = mysqli_query($connect,"SELECT *, 
											(`tdn_payout` + `aff_payout` + `lc_payout` + `ht_payout` + `ter_payout`) AS `rowsum_payout`,
											(`tdn_free` + `aff_free` + `lc_free`+ `ht_free`) AS `rowsum_free`,
											(`tdn_hits` + `aff_hits` + `lc_hits` + `ht_hits` + `ter_hits`) AS `rowsum_hits`,
											(`tdn_orders` + `aff_orders` + `lc_orders` + `ht_orders` + `ter_orders`) AS `rowsum_orders`
										FROM 
											`voz`.`stats` 
										WHERE 
											date >= '".date('Y-m-').$FromDay."' AND date <= '".$ToDay."' 
										ORDER BY 
											date DESC 
										LIMIT 999") or die(mysqli_error());
										
$sql_previous_two_weeks = mysqli_query($connect,"SELECT *, 
											(`tdn_payout` + `aff_payout` + `lc_payout` + `ht_payout` + `ter_payout`) AS `rowsum_payout`,
											(`tdn_free` + `aff_free` + `lc_free`+ `ht_free`) AS `rowsum_free`,
											(`tdn_hits` + `aff_hits` + `lc_hits` + `ht_hits` + `ter_hits`) AS `rowsum_hits`,
											(`tdn_orders` + `aff_orders` + `lc_orders` + `ht_orders` + `ter_orders`) AS `rowsum_orders`
										FROM 
											`voz`.`stats` 
										WHERE 
											date >= '".$previousFrom."' AND date <= '".$previousTo."' 
										ORDER BY 
											date DESC 
										LIMIT 999") or die(mysqli_error());

$sql_two_months = mysqli_query($connect,"SELECT *, 
											(`tdn_payout` + `aff_payout` + `lc_payout` + `ht_payout` + `ter_payout`) AS `rowsum_payout`,
											(`tdn_free` + `aff_free` + `lc_free`+ `ht_free`) AS `rowsum_free`,
											(`tdn_hits` + `aff_hits` + `lc_hits` + `ht_hits` + `ter_hits`) AS `rowsum_hits`,
											(`tdn_orders` + `aff_orders` + `lc_orders` + `ht_orders` + `ter_orders`) AS `rowsum_orders`
										FROM 
											`voz`.`stats` 
										WHERE 
											date >= '".date('Y-m-d', strtotime("first day of -2 month"))."' AND date <= '".$ToDay."' 
										ORDER BY 
											date DESC 
										LIMIT 999") or die(mysqli_error());

while($two_months = mysqli_fetch_array($sql_two_months)){
	$i++;
	$data2m .=  "{'placeval':'".$i."','Day':'".date('jS D', strtotime($two_months["date"]))."','Clicks':'".$two_months["tdn_hits"]."','Payout':'".number_format($two_months["tdn_payout"],0,'.','')."','Orders':'".$two_months["tdn_orders"]."','Free':'".$two_months["tdn_free"]."','Channel':'TDN'},
			{'placeval':'".$i."','Day':'".date('jS D', strtotime($two_months["date"]))."','Clicks':'".$two_months["aff_hits"]."','Payout':'".number_format($two_months["aff_payout"],0,'.','')."','Orders':'".$two_months["aff_orders"]."','Free':'".$two_months["aff_free"]."','Channel':'AFF'},
			{'placeval':'".$i."','Day':'".date('jS D', strtotime($two_months["date"]))."','Clicks':'".$two_months["lc_hits"]."','Payout':'".number_format($two_months["lc_payout"],0,'.','')."','Orders':'".$two_months["lc_orders"]."','Free':'".$two_months["lc_free"]."','Channel':'LC'},
			{'placeval':'".$i."','Day':'".date('jS D', strtotime($two_months["date"]))."','Clicks':'".$two_months["ter_hits"]."','Payout':'".number_format($two_months["ter_payout"],0,'.','')."','Orders':'".$two_months["ter_orders"]."','Free':'0','Channel':'TER'},
			";
}
?>
 
<script language="javascript">
	window.onresize = function () {
		twoMonthsHitsChart.draw(0, true);
	};
	
	$(document).ready(function() {
		var table = $('#2weeks').DataTable( {
			responsive: true,
			paging: false,
			fixedHeader: true,
			searching:false,
			info:false,
			order: [[ 0, "asc" ]],
			fixedColumns: {
				heightMatch: 'none'
			},
			columnDefs: [
				{ responsivePriority: 1, targets: 0 },
				{ responsivePriority: 2, targets: 1 },
				{ responsivePriority: 3, targets: 2 },
				{ responsivePriority: 4, targets: 3 },
				{ responsivePriority: 5, targets: 6 },
				{ visible: false, targets: 0 }
			]
		} );
		$('#2weeks tbody')
		.on( 'mouseenter', 'td', function () {
			var colIdx = table.cell(this).index().column;
			$( table.cells().nodes() ).removeClass( 'highlight' );
			$( table.column( colIdx ).nodes() ).addClass( 'highlight' );
		} );
		
		
	
	
	
	
		var table2 = $('#previous2weeks').DataTable( {
			responsive: true,
			paging: false,
			fixedHeader: true,
			searching:false,
			info:false,
			order: [[ 0, "asc" ]],
			fixedColumns: {
				heightMatch: 'none'
			},
			columnDefs: [
				{ responsivePriority: 1, targets: 0 },
				{ responsivePriority: 2, targets: 1 },
				{ responsivePriority: 3, targets: 2 },
				{ responsivePriority: 4, targets: 3 },
				{ responsivePriority: 5, targets: 6 },
				{ visible: false, targets: 0 }
			]
		} );
		
		
		
		$('#previous2weeks tbody')
		.on( 'mouseenter', 'td', function () {
			var colIdx2 = table.cell(this).index().column;
	
			$( table.cells().nodes() ).removeClass( 'highlight' );
			$( table.column( colIdx2 ).nodes() ).addClass( 'highlight' );
		} );
		
		
	} );
</script>




<div class="panel panel-flat">
    <div class="panel-heading">
		<h5 class="panel-title">Stats</h5>
   		<a href='http://cron.blr.pw/update_stats.php?db=voz'>(Update Stats)</a>
    </div>


	<table id="2weeks" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="text-center" >DayCount</th>
                <th class="text-center" >Date</th>
                <th class="text-center" >Total <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >Total <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >TDN <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >TDN <i class="icon-user-plus text-slate"></i></th>
                <th class="text-center" >TDN <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >FF <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >FF <i class="icon-user-plus text-slate"></i></th>
                <th class="text-center" >FF <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >LC <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >LC <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >TER <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >TER <i class="icon-coin-dollar text-slate"></i></th> 
            </tr>
        </thead>
        <tbody>
		<?php
        while($two_weeks = mysqli_fetch_array($sql_two_weeks)){
            if($total_days_running){
				$aff_hits_running_avg = $aff_hits_running_avg + $two_weeks["aff_hits"];$aff_free_running_avg = $aff_free_running_avg + $two_weeks["aff_free"];$aff_orders_running_avg = $aff_orders_running_avg + $two_weeks["aff_orders"];$aff_payout_running_avg = $aff_payout_running_avg + $two_weeks["aff_payout"];
				$ht_hits_running_avg = $ht_hits_running_avg + $two_weeks["ht_hits"];$ht_free_running_avg = $ht_free_running_avg + $two_weeks["ht_free"];$ht_orders_running_avg = $ht_orders_running_avg + $two_weeks["ht_orders"];$ht_payout_running_avg = $ht_payout_running_avg + $two_weeks["ht_payout"];
                $tdn_hits_running_avg = $tdn_hits_running_avg + $two_weeks["tdn_hits"]; $tdn_free_running_avg = $tdn_free_running_avg + $two_weeks["tdn_free"]; $tdn_orders_running_avg = $tdn_orders_running_avg + $two_weeks["tdn_orders"]; $tdn_payout_running_avg = $tdn_payout_running_avg + $two_weeks["tdn_payout"];
				$lc_hits_running_avg = $lc_hits_running_avg + $two_weeks["lc_hits"];$lc_free_running_avg = $lc_free_running_avg + $two_weeks["lc_free"];$lc_orders_running_avg = $lc_orders_running_avg + $two_weeks["lc_orders"];$lc_payout_running_avg = $lc_payout_running_avg + $two_weeks["lc_payout"];
            	$ter_hits_running_avg = $ter_hits_running_avg + $two_weeks["ter_hits"];$ter_orders_running_avg = $ter_orders_running_avg + $two_weeks["ter_orders"];$ter_payout_running_avg = $ter_payout_running_avg + $two_weeks["ter_payout"];
                $total_income_running_avg = $aff_payout_running_avg + $ter_payout_running_avg + $lc_payout_running_avg + $tdn_payout_running_avg + $ht_payout_running_avg;
                $total_hits_avg = $two_weeks["aff_hits"] + $two_weeks["ht_hits"] + $two_weeks["ter_hits"] + $two_weeks["lc_hits"] + $two_weeks["tdn_hits"];
                $total_hits_running_avg = $total_hits_running_avg+ $total_hits_avg;
                $total_days_running_avg++;		
            }
            $aff_hits_running = $aff_hits_running + $two_weeks["aff_hits"];
            $aff_free_running = $aff_free_running + $two_weeks["aff_free"];
            $aff_orders_running = $aff_orders_running + $two_weeks["aff_orders"];
            $aff_payout_running = $aff_payout_running + $two_weeks["aff_payout"];
            $total_hits = $two_weeks["aff_hits"] + $two_weeks["ht_hits"] + $two_weeks["ter_hits"] + $two_weeks["lc_hits"] + $two_weeks["tdn_hits"];
            $total_hits_running = $total_hits_running+ $total_hits;
			$ht_hits_running = $ht_hits_running + $two_weeks["ht_hits"];
			$ht_free_running = $ht_free_running + $two_weeks["ht_free"];
			$ht_orders_running = $ht_orders_running + $two_weeks["ht_orders"];
			$ht_payout_running = $ht_payout_running + $two_weeks["ht_payout"];
			$tdn_hits_running = $tdn_hits_running + $two_weeks["tdn_hits"];
			$tdn_free_running = $tdn_free_running + $two_weeks["tdn_free"];
			$tdn_orders_running = $tdn_orders_running + $two_weeks["tdn_orders"];
			$tdn_payout_running = $tdn_payout_running + $two_weeks["tdn_payout"];
			$lc_hits_running = $lc_hits_running + $two_weeks["lc_hits"];
			$lc_free_running = $lc_free_running + $two_weeks["lc_free"];
			$lc_orders_running = $lc_orders_running + $two_weeks["lc_orders"];
			$lc_payout_running = $lc_payout_running + $two_weeks["lc_payout"];
			$ter_hits_running = $ter_hits_running + $two_weeks["ter_hits"];
			$ter_orders_running = $ter_orders_running + $two_weeks["ter_orders"];
			$ter_payout_running = $ter_payout_running + $two_weeks["ter_payout"];
            $total_income_running = $aff_payout_running + $ter_payout_running + $lc_payout_running + $tdn_payout_running + $ht_payout_running;
            $total_days_running++;
            ($total_days_running == 1)?	$payout_highlight = 'highlight' : $payout_highlight = '';
            ?>
                <tr>
                	<td><?php print $total_days_running; ?></td>
                    <td style='text-align:left;'><?php print date('jS D', strtotime($two_weeks["date"])); ?></td>
                    <td><strong><?php print "$".number_format($two_weeks["rowsum_payout"],0); ?></strong></td>
                    <td><?php print $two_weeks["rowsum_hits"]; ?></td>
                    <td><?php print $two_weeks["tdn_hits"]; ?></td>
                    <td><?php print $two_weeks["tdn_free"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["tdn_payout"],0); ?></td>
                    <td><?php print $two_weeks["aff_hits"]; ?></td>
                    <td><?php print $two_weeks["aff_free"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["aff_payout"],0); ?></td>
                    <td><?php print $two_weeks["lc_hits"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["lc_payout"],0); ?></td>
                    <td><?php print $two_weeks["ter_hits"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["ter_payout"],0); ?></td>
                </tr>
            <?php
        }
        ?>
            <tr>
				<td><?php print $total_days_running+1; ?></td>
                <td class='totals' style='text-align:left;'><?php print $total_days_running; ?> Days</td>
                <td class='grand_total'>$<?php print number_format($total_income_running,0); ?></td>
                <td class='grand_total'><?php print $total_hits; ?></td>
                <td class='totals'><?php print $tdn_hits_running; ?></td>
                <td class='totals'><?php print $tdn_free_running; ?></td>
                <td class='totals'>$<?php print number_format($tdn_payout_running,0); ?></td>
                <td class='totals'><?php print $aff_hits_running; ?></td>
                <td class='totals'><?php print $aff_free_running; ?></td>
                <td class='totals'>$<?php print number_format($aff_payout_running,0); ?></td>
                <td class='totals'><?php print $lc_hits_running; ?></td>
                <td class='totals'>$<?php print number_format($lc_payout_running,0); ?></td>
                <td class='totals'><?php print $ter_hits_running; ?></td>
                <td class='totals'>$<?php print number_format($ter_payout_running,0); ?></td>
            </tr>
            <?php if($total_days_running > 1){ ?>            
            <tr>
				<td><?php print $total_days_running+2; ?></td>
                <td class='average' style='text-align:left;'><?php print $total_days_running_avg; ?> Day Avg</td>
                <td class='average'>$<?php print round($total_income_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($total_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($tdn_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($tdn_free_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($tdn_payout_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($aff_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($aff_free_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($aff_payout_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($lc_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($lc_payout_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($ter_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($ter_payout_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>	
        
        
        


<!-- Main charts -->
<div class="row">

    <div class="col-lg-12">

        <!-- Sales stats -->
        <div class="panel panel-flat">
            <div class="panel-heading">
                <h6 class="panel-title">Clicks over 2 Months</h6>
            </div>

            <div class="container-fluid">
            <div id="twoMonthsHits">
                <script type="text/javascript">
                var svg = dimple.newSvg("#twoMonthsHits", "100%", 535);
                var data = [<?php print $data2m; ?>];    
                var twoMonthsHitsChart = new dimple.chart(svg, data);
 
                
                var x = twoMonthsHitsChart.addCategoryAxis("x", "Day");
                x.addOrderRule("placeval", true);
    
                var y = twoMonthsHitsChart.addMeasureAxis("y", "Clicks");
                y.tickFormat = ",.f";
    
                twoMonthsHitsChart.addSeries("Channel", dimple.plot.bar);
                twoMonthsHitsChart.addLegend(60, 10, 510, 20, "left");
                twoMonthsHitsChart.draw();    
                </script>
            </div>	
            </div>

        </div>
        <!-- /sales stats -->


    </div>
</div>

        
        
        
        
        
 <div class="panel panel-flat">
    <div class="panel-heading">
        <h5 class="panel-title">Previous 2 weeks</h5>
    </div>
    <table id="previous2weeks" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="text-center" >DayCount</th>
                <th class="text-center" >Date</th>
                <th class="text-center" >Total <i class="icon-coin-dollar text-slate"></th>
                <th class="text-center" >Total <i class="icon-mouse text-slate"></th>
                <th class="text-center" >TDN <i class="icon-mouse text-slate"></th>
                <th class="text-center" >TDN <i class="icon-user-plus text-slate"></th>
                <th class="text-center" >TDN <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >FF <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >FF <i class="icon-user-plus text-slate"></th>
                <th class="text-center" >FF <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >LC <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >LC <i class="icon-coin-dollar text-slate"></i></th>
                <th class="text-center" >TER <i class="icon-mouse text-slate"></i></th>
                <th class="text-center" >TER <i class="icon-coin-dollar text-slate"></i></th> 
            </tr>
        </thead>
        <tbody>
        <?php
        $two_weeks = NULL;
        $total_days_running = 0;
        $total_days_running_avg = 0;
		$tdn_hits_running=NULL;$aff_hits_running=NULL;$lc_hits_running=NULL;$ter_hits_running=NULL;$ht_hits_running=NULL;
		$tdn_free_running=NULL;$aff_free_running=NULL;$lc_free_running=NULL;$ter_free_running=NULL;$ht_free_running=NULL;
		$tdn_orders_running=NULL;$aff_orders_running=NULL;$lc_orders_running=NULL;$ter_orders_running=NULL;$ht_orders_running=NULL;
		$tdn_payout_running=NULL;$aff_payout_running=NULL;$lc_payout_running=NULL;$ter_payout_running=NULL;$ht_payout_running=NULL;
		
		$aff_hits_running_avg = NULL;$aff_free_running_avg = NULL;$aff_orders_running_avg = NULL;$aff_payout_running_avg = NULL;
		$ht_hits_running_avg = NULL;$ht_free_running_avg = NULL;$ht_orders_running_avg = NULL;$ht_payout_running_avg = NULL;
		$tdn_hits_running_avg = NULL; $tdn_free_running_avg = NULL; $tdn_orders_running_avg = NULL; $tdn_payout_running_avg = NULL;
		$lc_hits_running_avg = NULL;$lc_free_running_avg = NULL;$lc_orders_running_avg = NULL;$lc_payout_running_avg = NULL;
		$ter_hits_running_avg = NULL;$ter_orders_running_avg = NULL;$ter_payout_running_avg = NULL;
		$total_income_running_avg = NULL;
		$total_hits_avg = NULL;
		
        while($two_weeks = mysqli_fetch_array($sql_previous_two_weeks)){
            if($total_days_running){
                $aff_hits_running_avg = $aff_hits_running_avg + $two_weeks["aff_hits"];$aff_free_running_avg = $aff_free_running_avg + $two_weeks["aff_free"];$aff_orders_running_avg = $aff_orders_running_avg + $two_weeks["aff_orders"];$aff_payout_running_avg = $aff_payout_running_avg + $two_weeks["aff_payout"];
                $ht_hits_running_avg = $ht_hits_running_avg + $two_weeks["ht_hits"];$ht_free_running_avg = $ht_free_running_avg + $two_weeks["ht_free"];$ht_orders_running_avg = $ht_orders_running_avg + $two_weeks["ht_orders"];$ht_payout_running_avg = $ht_payout_running_avg + $two_weeks["ht_payout"];
                $tdn_hits_running_avg = $tdn_hits_running_avg + $two_weeks["tdn_hits"]; $tdn_free_running_avg = $tdn_free_running_avg + $two_weeks["tdn_free"]; $tdn_orders_running_avg = $tdn_orders_running_avg + $two_weeks["tdn_orders"]; $tdn_payout_running_avg = $tdn_payout_running_avg + $two_weeks["tdn_payout"];
                $lc_hits_running_avg = $lc_hits_running_avg + $two_weeks["lc_hits"];$lc_free_running_avg = $lc_free_running_avg + $two_weeks["lc_free"];$lc_orders_running_avg = $lc_orders_running_avg + $two_weeks["lc_orders"];$lc_payout_running_avg = $lc_payout_running_avg + $two_weeks["lc_payout"];
                $ter_hits_running_avg = $ter_hits_running_avg + $two_weeks["ter_hits"];$ter_orders_running_avg = $ter_orders_running_avg + $two_weeks["ter_orders"];$ter_payout_running_avg = $ter_payout_running_avg + $two_weeks["ter_payout"];
                $total_income_running_avg = $aff_payout_running_avg + $ter_payout_running_avg + $lc_payout_running_avg + $tdn_payout_running_avg + $ht_payout_running_avg;
                $total_hits_avg = $two_weeks["aff_hits"] + $two_weeks["ht_hits"] + $two_weeks["ter_hits"] + $two_weeks["lc_hits"] + $two_weeks["tdn_hits"];
                $total_hits_running_avg = $total_hits_running_avg+ $total_hits_avg;
                $total_days_running_avg++;		
            }
            $aff_hits_running = $aff_hits_running + $two_weeks["aff_hits"];
            $aff_free_running = $aff_free_running + $two_weeks["aff_free"];
            $aff_orders_running = $aff_orders_running + $two_weeks["aff_orders"];
            $aff_payout_running = $aff_payout_running + $two_weeks["aff_payout"];
            $total_hits = $two_weeks["aff_hits"] + $two_weeks["ht_hits"] + $two_weeks["ter_hits"] + $two_weeks["lc_hits"] + $two_weeks["tdn_hits"];
            $total_hits_running = $total_hits_running+ $total_hits;
            $ht_hits_running = $ht_hits_running + $two_weeks["ht_hits"];
            $ht_free_running = $ht_free_running + $two_weeks["ht_free"];
            $ht_orders_running = $ht_orders_running + $two_weeks["ht_orders"];
            $ht_payout_running = $ht_payout_running + $two_weeks["ht_payout"];
            $tdn_hits_running = $tdn_hits_running + $two_weeks["tdn_hits"];
            $tdn_free_running = $tdn_free_running + $two_weeks["tdn_free"];
            $tdn_orders_running = $tdn_orders_running + $two_weeks["tdn_orders"];
            $tdn_payout_running = $tdn_payout_running + $two_weeks["tdn_payout"];
            $lc_hits_running = $lc_hits_running + $two_weeks["lc_hits"];
            $lc_free_running = $lc_free_running + $two_weeks["lc_free"];
            $lc_orders_running = $lc_orders_running + $two_weeks["lc_orders"];
            $lc_payout_running = $lc_payout_running + $two_weeks["lc_payout"];
            $ter_hits_running = $ter_hits_running + $two_weeks["ter_hits"];
            $ter_orders_running = $ter_orders_running + $two_weeks["ter_orders"];
            $ter_payout_running = $ter_payout_running + $two_weeks["ter_payout"];
            $total_income_running = $aff_payout_running + $ter_payout_running + $lc_payout_running + $tdn_payout_running + $ht_payout_running;
            $total_days_running++;
            ($total_days_running == 1)?	$payout_highlight = 'highlight' : $payout_highlight = '';
            ?>
                <tr>
                    <td><?php print $total_days_running; ?></td>
                    <td style='text-align:left;'><?php print date('jS D', strtotime($two_weeks["date"])); ?></td>
                    <td><strong><?php print "$".number_format($two_weeks["rowsum_payout"],0); ?></strong></td>
                    <td><?php print $two_weeks["rowsum_hits"]; ?></td>
                    <td><?php print $two_weeks["tdn_hits"]; ?></td>
                    <td><?php print $two_weeks["tdn_free"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["tdn_payout"],0); ?></td>
                    <td><?php print $two_weeks["aff_hits"]; ?></td>
                    <td><?php print $two_weeks["aff_free"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["aff_payout"],0); ?></td>
                    <td><?php print $two_weeks["lc_hits"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["lc_payout"],0); ?></td>
                    <td><?php print $two_weeks["ter_hits"]; ?></td>
                    <td><?php print "$".number_format($two_weeks["ter_payout"],0); ?></td>
                </tr>
            <?php
        }
        ?>
            <tr>
                <td><?php print $total_days_running+1; ?></td>
                <td class='totals' style='text-align:left;'><?php print $total_days_running; ?> Days</td>
                <td class='grand_total'>$<?php print number_format($total_income_running,0); ?></td>
                <td class='grand_total'><?php print $total_hits; ?></td>
                <td class='totals'><?php print $tdn_hits_running; ?></td>
                <td class='totals'><?php print $tdn_free_running; ?></td>
                <td class='totals'>$<?php print number_format($tdn_payout_running,0); ?></td>
                <td class='totals'><?php print $aff_hits_running; ?></td>
                <td class='totals'><?php print $aff_free_running; ?></td>
                <td class='totals'>$<?php print number_format($aff_payout_running,0); ?></td>
                <td class='totals'><?php print $lc_hits_running; ?></td>
                <td class='totals'>$<?php print number_format($lc_payout_running,0); ?></td>
                <td class='totals'><?php print $ter_hits_running; ?></td>
                <td class='totals'>$<?php print number_format($ter_payout_running,0); ?></td>
            </tr>          
            <tr>
                <td><?php print $total_days_running+2; ?></td>
                <td class='average' style='text-align:left;'><?php print $total_days_running_avg; ?> Day Avg</td>
                <td class='average'>$<?php print round($total_income_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($total_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($tdn_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($tdn_free_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($tdn_payout_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($aff_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($aff_free_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($aff_payout_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($lc_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($lc_payout_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'><?php print round($ter_hits_running_avg/$total_days_running_avg,0,PHP_ROUND_HALF_DOWN); ?></td>
                <td class='average'>$<?php print round($ter_payout_running_avg/$total_days_running_avg,2,PHP_ROUND_HALF_DOWN); ?></td>
            </tr>
        </tbody>
    </table>
</div>        
        
        
        
        
        
        
        
        
        			
    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>