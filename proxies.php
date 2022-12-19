<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

include_once($_SERVER['DOCUMENT_ROOT'].'/optionSniper/get_settings.php');

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=stats');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

# Include an array containing all data about each city
include_once(INCLUDE_PATH.'/city_info.php');

$PAGE_NAME = 'Proxies';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

session_start();
?>
<script type="text/javascript" src="/assets/js/core/voz.js"></script>
<script language="javascript">
function alertContents() {
	if(http_request.readyState == 4) {
		if(http_request.status == 200) {
			
			
			renewresponse = http_request.responseText.split("|@|");
			if(renewresponse[0] == 'renewone'){
				switch(renewresponse[1]) {
					case 'success':
						$currentip = document.getElementById('ip-'+renewresponse[4]).innerHTML;
						setTimeout(document.getElementById('ip-'+renewresponse[4]).innerHTML = "Old(" + $currentip + ') New(' + renewresponse[2] + ')', 500);
						setTimeout(document.getElementById('bancount-'+renewresponse[4]).innerHTML = '0', 500);
						break;
					case 'fail':
						setTimeout(document.getElementById('ip-'+renewresponse[4]).innerHTML = 'FAILED', 500);
						break;
					default:
						setTimeout(document.getElementById('ip-'+renewresponse[4]).innerHTML = 'FAILED', 500);
						break;
				}
			}
		}
	}
}
$(document).ready(function() {
    var table = $('#currentProxies').DataTable( {
		responsive: true,
		paging: true,
		fixedHeader: true,
		searching:true,
		info:false,
		order: [[ 4, "desc" ]],
		fixedColumns: {
			heightMatch: 'none'
		},
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 5 },
			{ responsivePriority: 3, targets: 6 },
			{ responsivePriority: 4, targets: 1 },
			{ responsivePriority: 5, targets: 3 },
			{ responsivePriority: 6, targets: 4 },
			{ visible: false, targets: 2 }
        ]
    } );
    $('#currentProxies tbody')
	.on( 'mouseenter', 'td', function () {
		var colIdx = table.cell(this).index().column;
		$( table.cells().nodes() ).removeClass( 'highlight' );
		$( table.column( colIdx ).nodes() ).addClass( 'highlight' );
	} );

} );

</script>
<script language="javascript">
function disenable(proxyID,current_state){	
	var poststr = "action=" + encodeURI("disenable")
				+ "&proxyID=" + encodeURI(proxyID)
				+ "&current_state=" + encodeURI(current_state);
	makePOSTRequest('<?php print BASE_URL; ?>/process_proxies.php', poststr);
}

function renewProxy($proxyid,$proxyip,$proxyport){
	var poststr = "proxyid=" + encodeURI($proxyid)
				+ "&proxyip=" + encodeURI($proxyip)
				+ "&proxyport=" + encodeURI($proxyport);
	makePOSTRequest('<?php print BASE_URL; ?>/proxiesRenewOne.php', poststr);
}
</script>	
    <div class="panel panel-flat">
    <div class="panel-heading" style='text-align:center'>
		<h2>Proxy Management</h2>
		<?php if($_SESSION['user']['graffiti'] != false){ ?>
		<span style='font-size:0.8em;'>
			Proxies currently come from <a href='http://sharedproxies.com'>sharedproxies.com</a><br />
			Proxies are used to get information from backpage.<br />
			Hundreds are used because backpage blocks our IP address because we copy a lot of data. <br />
			With these proxies, backpage thinks we are many many people visiting their website. <br />			
			<br />
			<div style='text-align:left; width:80%; margin: 0 auto;'>
				<h3>How to determine if proxies are bad:</h3>
				<ol>
					<li>In the table below, the Ban Count specifies how many times backpage rejected the proxy</li>
					<li>If the highest Ban Count is 4-7 more than the others, the renew button will delete it and auto get a new one</li>
					<li>Sometimes, 6 or 7 records may need to be renewed.</li>
					<li>Only 500 renewal requests will work per month. Normally only 4 or 5 are renewed weekly</l1>
				</ol>
			</div>
			<div style='text-align:left; width:80%; margin: 0 auto;'>
				<h3>Determine if all proxies are bad / All are shutdown</h3>
				<ul>
					<li>It could be that sharedproxies.com is no longer a viable company</li>
					<li>Look at the number of posts in the last 30 min. If this is 0 for 12 hours, proxies are no longer working</li>
					<li>If All records have a rapidly increasing Ban Count, proxies no longer work</li>
					<li>The renew button means sharedproxies.com either is no longer working or they changed their system that allows renewals of proxies</li>
				</ul>
			</div>
			<div style='text-align:left; width:80%; margin: 0 auto;'>
				<h3>Manually adding new proxies if the current proxies no longer work</h3>
				<ol>
					<li>First step is to <a href='/settings.php'>pause scraping here</a> for 4 hours by entering <strong>14400</strong> in the "Pause all scraping for X seconds" feild and clicking <strong>pause now</strong></li>
					<li>Next go to <a href='/phpmyadmin4voz/'>phpmyadmin for m5</a> and login</li>
					<li>On the bottom left choose the database "<strong>voz</strong>" and click on the table <strong>proxy</strong></li>
					<li>Select all records inside proxy and <strong>delete</strong> them. There are 100-500 (<a href='/proxies-db-view.png'>See picture of what it looks like here!</a>)</li>
					<li>Find new proxies on the internet (try <a href='https://actproxy.com/clientarea.php'>Act Proxy</a>, <a href='http://limeproxies.com/'>Lime Proxy</a>)</li>
					<li>If asked to enter an authorized IP enter "<strong>207.158.37.76</strong>". This is the server's IP address that will be using these proxies.</li>
					<li>Once about 200-300 proxies have been purchased for about $200-$400 monthly they need to be entered:<br />
				
						<ol>
							<li>Go to <a href='/phpmyadmin4voz/'>phpmyadmin for m5</a> and login</li>
							<li>On the bottom left, choose the database "voz" and click on the table proxy</li>
							<li>At the top, click on the <strong>insert</strong> tab next to search and export</li>
							<li>Enter the IP, Port, select the cuurent date in the add_date and ban_date feilds</li>
							<li>Click go and repeat these 5 steps for the next proxy (<a href="/insert-proxy.jpg">Click here to see a diagram for help inserting</a>)</li>
						</ol>
					</li>
					<li>Finnaly turn scraping back on by going to <a href='/settings.php'>settings</a> and entering <strong>1</strong> in the "Pause all scraping for X seconds" feild and clicking <strong>pause now</strong></li>
				</ol>
			</div>
		</span>
		<?php } ?>
	</div>
	<hr>

    <style type="text/css">
    .table-xxs i{
        font-size:13px;	
    }
    .table-xxs > tbody > tr > td{
        padding:6px 2px 6px 4px;
    }
    .table-xxs > tbody > tr > td{
        margin:0px;
        text-align:center;
        font-size:10px;
        white-space: nowrap;
        table-layout:fixed;
    }
	.table-xxs>thead>tr>th{
		/*white-space: nowrap;*/
	}
	
	.dataTable tr td.child > ul {
		background-color:#E6EEF1;
		display: table;
		table-layout: fixed;
		width: 100%;
		list-style: none;
		margin: 0;
		padding: 0;
	}
	.dataTable tr td.child > ul > li {
		display: table-row;
		border:1px solid #CECECE;
	}
	td.highlight {
		background-color: whitesmoke !important;
	}
	.totals{
		background-color:#EFF3E5;
	}
	.grand_total{
		background-color:#D4DAC8;
		font-weight:bold;
	}
    </style> 
	<table id="currentProxies" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="text-center" >IP <i class="icon-coin-dollar text-slate"></th>
                <th class="text-center" >Port <i class="icon-mouse text-slate"></th>
                <th class="text-center" >Website <i class="icon-mouse text-slate"></th>
                <th class="text-center" >Ban Date <i class="icon-user-plus text-slate"></th>
                <th class="text-center" >Ban Seconds <i class="icon-user-plus text-slate"></th>
                <th class="text-center" >Ban Count <i class="icon-user-plus text-slate"></th>
                <th class="text-center" >Enable/Renew <i class="icon-mouse text-slate"></i></th>
            </tr>
        </thead>
        <tbody>
		<?php
        $sqlCurrentProxies = mysqli_query($connect,"SELECT * FROM `voz`.`proxy` ORDER BY `ban_count` DESC LIMIT 1000");
        while($rowCurrentProxies = mysqli_fetch_array($sqlCurrentProxies)){
            $proxyCount++;
			if(!empty($rowCurrentProxies["enabled"])){$enabled="checked='checked'";$checkState='checked';}else{$enabled="";$checkState='unchecked';}
			?>
                <tr>
                    <td id='ip-<?php print $rowCurrentProxies["proxy_id"]; ?>'><?php print $rowCurrentProxies["ip"]; ?></td>
                    <td><?php print $rowCurrentProxies["port"]; ?></td>
                    <td><?php print $rowCurrentProxies["website"]; ?></td>
                    <td id='bandate-<?php print $rowCurrentProxies["proxy_id"]; ?>'><?php print date('D, M jS h:i a', strtotime($rowCurrentProxies["ban_date"])); ?></td>
                    <td id='bantime-<?php print $rowCurrentProxies["proxy_id"]; ?>'><?php print $rowCurrentProxies["ban_time"]; ?></td>
                    <td id='bancount-<?php print $rowCurrentProxies["proxy_id"]; ?>'><?php print $rowCurrentProxies["ban_count"]; ?></td>
                    <td><div>
                            <input  id='disable-<?php print $rowCurrentProxies["proxy_id"]; ?>' type="checkbox" <?php print $enabled; ?> onchange="disenable(<?php print $rowCurrentProxies["proxy_id"]; ?>,'<?php print $checkState; ?>')">
                        	<button id='proxy-<?php print $rowCurrentProxies["proxy_id"]; ?>' 
                        	onclick="renewProxy('<?php print $rowCurrentProxies["proxy_id"]; ?>', '<?php print $rowCurrentProxies["ip"]; ?>', '<?php print $rowCurrentProxies["port"]; ?>')">Renew</button>
                        </div>
    				</td>
                </tr>
            <?php
        }
        ?>
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