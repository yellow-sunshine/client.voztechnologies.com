<?php
session_start();
?>
	<!-- Main navbar -->
	<div class="navbar">
		<div class="navbar-header">
			<?php
				if($_SESSION['user']['graffiti'] == 1){
					?><a class="navbar-brand" href="http://voztechnologies.com/"><img src="/VozTechnologies-tiny.png" alt=""></a><?php
				}else{
					?><a class="navbar-brand" href="http://voztechnologies.com/"><img src="/GorillaTechLogoText.png" alt=""></a><?php
				}
			?>

			<ul class="nav navbar-nav pull-right visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
				<?php
                if(!$dontShowSide && in_array_r('sidebar',$_SESSION['user']['pages_allowed'])){
                ?>
                	<li><a class="sidebar-mobile-main-toggle sidebar-main-toggle"><i class="icon-paragraph-justify3"></i></a></li>
				<?php
				}
				?>
            </ul>
		</div>

		<div class="navbar-collapse collapse" id="navbar-mobile">

			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown dropdown-user">
					<a class="dropdown-toggle" data-toggle="dropdown">
						<?php
						if(file_exists(BASE_PATH.'/assets/images/profiles/'.$_SESSION['user']['username'].'.jpg')){
							?><img src='/assets/images/profiles/<?php print$_SESSION['user']['username'];?>.jpg' alt=""><?php
						}else{
							?><img src="/assets/images/placeholder.jpg" alt=""><?php
						}
						?>

						<span><?php print $_SESSION['user']['username']; ?></span>
						<i class="caret"></i>
					</a>

					<ul class="dropdown-menu dropdown-menu-right">
						<li><a href="#"><i class="icon-cog5"></i> Account settings</a></li>
						<li class="divider"></li>
						<li><a href="/index.php?logout=1"><i class="icon-switch2"></i> Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	<!-- /main navbar -->