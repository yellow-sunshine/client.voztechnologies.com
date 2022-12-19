<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/optionSniper/get_settings.php');

if(!$_SESSION['user']['authenticated']){
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/?r=tweets');
	exit();
}
include_once(__DIR__.'/functions.v2.php');
include_once(__DIR__.'../includes/voz_settings.php');
$PAGE_NAME = 'Twitter Log Tail';
include_once(__DIR__.'/../includes/header.php');
?>

<script type="text/javascript">
	$(function() {
    // Switchery toggles
    if (Array.prototype.forEach) {
        var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery'));
        elems.forEach(function(html) {
            var switchery = new Switchery(html);
        });
    }
    else {
        var elems = document.querySelectorAll('.switchery');

        for (var i = 0; i < elems.length; i++) {
            var switchery = new Switchery(elems[i]);
        }
    }
	});


	var refreshtime=900;
	function pause_tail(){
		if(refreshtime==900){
			refreshtime = 999999999;
		}else{
			refreshtime=900;
			setTimeout(tc,refreshtime);
		}
	}
</script>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-white" id="waitingReviews">
            <div class="panel-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
						<?php $active='twitterlogtail'; require_once($_SERVER['DOCUMENT_ROOT'].'optionSniper/tab_nav.php'); ?>

                    </ul>
                    <div class="tab-content">
                    	<div class="tab-pane active" id="left-icon-tab1">


							<div class="panel">
								<div class="panel-heading">
									<h2 class="panel-title">
									Twitter Log Tail
									<span style='font-size:.7em;'>(Live view of tweet collection)</span>
									</h2>

									<div class="heading-elements">
										<span onclick="cc('ffffff');" style='cursor:pointer;background-color:#fff;width:25px;height:25px;display:inline-block;border:1px solid #ccc;'> </span>
										<span onclick="cc('32adff');" style='cursor:pointer;background-color:#32adff;width:25px;height:25px;display:inline-block;'> </span>
										<span onclick="cc('19E200');" style='cursor:pointer;background-color:#19E200;width:25px;height:25px;display:inline-block;'> </span>
										<span onclick="cc('dc60ff');" style='cursor:pointer;background-color:#dc60ff;width:25px;height:25px;display:inline-block;'> </span>
										<span onclick="cc('ffe900');" style='cursor:pointer;background-color:#ffe900;width:25px;height:25px;display:inline-block;border:1px solid #ccc;'> </span>
										<span onclick="cc('ff1414');" style='cursor:pointer;background-color:#ff1414;width:25px;height:25px;display:inline-block;'> </span>
										<span onclick="cc('ffa814');" style='cursor:pointer;background-color:#ffa814;width:25px;height:25px;display:inline-block;'> </span>
									</div>
								</div>

								<div id="feed" class="panel-body" style='font-size:0.8em;padding:2px 2px 2px 4px; background-color:#000; color:#19E200;'></div>
							</div>

							<form class="heading-form" action="#">
								<div class="form-group">
									<label class="checkbox-inline checkbox-switchery checkbox-right switchery">
										<input type="checkbox" onclick="pause_tail();" class="switchery" checked="checked">
										&nbsp;&nbsp;Enabled
									</label>
								</div>
							</form>
                        </div>
					</div>
                </div>
            </div>
			<div style="margin-left:25px;">
				<span onclick="cc('ffffff');" style='cursor:pointer;background-color:#fff;width:25px;height:25px;display:inline-block;border:1px solid #ccc;'> </span>
				<span onclick="cc('32adff');" style='cursor:pointer;background-color:#32adff;width:25px;height:25px;display:inline-block;'> </span>
				<span onclick="cc('19E200');" style='cursor:pointer;background-color:#19E200;width:25px;height:25px;display:inline-block;'> </span>
				<span onclick="cc('dc60ff');" style='cursor:pointer;background-color:#dc60ff;width:25px;height:25px;display:inline-block;'> </span>
				<span onclick="cc('ffe900');" style='cursor:pointer;background-color:#ffe900;width:25px;height:25px;display:inline-block;border:1px solid #ccc;'> </span>
				<span onclick="cc('ff1414');" style='cursor:pointer;background-color:#ff1414;width:25px;height:25px;display:inline-block;'> </span>
				<span onclick="cc('ffa814');" style='cursor:pointer;background-color:#ffa814;width:25px;height:25px;display:inline-block;'> </span>
			</div>
        </div>
	</div>
</div>

<script type="text/javascript">
function cc(color){
	document.getElementById("feed").style.color='#'+color;
}
function tc()
{
asyncAjax("GET","get_twitter_log.php",Math.random(),display,{});
setTimeout(tc,refreshtime);
}
function display(xhr,cdat)
{
 if(xhr.readyState==4 && xhr.status==200)
 {
   document.getElementById("feed").innerHTML=xhr.responseText;
 }
}
function asyncAjax(method,url,qs,callback,callbackData)
{
    var xmlhttp=new XMLHttpRequest();
    //xmlhttp.cdat=callbackData;
    if(method=="GET")
    {
        url+="?"+qs;
    }
    var cb=callback;
    callback=function()
    {
        var xhr=xmlhttp;
        //xhr.cdat=callbackData;
        var cdat2=callbackData;
        cb(xhr,cdat2);
        return;
    }
    xmlhttp.open(method,url,true);
    xmlhttp.onreadystatechange=callback;
    if(method=="POST"){
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xmlhttp.send(qs);
    }
    else
    {
            xmlhttp.send(null);
    }
}
tc();
</script>
<?php
# Include the footer
include_once(INCLUDE_PATH.'/footer.php');
?>