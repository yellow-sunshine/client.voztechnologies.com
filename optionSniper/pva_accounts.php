<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL,~E_NOTICE);
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/optionSniper/get_settings.php');
# Include site functions
include_once(INCLUDES.'/functions.v2.php');

# Get all of the voz settings from the DB
include_once(INCLUDES.'/voz_settings.php');

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=tweets');
	exit();
}


$PAGE_NAME = 'Twitter PVA Accounts';

# Include the header
include_once(INCLUDE_PATH.'/header.php');
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

function disenable(proxyID,current_state){
	var poststr = "action=" + encodeURI("disenable")
				+ "&account_id=" + encodeURI(proxyID)
				+ "&current_state=" + encodeURI(current_state);
	makePOSTRequest('<?php print BASE_URL; ?>/optionSniper/process_pva.php', poststr);
}



$(document).ready(function() {
    var table = $('#currentTwitterAccounts').DataTable( {
		responsive: true,
		paging: true,
		fixedHeader: true,
		searching:true,
		info:false,
		order: [[ 0, "desc" ]],
		fixedColumns: {
			heightMatch: 'none'
		},
		"pageLength": 20,
		"iDisplayLength": 20,
		"aLengthMenu": [[10, 20, 50, 100, 200, 500,-1], [10, 20, 50,100, 200, 500, "All"]],
		columnDefs: [
			{ responsivePriority: 1, targets: 0 },
			{ responsivePriority: 2, targets: 1 },
			{ responsivePriority: 3, targets: 2 },
			{ responsivePriority: 4, targets: 3},
			{ responsivePriority: 5, targets: 4},
        ]
    } );
    $('#currentTwitterAccounts tbody')
	.on( 'mouseenter', 'td', function () {
		var colIdx = table.cell(this).index().column;
		$( table.cells().nodes() ).removeClass( 'highlight' );
		$( table.column( colIdx ).nodes() ).addClass( 'highlight' );
	} );

} );

</script>
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


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">

						<?php $active='pva'; require_once($_SERVER['DOCUMENT_ROOT'].'optionSniper/tab_nav.php'); ?>

                    </ul>

                    <div class="tab-content">
                    	<div class="tab-pane active" id="left-icon-tab1">















	<table id="currentTwitterAccounts" class="display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
        <thead>
            <tr>
				<th class="text-center"><i class="icon-envelop5 text-slate"></i> Email</th>
				<th class="text-center"><i class="icon-users text-slate"></i> Username</th>
				<th class="text-center"><i class="icon-twitter text-slate"></i> Password</th>
				<th class="text-center"><i class="icon-phone text-slate"></i> Phone</th>
               	<th class="text-center"><i class="icon-checkmark4 text-slate"></i> Enable</th>
                <th class="text-center none"><i class="icon-hash text-slate"></i> Times Used</th>
                <th class="text-center none"><i class="icon-calendar52 text-slate"></i> Date Last Used</th>
                <th class="text-center none"><i class="icon-shield2 text-slate"></i> Auth Token</th>
                <th class="text-center none"><i class="icon-shield2 text-slate"></i> Auth Secret</th>
                <th class="text-center none"><i class="icon-shield2 text-slate"></i> Consumer Key</th>
                <th class="text-center none"><i class="icon-shield2 text-slate"></i> Consumer Secret</th>
            </tr>
        </thead>
        <tbody>
		<?php
        $sqlTwitterAccounts = mysqli_query($connect,"SELECT * FROM `twitter_sniper`.`twitter_accounts` LIMIT 1000");
        while($rowTwitter = mysqli_fetch_array($sqlTwitterAccounts)){
            $proxyCount++;
			if(!empty($rowTwitter["enabled"])){$enabled="checked='checked'";$checkState='checked';}else{$enabled="";$checkState='unchecked';}
			?>
                <tr>
                    <td style="text-align:left;" id='ip-<?php print $rowTwitter["account_id"]; ?>'><?php print $rowTwitter["email"]; ?></td>
                    <td><?php print $rowTwitter["username"]; ?></td>
                    <td><?php print $rowTwitter["twitter_password"]; ?></td>
                    <td><?php print $rowTwitter["phone"]; ?></td>
                    <td>
                        <div>
                            <input  id='disable-<?php print $rowTwitter["account_id"]; ?>'
                            		type="checkbox" <?php print $enabled; ?>
                            		onchange="disenable(<?php print $rowTwitter["account_id"]; ?>,'<?php print $checkState; ?>')">
                        </div>
    				</td>
                    <td><?php print number_format($rowTwitter["times_used"]); ?></td>
                    <td>
						<?php
							$last_used = new DateTime($rowTwitter["last_update_date"]);
							print $last_used->format('Y-m-d H:i:s');
						?>
                    </td>
                    <td><?php print $rowTwitter["oauth_access_token"]; ?></td>
                    <td><?php print $rowTwitter["oauth_access_token_secret"]; ?></td>
                    <td><?php print $rowTwitter["consumer_key"]; ?></td>
                    <td><?php print $rowTwitter["consumer_secret"]; ?></td>
                </tr>
            <?php
        }
        ?>
        </tbody>
    </table>



























						</div>
                	</div>
                </div>
            </div>
        </div>
	</div>
</div>


<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>