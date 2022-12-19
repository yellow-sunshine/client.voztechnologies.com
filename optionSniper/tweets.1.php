<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL, ~E_NOTICE);


session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once(__DIR__.'/get_settings.php');

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=tweets');
	exit();
}

# Include site functions
include_once(__DIR__.'/functions.v2.php');

$PAGE_NAME = 'Tweets';

# Include the header
include_once(INCLUDE_PATH.'/header.php');

$sql_tweets = mysqli_query($connect,"SELECT t.*, t.tweet_id tid
										FROM
											`twitter_sniper`.`tweets` t
										WHERE
											t.`entry_type` IN('buy','sell','buy p','buy c')
										ORDER BY
											t.`date_tweeted` DESC
										LIMIT 750");
?>

<script language="javascript">
	function addMSG(tweetRow){
		var msg = document.getElementById(tweetRow+'-newMSG');
		var notes2 = document.getElementById(tweetRow+'-notes2');
		var tweet_id = document.getElementById(tweetRow+'-tweetid').innerHTML;

		$.post("/optionSniper/add_note.php",
		{
			tweet_id: tweet_id,
			username: "<?php print $_SESSION['user']['username']; ?>",
			note: msg.value
		},
		function(data, status){
			if(data && status){
				notes2.innerHTML = data;
				$('#'+tweetRow+'-dummy').append("<div class='bubble me' id=''>\
														<div class='user-container pull-left'>\
																<img src='/assets/images/profiles/<?php print $_SESSION['user']['username']; ?>.jpg' width='38' height='38' class='img-circle' alt=''>\
														</div><span class='note-container text-right pull-left'>" + msg.value + "</span>\
													</div>\
													");
				document.getElementById(tweetRow+'-newMSG').value= '';
				var audio = new Audio('/optionSniper/send.mp3');
				audio.play();
			}else{
				alert('Could not save message: ' + data);
			}
		});
	}
	$(document).ready(function() {
		var table = $('#twittertbl').DataTable( {
			responsive: true,
			select:"single",
			paging: true,
			info:false,
			order: [[ 0, "asc" ]],
			fixedColumns: {
			heightMatch: 'none'
			},
			"order": [[ 1, "desc" ]],
			"pageLength": 20,
			"iDisplayLength": 20,
			"aLengthMenu": [[10, 20, 50, 100, 200, 500,-1], [10, 20, 50,100, 200, 500, "All"]],
			responsive: {
				details: {
					renderer: function ( api, rowIdx, columns ) {
						var data = $.map( columns, function ( col, i ) {
							if(col.title == 'Notes'){
								col.data = document.getElementById(rowIdx+'-notes2').innerHTML;
								var notes_data = JSON.parse(col.data);
								var chat='';
								var messages='';
								var loggedInUser = '<?php print $_SESSION['user']['username']; ?>';
								Object.keys(notes_data).forEach(function (key){
									chat = notes_data[key];
									var note, mdate, username;
									Object.keys(chat).forEach(function (innerKey){
										switch(innerKey){
											case 'username' : username=chat[innerKey];break;
											case 'date' : mdate=chat[innerKey];break;
											case 'note' : note=chat[innerKey];break;
										}
									});
									if(loggedInUser == username){
										speaker = 'me';
										speaker_color ='success';
										text_pos='text-right';
										user_pos='pull-left';
										note_pos='pull-left';
									}else{
										speaker = 'you';
										speaker_color ='primary';
										text_pos='text-left';
										user_pos='pull-right';
										note_pos='pull-right';
									}
									thismsg =   "<div class='bubble "+speaker+"' id='"+key+"'>\
													<div class='user-container "+user_pos+"'>\
															<img src='/assets/images/profiles/"+username+".jpg' width='38' height='38' class='img-circle' alt=''>\
													</div>\
													<span class='note-container "+text_pos+" "+note_pos+"'>"+note+"</span>\
													\
												</div>\
												";
									messages = messages + thismsg;
								});

								if(col.hidden){
									return "\
										<tr data-dt-row='"+col.rowIndex +"' data-dt-column='"+col.columnIndex+"' class='notesTR'>\
											<td colspan='2'><div class='chat' id='"+rowIdx+"-chat'>" + messages + "<div id='"+rowIdx+"-dummy'></div></div></td>\
										</tr>\
										<tr class='formTR'>\
											<td colspan='2'>\
													<div class='input-group ml-20 mt-15'>\
														<input type='text' id='"+rowIdx+"-newMSG' class='form-control' style='width:400px;' placeholder='Your Message'>\
														<button type='submit' onclick=\"addMSG('"+rowIdx+"')\" class='btn btn-primary'>Submit <i class='icon-arrow-right14 position-right'></i></button>\
													</div>\
											</td>\
										</tr>";
								}else{
									return "";
								}

							}else{
								return col.hidden ?
								'<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'" class=\'childRow\'>'+
									'<td class=\'childHead\'>'+col.title+'</td>'+
									'<td class=\'childBody\'>'+col.data+'</td>'+
								'</tr>' :
								'';
								}

						} ).join('');
						return data ?
							$('<table/>').attr( 'id', rowIdx+'-child' ).append( data ) :
							false;
					}
				}
			},
			columnDefs: [
				{ responsivePriority: 1, targets: 0 },
				{ responsivePriority: 2, targets: 1 },
				{ responsivePriority: 3, targets: 2 },
				{ responsivePriority: 4, targets: 5 },
				{ responsivePriority: 6, targets: 7 },
				{ visible: false, targets: 0 }
			]
		} );

		var table = $('#twittertbl').DataTable();
		$('#twittertbl tbody').on('click', 'tr', function () {
			//var data = table.row( this ).data();

			//document.getElementById[data[0]+'-notes1'].innerHTML = document.getElementById[data[0]+'-notes2'].innerHTML;
			//alert("here it is: " + document.getElementById['3-notes1'].innerHTML );
		} );




	} );



</script>
<style>
#twittertbl .sell:hover{
	background-color:#BD0003 !important;
	color:#fff;
}
#twittertbl .buy:hover{
	background-color:#419B00 !important;
	color:#fff;
}

.smltxt{
	font-size: .8em;
}



.chat {
    max-width: 550px;
	margin-top:10px;
}

.bubble{
    background-color: #F2F2F2;
    border-radius: 5px;
    box-shadow: 0 0 6px #B2B2B2;
    display: inline-block;
    padding: 0 3px 5px 3px;
    position: relative;
    vertical-align: top;
	clear:both;
   white-space: pre-wrap;      /* CSS3 */
   white-space: -moz-pre-wrap; /* Firefox */
   white-space: -pre-wrap;     /* Opera <7 */
   white-space: -o-pre-wrap;   /* Opera 7 */
   word-wrap: break-word;      /* IE */

}

.bubble::before {
    background-color: #F2F2F2;
    content: "\00a0";
    display: block;
    height: 16px;
    position: absolute;
    top: 13px;
    transform:             rotate( 29deg ) skew( -35deg );
        -moz-transform:    rotate( 29deg ) skew( -35deg );
        -ms-transform:     rotate( 29deg ) skew( -35deg );
        -o-transform:      rotate( 29deg ) skew( -35deg );
        -webkit-transform: rotate( 29deg ) skew( -35deg );
    width:  20px;
}

.me {
    float: left;
    margin: 5px 45px 5px 20px;
    text-align:left;
}

.me::before {
    box-shadow: -2px 2px 2px 0 rgba( 178, 178, 178, .4 );
    left: -9px;
}

.you {
    float: right;
    margin: 5px 20px 5px 45px;
    text-align:right;
}

.you::before {
    box-shadow: 2px -2px 2px 0 rgba( 178, 178, 178, .4 );
    right: -9px;
}

.user-container{
	max-width:50px;
	float:right;
	padding:5px;
}
.note-container{
	float:left;
	max-width:425px;
}
.childRow{
	border:1px dotted #e3e3e3 !important;
}
.childHead{
	font-weight:bold;
	padding:5px;
}

.childBody{
	padding:5px;
}



</style>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
						<?php $active='tweets'; require_once($_SERVER['DOCUMENT_ROOT'].'optionSniper/tab_nav.php'); ?>
                    </ul>
					<div class="tab-content">
						<div class="tab-pane active" id="left-icon-tab1">


							<table id="twittertbl" class="twittertbl display nowrap table-bordered table-togglable table-striped table-hover table-xxs" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th class="text-center">List ID</th>
										<th class="text-center"><i class='icon-calendar text-slate'></i> Date</th>
										<th class="text-center"><i class="icon-list2 text-slate"></i> Type</th>
										<th class="text-center"><i class="icon-pushpin text-slate"></i> Symbol</th>
										<th class="text-center"><i class="icon-coin-dollar text-slate"></i> Strike Price</th>
										<th class="text-center"><i class="icon-coin-dollar text-slate"></i> Quote @ Tweet Time</th>
										<th class="text-center"><i class="icon-coin-dollar text-slate"></i> Contract Price</th>
										<th class="text-center"><i class="icon-coin-dollar text-slate"></i> Contract Price @ Tweet Time</th>
										<th class="text-center"><i class="icon-alarm text-slate"></i> Expiration</th>
										<th class="text-center"><i class="icon-magic-wand2 text-slate"></i> Action Taken</th>

										<th class="text-center none">Contract Count</th>
										<th class="text-center none">Tweet ID</th>
										<th class="text-center none">Original Tweet</th>
										<th class="text-center none">Date discovered</th>
										<th class="text-center none">Discovery Time</th>
										<th class="text-center none">Action Reason</th>
										<th class="text-center none">Notes</th>
										<th class="text-center none"></th>
									</tr>
								</thead>
								<tbody>
								<?php
								while($tweets = mysqli_fetch_array($sql_tweets)){
										//Convert them to timestamps.
										$date1Timestamp = strtotime($tweets['date_tweeted']);
										$date2Timestamp = strtotime($tweets['date_discovered'])+10800;

										//Calculate the difference.
										$difference = $date2Timestamp - $date1Timestamp;

									?>
										<tr class='<?php print $tweets["entry_type"]; ?>'>
											<td><?php print $i; ?></td>
											<td style='text-align:left;'>
												<span style='display:none;'><?php print $tweets["date_tweeted"]; #For sorting the table display date like this ?></span>
												<?php print date('D, M jS g:i a @ ', strtotime($tweets["date_tweeted"])); ?>
												<span class='smltxt'><?php print date(' s', strtotime($tweets["date_tweeted"])); ?> sec</span>
											</td>
											<td>
												<strong><?php print ucwords($tweets["entry_type"]); ?></strong>
											</td>
											<td><strong><?php print strtoupper($tweets["symbol"]); ?></strong></td>
											<td>$<?php print number_format($tweets["strike_price"],2); ?></td>
											<td>$<?php print number_format($tweets['tweet_time_quote_price'],2);?></td>
											<td>$<?php print number_format($tweets["contract_price"],2); ?></td>
											<td>$<?php print number_format($tweets['tweet_time_contract_price'],2);?></td>
											<td><?php
													if($tweets["expiration_date"][0]){
														print date('M jS', strtotime($tweets["expiration_date"]));
													}else{
														print "NA";
													}
												?>
											</td>
											<td>
												<?php
												if(!$tweets['action_taken']){
													print 'Ignored';
												}else{
													print "<span class='bg-success p-5'>".$tweets['action_taken']."</span>";
												}
												?>
											</td>
											<td><?php ($tweets["contract_count"])?print $tweets["contract_count"]:print 'NA'; ?></td>
											<td>
												<span style='display:none;' id='<?php print $i; ?>-tweetid'><?php print $tweets["tid"];?></span>
												<?php print "<a href='https://twitter.com/option_snipper/status/".$tweets["tid"]."' target='_blank'>".$tweets["tid"]."</a>"; ?>
											</td>
											<td><?php print $tweets["tweet"]; ?></td>
											<td>
												<?php
												print date('D, M jS g:i a @ ', strtotime($tweets["date_discovered"]));
												?>
												<span class='smltxt'><?php print date(' s', strtotime($tweets["date_discovered"])); ?> sec</span>
											</td>
											<td><?php print $difference; ?> Seconds</td>
											<td><?php print $tweets["action_reason"]; ?></td>
											<td id='<?php print $i.'-notes1';?>'>
												<?php
													if(!$tweets["notes"]){print '{}';}else{print $tweets["notes"];}
												?>
											</td>
											<td><span id='<?php print $i.'-notes2';?>' class='invisible hidden'>
												<?php
													if(!$tweets["notes"]){print '{}';}else{print $tweets["notes"];}
												?>
											</span></td>
										</tr>
									<?php
									$i++;
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



    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>