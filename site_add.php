<?php
session_start();

# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); 

if(!$_SESSION['user']['authenticated'] && $_GET['p']!=GET_PASS){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=sites');
	exit();
}

# Include site functions
include_once(INCLUDES.'/functions.php');

# Include an array containing all data about each city
include_once(INCLUDE_PATH.'/city_info.php');

if(isset($_POST['site_name'])){
	// Make variables from post and clean vars for db
	foreach($_POST as $key=>$value){ ${$key} = mysqli_real_escape_string($connect,trim($value)); }

	if(strlen($site_name) < 4
		|| strlen($display_name) < 3
		|| !is_numeric($site_id)
		|| !in_array($is_police_site,array(0,1))
		|| empty($site_type)
		|| strlen($images_folder) < 3
		|| empty($ad_images_folder)
		|| !is_numeric($action_404)
		|| !is_numeric($posts_per_page)
		|| strlen($javascript_folder) < 1
		|| strlen($style) < 5
		|| strlen($public_cache_path) < 2 
		|| strlen($public_cache_url_folder) < 2
		|| !is_numeric($campaign_id_a)
		|| !is_numeric($campaign_id_b)
		|| !is_numeric($a_percent)
		|| !is_numeric($b_percent)
		|| strlen($a_display_name) < 3
		|| strlen($b_display_name) < 3
		|| !is_numeric($campaign_id_reviews)
		|| !is_numeric($campaign_id_reviews_phone)
		|| !in_array($site_enabled,array(0,1))
		){
		# One of the conditions was not met and therefor this record should not be saved
		print "error-1";
	}else{
		$updateSQL="INSERT INTO sites SET
							site_id='".$site_id."',
							site_name='".$site_name."',
							is_police_site='".$is_police_site."',
							site_type='".$site_type."',
							display_name='".$display_name."',
							action_404='".$action_404."',
							posts_per_page='".$posts_per_page."',
							javascript_folder='".$javascript_folder."',
							style='".$style."',
							public_cache_path='".$public_cache_path."',
							public_cache_url_folder='".$public_cache_url_folder."',
							images_folder='".$images_folder."',
							ad_images_folder='".$ad_images_folder."',
							url_prefix='".$url_prefix."',
							url_post='".$url_post."',
							url_forwardto='".$url_forwardto."',
							campaign_id_a='".$campaign_id_a."',
							campaign_id_b='".$campaign_id_b."',
							a_percent='".$a_percent."',
							b_percent='".$b_percent."',
							a_display_name='".$a_display_name."',
							b_display_name='".$b_display_name."',
							campaign_id_reviews='".$campaign_id_reviews."',
							campaign_id_reviews_phone='".$campaign_id_reviews_phone."',
							site_enabled='".$site_enabled."'";
		if(mysqli_query($connect, $updateSQL)){
			print "success";
		}else{
			print "error-2";	
		}
	}
	exit();
}



$PAGE_NAME = 'Edit Site';

# Include the header
$dontShowSide=1;
include_once(INCLUDE_PATH.'/header.php');
?>
		<script type="application/javascript">
        
            function alertContents() {
                if(http_request.readyState == 4) {
                    if(http_request.status == 200) {
						var success = http_request.responseText.match(/success/);
						if(success){
							colorSuccess('updateSiteContainer');
						}else{
							colorError('updateSiteContainer');
						}
                    }
                }
            }
        
            function correctPerecentb(){
                document.getElementById('b_percent').value = 100 - document.getElementById('a_percent').value
            }
            function correctPerecenta(){
                document.getElementById('a_percent').value = 100 - document.getElementById('b_percent').value
            }
            function updateSite(){
                var errors = 0;
                var site_name = document.getElementById('site_name');
                var display_name = document.getElementById('display_name');
                var site_id = document.getElementById('site_id');
                var site_type = document.getElementById('site_type');
                var is_police_site = document.getElementById('is_police_site');
                var images_folder = document.getElementById('images_folder');
                var ad_images_folder = document.getElementById('ad_images_folder');
                var url_prefix = document.getElementById('url_prefix');
                var url_post = document.getElementById('url_post');
                var url_forwardto = document.getElementById('url_forwardto');
                var campaign_id_a = document.getElementById('campaign_id_a');
                var campaign_id_b = document.getElementById('campaign_id_b');
                var a_percent = document.getElementById('a_percent');
                var b_percent = document.getElementById('b_percent');
                var a_display_name = document.getElementById('a_display_name');
                var b_display_name = document.getElementById('b_display_name');
                var campaign_id_reviews = document.getElementById('campaign_id_reviews');
                var campaign_id_reviews_phone = document.getElementById('campaign_id_reviews_phone');
                var site_enabled = document.getElementById('site_enabled');
				var action_404 = document.getElementById('action_404');
				var posts_per_page = document.getElementById('posts_per_page');
				var javascript_folder = document.getElementById('javascript_folder');
				var style = document.getElementById('style');
				var public_cache_path = document.getElementById('public_cache_path');
				var public_cache_url_folder = document.getElementById('public_cache_url_folder');

				var jf = javascript_folder.value
				if(jf.length < 1){
					colorError('javascript_folder');
					errors=1;
				}
		
				var sty = style.value
				if(sty.length < 5){
					colorError('style');
					errors=1;
				}
		
				var pcp = public_cache_path.value
				if(pcp.length < 2){
					colorError('public_cache_path');
					errors=1;
				}
		
				var pcuf = public_cache_url_folder.value
				if(pcuf.length < 2){
					colorError('public_cache_url_folder');
					errors=1;
				}
			
                var adn = a_display_name.value
                if(adn.length < 3){
                    colorError('a_display_name');
                    errors=1;
                }
        
                var bdn = b_display_name.value
                if(bdn.length < 3){
                    colorError('b_display_name');
                    errors=1;
                }
        
                var sn = site_name.value;
                if(sn.length < 6){
                    colorError('site_name');
                    errors=1;
                }
                
                var dn = display_name.value;
                if(dn.length < 3){
                    colorError('display_name');
                    errors=1;
                }
                
                var diuf = images_folder.value
                if(diuf.length < 3){
                    colorError('images_folder');
                    errors=1;
                }
        
                var aif = ad_images_folder.value
                if(aif.length < 3){
                    colorError('ad_images_folder');
                    errors=1;
                }

				if(!isNumber(action_404.value)){
					colorError('action_404');
					errors=1;
				}
		
				if(!isNumber(posts_per_page.value)){
					colorError('posts_per_page');
					errors=1;
				}

                if(!isNumber(campaign_id_a.value)){
                    colorError('campaign_id_a');
                    errors=1;
                }
                if(!isNumber(campaign_id_b.value)){
                    colorError('campaign_id_b');
                    errors=1;
                }
                if(!isNumber(a_percent.value)){
                    colorError('a_percent');
                    errors=1;
                }
                if(!isNumber(b_percent.value)){
                    colorError('b_percent');
                    errors=1;
                }
                
                if(!isNumber(site_id.value)){
                    colorError('site_id');
                    errors=1;
                }
                
                if(errors < 1){
                    var poststr = "site_id=" + encodeURI(site_id.value)
                                  + "&site_type=" + encodeURI(site_type.value)
                                  + "&is_police_site=" + encodeURI(is_police_site.value)
                                  + "&site_enabled=" + encodeURI(site_enabled.value)
                                  + "&ad_images_folder=" + encodeURI(ad_images_folder.value)
                                  + "&url_forwardto=" + encodeURI(url_forwardto.value)
                                  + "&images_folder=" + encodeURI(images_folder.value)
                                  + "&display_name=" + encodeURI(display_name.value)
								  + "&action_404=" + encodeURI(action_404.value)
								  + "&posts_per_page=" + encodeURI(posts_per_page.value)
								  + "&javascript_folder=" + encodeURI(javascript_folder.value)
								  + "&style=" + encodeURI(style.value)
								  + "&public_cache_path=" + encodeURI(public_cache_path.value)
								  + "&public_cache_url_folder=" + encodeURI(public_cache_url_folder.value)
                                  + "&site_name=" + encodeURI(site_name.value)
                                  + "&url_prefix=" + encodeURI(url_prefix.value)
                                  + "&url_post=" + encodeURI(url_post.value)
                                  + "&site_enabled=" + encodeURI(site_enabled.value)
                                  + "&campaign_id_b=" + encodeURI(campaign_id_b.value)
                                  + "&campaign_id_a=" + encodeURI(campaign_id_a.value) 
                                  + "&a_percent=" + encodeURI(a_percent.value) 
                                  + "&b_percent=" + encodeURI(b_percent.value) 
                                  + "&a_display_name=" + encodeURI(a_display_name.value) 
                                  + "&b_display_name=" + encodeURI(b_display_name.value) 
                                  + "&campaign_id_reviews=" + encodeURI(campaign_id_reviews.value) 
                                  + "&campaign_id_reviews_phone=" + encodeURI(campaign_id_reviews_phone.value);
                    makePOSTRequest('<?php print BASE_URL; ?>/site_add.php', poststr); 
                }
            }
        </script>
        <form method="post" action="">
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <div class="panel registration-form">
                        <div class="panel-body" id='updateSiteContainer'>
                            <div class="text-center">
                                <div class="icon-object border-primary-600 text-primary-300"><i class="icon-pencil6"></i></div>
                                <h5 class="content-group-lg">Add Site</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        Site Name: <input id="site_name" name="site_name" type="text" class="form-control" placeholder="google.com" value="<?php print $_POST['site_name']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        Display Name: <input id="display_name" name="display_name" type="text" class="form-control" placeholder="Google" value="<?php print $_POST['display_name']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group has-feedback">
                                        Site ID:
                                        <?php
                                        $site_id_list = mysqli_query($connect,"SELECT site_id FROM `voz`.`sites` LIMIT 300");
                                        $site_id_listA = array();
                                        while($idrow=mysqli_fetch_array($site_id_list)){
                                            $site_id_listA[$idrow['site_id']]=$idrow['site_id'];
                                        }
                                        ?>
                                        <select id="site_id" name='site_id' class="form-control">
                                        	<option value=""></option>
                                            <?php 
                                            $i=1;
                                            for(;;){
                                                if($i>200){break;}
                                                
                                                if($i==$_POST['site_id']){
                                                    print "<option value='$i' selected>$i</option>\n";
                                                }else{
                                                    if(!in_array($i,$site_id_listA)){
                                                        print "<option value='$i'>$i</option>\n";
                                                    }
                                                }
                                                
                                                $i++;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback">
                                        Enabled:
                                        <select id="site_enabled" name='site_enabled' class="form-control">
                                        	<option value=""></option>
                                            <?php 
                                            $site_typesA = array("0","1");
                                            foreach($site_typesA as $type=>$val){
                                                if($val==$_POST['site_enabled']){$selected='selected';}
                                                if($val==1){$displayVal='Yes';}else{$displayVal='No';}
                                                print "<option value='$val' $selected>".$displayVal."</option>\n";
                                                $selected='';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback">
                                        DB/Type:
                                        <select id="site_type" name='site_type' class="form-control">
                                        	<option value=""></option>
                                            <?php 
                                            $site_typesA = array("adm","em","mt","bs","ts");
                                            foreach($site_typesA as $type=>$val){
                                                if($val==$_POST['site_type']){$selected='selected';}
                                                print "<option value='$val' $selected>$val</option>\n";
                                                $selected='';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback">
                                        Police / Bust Site:
                                        <select id="is_police_site" name='is_police_site' class="form-control">
                                        	<option value=""></option>
                                            <?php 
                                            $site_typesA = array("0","1");
                                            foreach($site_typesA as $type=>$val){
                                                if($val==$_POST['is_police_site']){$selected='selected';}
                                                if($val==1){$displayVal='Yes';}else{$displayVal='No';}
                                                print "<option value='$val' $selected>".$displayVal."</option>\n";
                                                $selected='';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback">
                                        404 Action:
                                        <select id="action_404" name='action_404' class="form-control">
                                        	<option value=""></option>
                                            <?php 
                                            $action_404A = array(1,2,3,4,5,6,7);
                                            $action_404_displayA = array("",
																		"No redirect or ads",
																		"Show affiliate ads",
																		"Show recent escorts",
																		"Meta Redirect show affiliate ads",
																		"Meta Redirect show recent escorts",
																		"Meta Redirect only",
																		"302 Redirect"
																		);
                                            foreach($action_404A as $type=>$val){
                                                if($val==$_POST['action_404']){$selected='selected';}
                                                print "<option value='".$val."' ".$selected.">".$action_404_displayA[$val]."</option>\n";
                                                $selected='';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group has-feedback">
                                        Posts Per Page
                                        <input name="posts_per_page" id="posts_per_page" onKeyUp="AcceptDigits(this);" type="text" class="form-control" value='<?php print $sitesrow['posts_per_page']; ?>'>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group has-feedback">
                                        JS Folder:<input id="javascript_folder" name="javascript_folder" type="text" class="form-control" value='<?php print $_POST['javascript_folder']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group has-feedback">
                                        Style Location:<input id="style" name="style" type="text" class="form-control" value='<?php print $_POST['style']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group has-feedback">
                                        Public Cache Path:<input id="public_cache_path" name="public_cache_path" type="text" class="form-control" value='<?php print $_POST['public_cache_path']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group has-feedback">
                                        Public Cache URL:<input id="public_cache_url_folder" name="public_cache_url_folder" type="text" class="form-control" value='<?php print $_POST['public_cache_url_folder']; ?>'>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        Images Folder:<input id="images_folder" name="images_folder" type="text" class="form-control" value='<?php print $_POST['images_folder']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        Ad Images Folder:<input id="ad_images_folder" name="ad_images_folder" type="text" class="form-control" value='<?php print $_POST['ad_images_folder']; ?>'>
                                    </div>
                                </div>
                            </div>
        
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        City URL Prefix:<input id="url_prefix" name="url_prefix" type="text" class="form-control" value='<?php print $_POST['url_prefix']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group has-feedback">
                                        City URL Post:<input id="url_post" name="url_post" type="text" class="form-control" value='<?php print $_POST['url_post']; ?>'>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group has-feedback">
                                        Redirect Traffic to:<input id="url_forwardto" name="url_forwardto" type="text" class="form-control" value='<?php print $_POST['url_forwardto']; ?>'>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <?php
                            $campaignA = array();
                            $clistResult = mysqli_query($connect,"SELECT * FROM `campaigns` ORDER BY `network`,`campaign_name`");
                            while($clist = mysqli_fetch_array($clistResult)){
                                $campaignA[$clist['network']][$clist['campaign_name']]['campaign_id']=$clist['campaign_id'];
                                $campaignA[$clist['network']][$clist['campaign_name']]['campaign_name']=$clist['campaign_name'];
                                $campaignA[$clist['network']][$clist['campaign_name']]['network']=$clist['network'];
                                $campaignA[$clist['network']][$clist['campaign_name']]['campaign_url']=$clist['campaign_url'];
                            }
                            ?>
                            <style type="text/css">
                                .caContain{
                                    background-color:#F1F7FF;
                                }
                                .cbContain{
                                    background-color:#EDF3FF;
                                }
                                .errorField{
                                    background-color:#FFF0F0;	
                                    border:1px solid #FF8A8A;
                                }
                            </style>
                            <div class="row">
                                <div class="col-md-4 caContain">
                                    <div class="form-group has-feedback">
                                        Campaign A: &nbsp;
                                        <select class="form-control" id="campaign_id_a" name="campaign_id_a">
                                        	<option value=""></option>
                                            <?php 
                                            foreach($campaignA as $val=>$key){
                                                print"<optgroup label='".$val."'>\n";
                                                foreach($key as $innerKey=>$innerVal){
                                                    if($campaignA[$val][$innerKey]['campaign_id'] == $_POST['campaign_id_a']){
                                                        $selected="selected";
                                                    }
                                                     print "<option value='".$campaignA[$val][$innerKey]['campaign_id']."' $selected>".$innerKey."</option>\n";
                                                     $selected='';
                                                }
                                                print "</optgroup>\n\n";
                                            }
                                            ?>    
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-2 caContain">
                                    <div class="form-group has-feedback">
                                        Display Name:
                                        <input id="a_display_name" name="a_display_name" type="text" class="form-control" value='<?php print $_POST['a_display_name']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-2 caContain">
                                    <div class="form-group has-feedback">
                                        % Traffic
                                        <input name="a_percent" id="a_percent" onBlur="correctPerecentb()" onKeyUp="AcceptDigits(this);" type="text" class="form-control" value='<?php print $_POST['a_percent']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group has-feedback">
                                        Reviews No Phone: &nbsp;
                                        <select class="form-control" id="campaign_id_reviews" name="campaign_id_reviews">
                                        	<option value=""></option>
                                            <?php 
                                            foreach($campaignA as $val=>$key){
                                                print"<optgroup label='".$val."'>\n";
                                                foreach($key as $innerKey=>$innerVal){
                                                    if($campaignA[$val][$innerKey]['campaign_id'] == $_POST['campaign_id_reviews']){
                                                        $selected="selected";
                                                    }
                                                     print "<option value='".$campaignA[$val][$innerKey]['campaign_id']."' $selected>".$innerKey."</option>\n";
                                                     $selected='';
                                                }
                                                print "</optgroup>\n\n";
                                            }
                                            ?>    
                                            </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 cbContain">
                                    <div class="form-group has-feedback">
                                        Campaign B: &nbsp;
                                        <select class="form-control" id="campaign_id_b" name="campaign_id_b">
                                        	<option value=""></option>
                                            <?php 
                                            foreach($campaignA as $val=>$key){
                                                print"<optgroup label='".$val."'>\n";
                                                foreach($key as $innerKey=>$innerVal){
                                                    if($campaignA[$val][$innerKey]['campaign_id'] == $_POST['campaign_id_b']){
                                                        $selected="selected";
                                                    }
                                                     print "<option value='".$campaignA[$val][$innerKey]['campaign_id']."' $selected>".$innerKey."</option>\n";
                                                     $selected='';
                                                }
                                                print "</optgroup>\n\n";
                                            }
                                            ?>    
                                            </select>
                                    </div>
                                </div>
                                <div class="col-md-2 cbContain">
                                    <div class="form-group has-feedback">
                                        Display Name:
                                        <input id="b_display_name" name="b_display_name" type="text" class="form-control" value='<?php print $_POST['b_display_name']; ?>'>
                                    </div>
                                </div>
                                <div class="col-md-2 cbContain">
                                    <div class="form-group has-feedback">
                                        % Traffic
                                        <input name="b_percent" id="b_percent" onBlur="correctPerecenta()" onKeyUp="AcceptDigits(this);" type="text" class="form-control" value='<?php print $_POST['b_percent']; ?>'>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group has-feedback">
                                        Reviews With Phone: &nbsp;
                                        <select class="form-control" id="campaign_id_reviews_phone" name="campaign_id_reviews_phone">
                                        	<option value=""></option>
                                            <?php 
                                            foreach($campaignA as $val=>$key){
                                                print"<optgroup label='".$val."'>\n";
                                                foreach($key as $innerKey=>$innerVal){
                                                    if($campaignA[$val][$innerKey]['campaign_id'] == $_POST['campaign_id_reviews_phone']){
                                                        $selected="selected";
                                                    }
                                                     print "<option value='".$campaignA[$val][$innerKey]['campaign_id']."' $selected>".$innerKey."</option>\n";
                                                     $selected='';
                                                }
                                                print "</optgroup>\n\n";
                                            }
                                            ?>    
                                        </select>
                                    </div>
                                </div> 
                            </div>
        
                            <div class="text-right">
                                <a href="/sites.php"<i class="icon-arrow-left13 position-left"></i> Back to site list</a>
                                <span class="btn bg-primary-400 btn-labeled btn-labeled-right ml-10" onclick="updateSite();"><b><i class="icon-pencil6"></i></b> Add Site</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- /dashboard content -->

<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>