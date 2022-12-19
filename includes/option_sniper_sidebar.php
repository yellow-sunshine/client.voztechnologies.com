
    <div id='sidebar1' class="sidebar sidebar-main sidebar-default">
        <div class="sidebar-content">
            <div class="">
                    <div class="panel panel-flat">
                        <div class="panel-heading">
                            <h6 class="panel-title"><i class="icon-watch position-left"></i> Today</h6>
                        </div>
                        <div class="panel-body">
                            <ul class="timer-weekdays mb-10">
                            	<?php
									date_default_timezone_set('America/Halifax'); // Set timezone to when TDN stats change over
									$dayA = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
									foreach($dayA as $day=>$abv){
										if(date('D') == $abv){
											print "<li> <span style='padding:4px; font-size:9px; margin-left:2px;' class='label label-danger'> $abv </span> </li>";
										}else{
											print "<li> <span style='padding:4px; font-size:9px; margin-left:2px;' class='label label-default'> $abv </span> </li>";
										}
									}
								?>
                            </ul>
                            <?php
							$date = new DateTime();
							$date->setTimezone(new DateTimeZone('America/New_York'));
							print $date->format('l, M jS Y');
							print "<br />".$date->format('g:i a')." NYC";
							?>
                        </div>
                    </div>

			<?php $datetime = new DateTime;  ?>
            <script>
                Array.prototype.contains = function(value) {
                    for(var i=0; i< this.length; i++){
                        if(this[i] == value)
                            return true;
                    }
                    return false;
                }

                var openPositionsList =[];
                var colors = ['indigo-400', 'danger-400', 'success-400', 'warning-400', 'violet-400', 'blue-400', 'purple-400', 'pink-400', 'teal-400', 'brown-400', 'grey-400', 'orange-400', 'slate-400',
                                                'indigo-400', 'danger-400', 'success-400', 'warning-400', 'violet-400', 'blue-400', 'purple-400', 'pink-400', 'teal-600', 'brown-600', 'grey-600', 'orange-600', 'slate-600',
                                                'indigo-800', 'danger-800', 'success-800', 'warning-800', 'violet-800', 'blue-800', 'purple-800', 'pink-800', 'teal-800', 'brown-800', 'grey-800', 'orange-800', 'slate-800'];

                function blinkMe(el, fval=0, sval=0){
                    if(fval != sval){
                        $('#'+el).blink({
                            maxBlinks: 1
                        });
                        document.getElementById(el).innerHTML = sval;
                    }
                }

                const numberWithCommas = (x) => {
                    return x.toLocaleString();
                }
                var get_account_count = 0;
                var last_quote_list = []; // Place holder to hold for currently held positions
                function get_current_account_data(){
                    get_account_count++;
                    setTimeout(get_current_account_data, 5000);
                    $.get("/optionSniper/current_account_data.php", function(data, status){
                        if(status=='success'){
                            if(!data){
                                return false;
                            }
                            var current_data = JSON.parse(data);
                            //alert('here is is: '+current_data.securitiesAccount.roundTrips);
                            var total_invested = parseInt(current_data.securitiesAccount.currentBalances.longOptionMarketValue) + parseInt(current_data.securitiesAccount.currentBalances.shortOptionMarketValue);
                            blinkMe("side_invested", document.getElementById("side_invested").innerHTML, "$"+numberWithCommas(total_invested));
                            blinkMe("side_round_trips", document.getElementById("side_round_trips").innerHTML, numberWithCommas(parseInt(current_data.securitiesAccount.roundTrips)));
                            blinkMe("side_available", document.getElementById("side_available").innerHTML, '$' + numberWithCommas(parseInt(current_data.securitiesAccount.currentBalances.buyingPowerNonMarginableTrade)));


                            positions = current_data.securitiesAccount.positions;
                            var profitLoss = 0;
                            var positionCount = 0;
                            var played_gorilla = 0;
                            openPositionsList = [];
                            if(positions !=null && positions.constructor === Array){
                                positions.forEach(function(el, index) {
                                    //print "$var - $val";
                                    profitLoss = profitLoss + parseInt(el.currentDayProfitLoss);
                                    if(el.instrument.assetType == 'OPTION'){
                                        positionCount++;
                                        if(get_account_count > 4 && !last_quote_list.contains(el.instrument.symbol)){
                                            new PNotify({
                                                    title: 'Success',
                                                    text: 'Position Entered for ' + el.instrument.symbol,
                                                    icon: 'icon-checkmark3',
                                                    type: 'success'
                                                });
                                            if(!played_gorilla){
                                                var audio = new Audio('/optionSniper/gorilla.mp3');
                                                audio.play();
                                                played_gorilla = 1;
                                            }
                                        }

                                        last_quote_list.push(el.instrument.symbol);
                                        openPositionsList.push(el.instrument.symbol);
                                    }
                                });
                            }
                            last_quote_list = openPositionsList;
                            blinkMe("profitLoss", document.getElementById("profitLoss").innerHTML, "$"+numberWithCommas(profitLoss));
                            blinkMe("side_open_positions", document.getElementById("side_open_positions").innerHTML, positionCount);
                            if(profitLoss < 0){
                                $( "#profitLossBtn" ).addClass( "bg-danger-800");
                            }else if(profitLoss == 0){
                                $( "#profitLossBtn" ).addClass( "bg-primary-400");
                            }else{
                                $( "#profitLossBtn" ).addClass( "bg-success-600");
                            }
                        }
                    });
                }
                get_current_account_data();

                var quotes = [<?php foreach(array_reverse(TRADE_SYMBOLS) as $key=>$val){$quoteList .= "'$val',";} print substr($quoteList,0,-1); ?>];
                function update_quote_data(){
                    setTimeout(update_quote_data, 1000);
                    if(openPositionsList.constructor === Array){
                        openPositionsList.forEach(function(el, index) {
                            if(!quotes.contains(el)){
                                quotes.unshift(el);
                            }
                        });
                    }
                    //alert("getting "+quotes);
                    $.get("/optionSniper/current_quote_data.php?quotes="+quotes, function(data, status){
                        if(status=='success'){
                            var current_quotes = JSON.parse(data);
                            var i=0;
                            //Object.keys(current_quotes.forEach(function (el){
                            for (var el in current_quotes) {
                            //current_quotes.forEach(function(el, index) {

                                // el is the symbol
                                var color = colors[i];
                                i++;

                                if(el && current_quotes[el.toLowerCase()]['askPrice'] != null && document.getElementById(el+"-ask") != null){
                                    //alert(el);
                                    blinkMe(el+"-ask", document.getElementById(el+"-ask").innerHTML, current_quotes[el.toLowerCase()]['askPrice']);
                                    blinkMe(el+"-bid", document.getElementById(el+"-bid").innerHTML, current_quotes[el.toLowerCase()]['bidPrice']);
                                }else{
                                    $('#quote_tbl').prepend("\
                                    <tr>\
                                        <td>\
                                            <div class='media-left media-middle'>\
                                                <a href='#' class='btn bg-"+color+" btn-rounded btn-icon btn-s'>\
                                                    <span class='label' id='"+el+"'>"+el+"</span>\
                                                </a>\
                                            </div>\
                                            <div class='media-body'>\
                                                <div class='media-heading'>\
                                                    Bid <span id='"+el+"-bid'>"+current_quotes[el.toLowerCase()]['bidPrice']+"</span>\
                                                </div>\
                                                <div class='text-muted text-size-small'>\
                                                    Ask <span id='"+el+"-ask'>"+current_quotes[el.toLowerCase()]['askPrice']+"</span>\
                                                </div>\
                                            </div>\
                                        </td>\
                                        <td>\
                                            <span class='text-muted text-size-small'></span>\</span>\
                                        </td>\
                                    </tr>\
                                    ");
                                }
                            };

                        }
                    });


                }
                update_quote_data();


            </script>
            <div class="sidebar-category">
                <div class="category-content">
                <div class="row row-condensed">
                        <div class="col-xs-6">
                            <button type="button" class="btn bg-success-600 btn-block btn-float"><h1><i class="fa fa-bank"></i></h1>
                                <span id='side_available'>
                                    $<?php print number_format($account['securitiesAccount']['currentBalances']['buyingPowerNonMarginableTrade'],2); ?>
                                </span><br />
                                AVAILABLE
                            </button>
                        </div>
                        <div class="col-xs-6">
                            <button type="button" class="btn bg-blue-800 btn-block btn-float"><h1><i class="fa fa-bar-chart"></i></h1>
                                <span id='side_invested'> $??.?? </span><br />
                                INVESTED
                            </button>
                        </div>
                    </div>
                    <br />
                    <div class="row row-condensed">
                    <div class="col-xs-6">
                            <button id='profitLossBtn' type="button" class="btn btn-block btn-float"><h1><i class="fa fa-balance-scale"></i></h1>
                                <span id='profitLoss'>$0.00</span><br />
                                +/- Open Positions
                            </button>
                        </div>
                        <div class="col-xs-6">
                            <button type="button" class="btn bg-grey-400 btn-block btn-float"><h1><i class="fa fa-flag-checkered"></i></h1>
                                <span id='side_open_positions'>0</span><br />
                                Open<br />Positions
                            </button>
                        </div>
                    </div>
                </div>
                <?php
                    // Only get quote data every 2 minutes and store it in memcache
                    $key = 'quote_data_sidebar';
                    $quote_data = $memcache->get($key);
                    // $quote_data = FALSE;
                    if(!$quote_data){
                        $quote_data = $tda->quote_info_multi(TRADE_SYMBOLS_CS,1);
                        $memcache->set($key, $quote_data, 0, 4);
                    }
                ?>

                <table class="table text-nowrap">
                    <tbody>
                        <tr>
                            <td>Total Round Trips</td>
                            <td id='side_round_trips'>??</td>
                        </tr>
                    </tbody>
                </table>

                <table class="table text-nowrap">
                    <tbody id='quote_tbl'>

                    </tbody>
                </table>


				<?php
					$key = 'disk-space';
					$dup = $memcache->get($key);
					if(!$dup){
						$dup= number_format(((disk_total_space("/") - disk_free_space("/")) / disk_total_space("/")) * 100,2);
						$memcache->set($key, $dup, 0, 3600);
					}

					$key = 'm5-load';
					$load = $memcache->get($key);
					if(!$load){
						$load = sys_getloadavg();
						$memcache->set($key, $load, 0, 120);
					}

					$key = 'Apache-uptime';
                    $AutResult = $memcache->get($key);
					if(!$AutResult){
						$AutResult = exec("apachectl status | grep uptime | sed 's/Server uptime://g'");
                        $AutResult = preg_replace('/\sdays(\s)?/i','D-',$AutResult);
                        $AutResult = preg_replace('/\shours(\s)?/i','hr-',$AutResult);
                        $AutResult = preg_replace('/\sminutes.*/i','m',$AutResult);
						$memcache->set($key, $AutResult, 0, 1800);
					}

					$key = 'Apache-threads';
					$currrent_threads = $memcache->get($key);
					if(!$currrent_threads){
						$ctr = mysqli_query($connect,"SHOW STATUS WHERE `variable_name` = 'Threads_connected'");
						$currrent_threads = $ctr->fetch_assoc();
						$currrent_threads = $currrent_threads['Value'];

						//if($memcache->get('currentthreads') < 1){
						if($currrent_threads < 1){
							$currrent_threads = 'No';
						}
						$memcache->set($key, $currrent_threads, 0, 10);
					}


				?>
                <div class="sidebar-category">
                    <div class="category-content no-padding">
                        <ul class="navigation navigation-alt navigation-accordion">
                            <li><a href="#"><i class="icon-floppy-disk"></i> <strong>M5 Disk: </strong><?php print $dup; ?>% Full</a></li>
                            <li><a href="#"><i class="icon-tux"></i> <strong>M5 Load:</strong> <?php print $load[0].", ".$load[1].", ".$load[2]; ?></a></li>
                            <li><a href="#"><i class="icon-stack3"></i> <strong>Thread Count:</strong> <?php print $currrent_threads; ?> Threads</a></li>
                            <li><a href="#"><i class="fa fa-hourglass-3"></i> <strong>Apache Uptime:</strong> <?php print $AutResult; ?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="category-content no-padding">
                    <ul class="navigation navigation-main">
                    <li>
                        <a href="#"><i class="icon-stack2"></i> <span>Internal</span></a>
                        <ul>
                            <li><a href="//<?php print ADMIN_DOMAIN; ?>/server-status" target="_blank"><i class="fa fa-tasks"></i> Apache Top</a></li>
                            <li><a href="//<?php print ADMIN_DOMAIN; ?>/phpMemcachedAdmin1.3.0/" target="_blank"><i class="fa fa-tasks"></i> phpMCA 1.3.0</a></li>
                            <li><a href="//<?php print ADMIN_DOMAIN; ?>/phpMemcachedAdmin/" target="_blank"><i class="fa fa-tasks"></i> phpMCA 1.2.2</a></li>
                            <li><a href="/phpinfofile.php" target="_blank"><i class="fa fa-tasks"></i> phpInfo</a></li>
                            <li><a href="//vpsd.blr.pw/restart/services.php" target="_blank"><i class="fa fa-tasks"></i> Restart Services</a></li>
                            <li><a href="//quotes.voztechnologies.com/optionSniper/auth.php?p=sdcyh87347y9hbecIJHBuhygbd" target="_blank"><i class="fa fa-tasks"></i> Quotes Setttings</a></li>
                            <li class="navigation-divider"></li>
                            <li>
                                <a href="#"><i class="fa fa-database"></i> phpMyAdmin</a>
                                <ul>
                                    <li><a href="/phpmyadmin4voz" target="_blank">M5</a></li>

                                    <li><a href="//vpsd.blr.pw/phpmyadmin/" target="_blank">VPSD</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="icon-stack2"></i> <span>External</span></a>
                        <ul>
                            <li><a href="https://service.m5hosting.com/" target="_blank"><i class="fa fa-server"></i> M5 Hosting</a></li>
                            <li><a href="https://signin.aws.amazon.com/" target="_blank"><i class="fa fa-server"></i> AWS</a></li>
                            <li><a href="https://www.cloudflare.com/login" target="_blank"><i class="fa fa-cloud"></i> CloudFlare</a></li>
                            <li><a href="https://www.namecheap.com/myaccount/login-only.aspx" target="_blank"><i class="fa fa-check-square"></i> NameCheap</a></li>
                            <li><a href="https://www.twilio.com/login" target="_blank"><i class="fa fa-commenting"></i> Twilio</a></li>
                            <li>
                                <a href="#"><i class="fa fa-sitemap"></i> Proxies</a>
                                <ul>
                                    <li><a href="http://www.sharedproxies.com/user.php" target="_blank"> Shared Proxies</a></li>
                                    <li><a href="https://panel.limeproxies.com/login" target="_blank"> Lime Proxies (Not Used)</a></li>
                                    <li><a href="https://actproxy.com/clientarea.php" target="_blank"> ACT Proxy (Not Used)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <style>.smallFont{font-size: 0.6em;}</style>
                    <li>
                        <a href="#"><i class="icon-alarm-check"></i> Cron/Scripts</a>
                        <ul>
                            <li>
                                <a href="#"><i class="icon-twitter"></i></i> Get tweets using:</a>
                                <ul>
                                    <?php
									$key = 'twitter_accounts';
									$twitter_accounts = $memcache->get($key);
									$twitter_accounts = FALSE;
									if(!$twitter_accounts){
										$twitter_accountsDS = mysqli_query($connect,"SELECT * FROM `twitter_sniper`.`twitter_accounts` LIMIT 100");
										while($row = mysqli_fetch_array($twitter_accountsDS)){
											$twitter_accounts[$row['account_id']] = array('account_id'=>$row['account_id'], 'email'=>$row['email']);
										}
										$memcache->set($key, $twitter_accounts, 0, 1800);
									}

									foreach($twitter_accounts as $account=>$val){
                                    ?>
                                        <li class='smallFont text-primary'>
                                            <a href="//client.voztechnologies.com/optionSniper/get_tweets.php?ta=<?php print $val['account_id'];?>" target="_blank">
                                            <?php print $val['account_id']."-".$val['email'];?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    </ul>
                </div>
            </div>
		</div>
	</div>
</div>