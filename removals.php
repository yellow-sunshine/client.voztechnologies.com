<?php
session_start();
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=removals');
	exit();
} 

# Include site functions
include_once(INCLUDES.'/functions.php');

$PAGE_NAME = 'Removals';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

?>
<script type="text/javascript" src="/assets/js/core/voz.js"></script>
<script type="text/javascript" src="/assets/js/pages/form_select2.js"></script>
<script language="javascript">
function alertContents() {
	if(http_request.readyState == 4) {
		if(http_request.status == 200) {
			remresponse = http_request.responseText.split("|@|");
			if(remresponse[0] == 'removal'){
				switch(remresponse[1]) {
					case 'success':
						changebg('#a2e060', 'addRemovalResult');
						setTimeout(document.getElementById('addRemovalResult').innerHTML = remresponse[2], 500);
						changebg('#fff', 'addRemovalResult');
						break;
					case 'fail':
						changebg('#fc7474', 'addRemovalResult');
						setTimeout(document.getElementById('addRemovalResult').innerHTML = "Failed with:".remresponse[2], 500);
						break;
					default:
						changebg('#fc7474', 'addRemovalResult');
						setTimeout(document.getElementById('addRemovalResult').innerHTML = "Unknown Removal Response: ".remresponse[0], 500);
						break;
				}
			}else if(remresponse[0] == 'search'){
				switch(remresponse[1]) {
					case 'success': 	document.getElementById('searchRemovalResult').innerHTML = remresponse[2];
									changebg('#a2e060', 'searchResultsTable');
									setTimeout(function(){changebg('#fff', 'searchResultsTable')},2000);
									break;
					case 'fail':    changebg('#fc7474', 'searchRemovalResult');
									document.getElementById('searchRemovalResult').innerHTML = remresponse[2];
									break;
					default:        changebg('#fc7474', 'searchRemovalResult');
									document.getElementById('searchRemovalResult').innerHTML = remresponse[2];
									break;
				}
			}else{
					
			}
		}
	}
}

function searchRemoval(){
	var poststr = "&phone=" + encodeURI(document.getElementById("phoneSearch").value) + "&action=" + encodeURI('search');
	makePOSTRequest('<?php print BASE_URL; ?>/process_removals.php', poststr);
}
function addRemoval(){
	var sid = document.getElementById("site_id");
	var poststr = "&site_id=" + encodeURI(sid.options[sid.selectedIndex].value) + "&phone=" + encodeURI(document.getElementById("phone").value) + "&action=" + encodeURI('add');
	makePOSTRequest('<?php print BASE_URL; ?>/process_removals.php', poststr);
}
</script>
<style type="text/css">
    .table-xxs > tbody > tr > td {
        padding:6px 2px 6px 4px;
        margin:0px;
        text-align:center;
        font-size:10px;
        white-space: nowrap;
        table-layout:fixed;
    }
	.table-xxs > thead > tr > th {
		padding:6px 2px 6px 4px;
	}
    </style> 
<?php
$sql = "SELECT site_id, site_name FROM sites ORDER BY site_name LIMIT 100";
$result = mysqli_query($connect,$sql) or die (mysqli_error());
$site_info = array();
while($row = mysqli_fetch_array($result)){
	$site_info[$row['site_id']] = $row['site_name'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
		
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#left-icon-tab1" data-toggle="tab"><i class="icon-menu7 position-left"></i> Remove #</a></li>
                        <li><a href="#left-icon-tab2" data-toggle="tab"><i class="icon-mention position-left"></i> Search Removals</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-gear position-left"></i> Stats <span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#left-icon-tab3" data-toggle="tab">Recent Removals</a></li>
                                <li><a href="#left-icon-tab4" data-toggle="tab">Top removers by email</a></li>
                                <li><a href="#left-icon-tab5" data-toggle="tab">Top removers by IP</a></li>
                            </ul>
                        </li>
                    </ul>

                    <div class="tab-content">
                    	<div class="tab-pane active" id="left-icon-tab1">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                        <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" id="phone" placeholder="Phone Number">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);">
                                            <i class="icon-phone2"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <select id="site_id" class="select-fixed-single">
                                           <option value='911'>DELETE ALL IMAGES BAN PHONE</option>
                                            <?php 
                                            foreach($site_info as $key => $val){
                                                if($key == 100){$selected=" selected";}else{$selected="";}
                                                print "<option value='".$key."'$selected>".$val."</option>\n";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">	
                                <div class="col-md-3">
                                    <div>
                                    	<button type="button" onclick="addRemoval()" id='removebtn' class="btn btn-primary"><i class="icon-trash position-left"></i> Remove</button>
                                    </div>
                                    <div id='addRemovalResult' style="padding:5px;"></div>
                                </div>
                            </div>
						</div>
                        <div class="tab-pane" id="left-icon-tab2">
							<div class="row">
                                <div class="col-md-2">
                                    <div class="form-group has-feedback has-feedback-left">
                                       <input type="text" class="form-control input" onkeyup="AcceptDigits(this);" id="phoneSearch" placeholder="Phone Number">
                                        <div class="form-control-feedback" onkeyup="AcceptDigits(this);">
                                            <i class="icon-phone2"></i>
                                        </div>
                                    </div>
                                </div>
               				</div>
                            
                            <div class="row">	
                                <div class="col-md-3">
                                    <div>
                                    	<button type="button" onclick="searchRemoval()" id='searchbtn' class="btn btn-primary"><i class="icon-search4 position-left"></i> Search</button>
                                    </div>
                                    <div id='searchRemovalResult' style="padding:5px;"></div>
                                </div>
                            </div>
                            
                        </div>




                        <div class="tab-pane" id="left-icon-tab3">
							<?php
                            $recent_removals_SQL =  "SELECT b . * , s.site_name
                                                    FROM `voz`.`banned_phone_waiting` b
                                                    INNER JOIN `voz`.`sites` s ON b.site_id=s.site_id
                                                    WHERE b.processed = 1
                                                    ORDER BY b.date_added DESC
                                                    LIMIT 50";
                            $result = mysqli_query($connect,$recent_removals_SQL) or die(mysqli_error()." ".$sql);
							print "<table id='searchResultsTable' class='display nowrap table-bordered table-striped table-xxs'>
									<thead><tr><th>Phone</th><th>Site</th><th>Email</th><th>IP</th><th>Date Added</th></tr></thead>";
                            while($row = mysqli_fetch_array($result)){
								?>
								<tr><td><?php print $row['phone'];?></td>
                                	<td><?php print $row['site_name'];?></td>
                                    <td><?php print $row['email'];?></td>
                                    <td><?php print $row['ip_address'];?></td>
                                    <td><?php print $row['date_added'];?></td></tr>
								<?php
							}
							print "</table>";
                            ?>
                        </div>
                        
                        
                        
                        
                        
                        
                        <div class="tab-pane" id="left-icon-tab4">
							<?php
                            $recent_removals_SQL =  "SELECT COUNT( * ) AS countField, w.email, w.phone, w.date_added, w.ip_address
													FROM `voz`.`banned_phone_waiting` w
													INNER JOIN `voz`.`sites` s ON w.site_id = s.site_id
													GROUP BY w.email
													ORDER BY  `countField` DESC 
													LIMIT 50";
                            $result = mysqli_query($connect,$recent_removals_SQL) or die(mysqli_error()." ".$sql);
							print "<table id='searchResultsTable' class='display nowrap table-bordered table-striped table-xxs'>
									<thead><tr><th>Email</th><th>Submit Count</th><th>phone</th><th>IP</th><th>Date Added</th></tr></thead>";
                            while($row = mysqli_fetch_array($result)){
								?>
                                <tr>
                                    <td><?php print $row['email'];?></td>
                                    <td><?php print $row['countField'];?></td>
                                    <td><?php print $row['phone'];?></td>
                                    <td><?php print $row['ip_address'];?></td>
                                    <td><?php print $row['date_added'];?></td>
                                </tr>
								<?php
							}
							print "</table>";
                            ?>
                        </div>






                        <div class="tab-pane" id="left-icon-tab5">
							<?php
                            $recent_removals_SQL =  "SELECT COUNT( * ) AS countField, w.email, w.phone, w.date_added, w.ip_address
													FROM `voz`.`banned_phone_waiting` w
													INNER JOIN `voz`.`sites` s ON w.site_id = s.site_id
													GROUP BY w.ip_address
													ORDER BY  `countField` DESC 
													LIMIT 50";
                            $result = mysqli_query($connect,$recent_removals_SQL) or die(mysqli_error()." ".$sql);
							print "<table id='searchResultsTable' class='display nowrap table-bordered table-striped table-xxs'>
									<thead><tr><th>Email</th><th>Submit Count</th><th>phone</th><th>IP</th><th>Date Added</th></tr></thead>";
                            while($row = mysqli_fetch_array($result)){
								?>
                                <tr>
                                    <td><?php print $row['email'];?></td>
                                    <td><?php print $row['countField'];?></td>
                                    <td><?php print $row['phone'];?></td>
                                    <td><?php print $row['ip_address'];?></td>
                                    <td><?php print $row['date_added'];?></td>
                                </tr>
								<?php
							}
							print "</table>";
                            ?>
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