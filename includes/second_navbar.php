<!-- Second navbar -->
	<div class="navbar navbar-xs navbar-default" id="navbar-second">
		<ul class="nav navbar-nav no-border visible-xs-block">
			<li><a class="text-center collapsed" data-toggle="collapse" data-target="#navbar-second-toggle"><i class="icon-menu7"></i></a></li>
		</ul>
        <?php

		?>
		<style>
			.navphoneSearch input{
				margin-top:8px;
				border:1px solid #ccc;
				width:100px;
			}

			.navphoneSearch input:focus{
				background-color:#fcfcfc;
				border:1px solid
				box-shadow: 0 0 5px rgba(81, 203, 238, 1);
				border: 1px solid rgba(81, 203, 238, 1);
			}
		</style>
			<?php
            $review_sql = "SELECT r.*, s.site_name FROM `voz`.`reviews` r INNER JOIN sites s on s.site_id = r.site_id WHERE r.review_status=0 ORDER BY r.review_date ASC LIMIT 30";
            $reviewA=array();
            $review_result = mysqli_query($connect,$review_sql);
            $reviewcount=mysqli_num_rows($review_result);
            while($rr = mysqli_fetch_array($review_result)){
                $reviewA[$rr['review_id']] = array(
                    'review_name'=>$rr['review_name'],
                    'site_name'=>$rr['site_name'],
                    'review_id'=>$rr['review_id'],
                    'review_body'=>$rr['review_body'],
                    'review_date'=>$rr['review_date'],
                    'review_phone'=>$rr['review_phone'],
                );
            }
			$result = mysqli_query($connect, "SELECT count(*) as comment_count FROM `simplephpblog`.`blog20_comments` WHERE `status`='Not approved'");
			if ($result !== false) {
				$row=mysqli_fetch_array($result,MYSQLI_ASSOC);
				$comments = $row['comment_count'];
			}

            ?>
		<div class="navbar-collapse collapse" id="navbar-second-toggle">
			<ul class="nav navbar-nav">
                <?php if(in_array_r('blog',$_SESSION['user']['pages_allowed'])){?>
					<li>
						<a href="/blog/admin.php?act=comments"> Blog
							<span class="badge bg-warning-400"><?php print $comments; ?></span>
						</a>
					</li>
                <?php } ?>
                <?php if(in_array_r('campaigns',$_SESSION['user']['pages_allowed'])){?><li><a href="/campaigns.php"> Campaigns</a></li><?php } ?>
                <?php if(in_array_r('proxies',$_SESSION['user']['pages_allowed']) && $_SESSION['user']['graffiti']==1 ){?><li><a href="/proxies.php"> Proxies</a></li><?php } ?>
                <?php if(in_array_r('records',$_SESSION['user']['pages_allowed'])){?><li><a href="/records.php"> Records</a></li><?php } ?>
                <?php if(in_array_r('removals',$_SESSION['user']['pages_allowed'])){?><li><a href="/removals.php"> Removals</a></li><?php } ?>
                <?php if(in_array_r('reviews',$_SESSION['user']['pages_allowed'])){?>
					<li>
						<a href="/reviews.php"> Reviews
						<span class="badge bg-warning-400" id="reviewsWaitingCount"><?php print $reviewcount; ?></span>
						</a>
					</li>
                <?php } ?>
                <?php if(in_array_r('settings',$_SESSION['user']['pages_allowed'])){?><li><a href="/settings.php"> Settings</a></li><?php } ?>
                <?php if(in_array_r('sites',$_SESSION['user']['pages_allowed'])){?><li><a href="/sites.php"> Sites</a></li><?php } ?>
                <?php if(in_array_r('stats',$_SESSION['user']['pages_allowed'])){?><li><a href="/stats.php"> Stats</a></li><?php } ?>
                <?php if(in_array_r('tweets',$_SESSION['user']['pages_allowed'])){?><li><a href="/optionSniper/tweets.php"> Option Sniper</a></li><?php } ?>
				<?php if(in_array_r('crypto tracker',$_SESSION['user']['pages_allowed'])){?><li><a href="/crypto_tracker.php"> Crypto Tracker</a></li><?php } ?>
						</ul>

			<ul class="nav navbar-nav navbar-xs navbar-right">
                <?php if(in_array_r('records',$_SESSION['user']['pages_allowed'])){
						switch($_SESSION['user']['selected_db']){
							case 'bs':  $bs_selected='selected';break;
							case 'em':  $em_selected='selected';break;
							case 'mt':  $mt_selected='selected';break;
							case 'ts':  $ts_selected='selected';break;
							case 'voz': $em_selected='selected';break;
							default: $em_selected='selected';break;
						}
				?>
                		<li class='navphoneSearch'>
                			<form action="/search.php" method="post" enctype="multipart/form-data">
								<select name='forced_db' name='forced_db'>
									<option value='bs' <?php print $bs_selected; ?>>BS</option>
									<option value='em' <?php print $em_selected; ?>>EM</option>
									<option value='mt' <?php print $mt_selected; ?>>MT</option>
									<option value='ts' <?php print $ts_selected; ?>>TS</option>
								</select>&nbsp;&nbsp;
									<input type='text' name='bpid' onkeyup="AcceptDigits(this);" maxlength="45" placeholder=' bpid'>
								<span class='text-muted'>&nbsp;or&nbsp;</span>
									<input type='text' name='phone' onkeyup="AcceptDigits(this);" maxlength="12" placeholder=' phone'>
								<button type="submit" class="btn btn-xs btn-primary">Search</button>
							</form>
                		</li>
                <?php } ?>
				<!--li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="icon-bubbles4"></i>
						<span class="badge bg-warning-400" id="reviewsWaitingCount"><?php print $reviewcount; ?></span>
					</a>
					<?php if(!empty($reviewcount)){?>
					<div class="dropdown-menu dropdown-content width-350">
						<div class="dropdown-content-heading">
							Reviews
						</div>

						<ul class="media-list dropdown-content-body">
							<?php
							$rcount=0;
							foreach($reviewA as $review_id => $id_array){
								?>
								<li class="media" id='review-<?php print $review_key; ?>'>
									<div class="media-left"><a href='/reviews.php?review_id=<?php print $review_id; ?>'><span class='fa fa-comment-o' style='font-size:30px;'></span></a></div>
									<div class="media-body">
										<a href='/reviews.php?review_id=<?php print $review_id; ?>' class="media-heading">
											<span class="text-semibold"><?php print $reviewA[$review_id]['review_name']; ?></span>
											<span class="media-annotation pull-right"><?php print date('D g:ia',$reviewA[$review_id]['review_date']); ?></span>
										</a>
										<span class="text-muted"><?php print dots(70,$reviewA[$review_id]['review_body']); ?></span>
									</div>
								</li>
								<?php
								$rcount++; if($rcount > 5){break;} // Stop us from showing of hundreds of waiting reviews.
							}
							?>
						</ul>

						<div class="dropdown-content-footer">
							<a href="/reviews.php" data-popup="tooltip" title="All messages"><i class="icon-menu display-block"></i></a>
						</div>
					</div>
                    <?php } ?>
				</li-->
			</ul>
		</div>
	</div>
	<!-- /second navbar -->