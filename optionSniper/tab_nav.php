
<li <?php if($active=='settings'){print "class='active'"; $href="#left-icon-tab1";}else{$href="/optionSniper/settings.php";} ?>>
<a href="<?php print $href;?>">
<i class="icon-menu7 position-left"></i> Settings</a></li>

<li <?php if($active=='pva'){print "class='active'"; $href="#left-icon-tab1";}else{$href="/optionSniper/pva_accounts.php";} ?>>
<a href="<?php print $href;?>">
<i class="icon-menu7 position-left"></i> Twitter Accounts</a></li>

<li <?php if($active=='twitterlogtail'){print "class='active'"; $href="#left-icon-tab1";}else{$href="/optionSniper/twitter_log_tail.php";} ?>>
<a href="<?php print $href;?>">
<i class="icon-menu7 position-left"></i> Twitter Log</a></li>

<li <?php if($active=='tweets'){print "class='active'"; $href="#left-icon-tab1";}else{$href="/optionSniper/tweets.php";} ?>>
<a href="<?php print $href;?>">
<i class="icon-menu7 position-left"></i> Tweets</a></li>