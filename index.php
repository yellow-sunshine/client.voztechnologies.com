<?php
# Include configurations for the site
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');

# Include site functions
include_once(INCLUDES.'/functions.php');

session_start();
if($_GET['logout']==1){
	$_SESSION['user']['authenticated']=0;
	unset($_SESSION['user']);
	header('Location://'.ADMIN_DOMAIN.'/index.php');
	exit();
}
if(!$_SESSION['user']['authenticated'] && isset($_POST['submit'])){
	$user = strtolower($_POST['user']);
	$pass = $_POST['pass'];
	if(!$user || !$pass || strlen($pass) < 6 || strlen($user) < 4){
		unset($_SESSION['user']);
		$_SESSION['user']['authenticated'] = 0;
		header("Location: //".ADMIN_DOMAIN."/index.php?r=".$_GET['r']);
		exit();
	}



	if($users[$user]['password'] == $pass){
		unset($_SESSION['user']);
		$_SESSION['user']['authenticated'] = 1;
		$_SESSION['user']['username'] = $user;
		$_SESSION['user']['type'] = $users[$user]['type'];
		$_SESSION['user']['email'] = $users[$user]['email'];
		$_SESSION['user']['name'] = $users[$user]['name'];
		$_SESSION['user']['pages_allowed'] = $users[$user]['pages_allowed'];
		$_SESSION['user']['first_page'] = $users[$user]['first_page'];
		$_SESSION['user']['graffiti'] = $users[$user]['graffiti'];
		if($_POST['r']){
			header("Location: //".ADMIN_DOMAIN."/".preg_replace("@\#@","", $_POST['r']).".php");
			exit();
		}
		if($_GET['r']){
			header("Location: //".ADMIN_DOMAIN."/".preg_replace("@\#@","", $_GET['r']).".php");
			exit();
		}
		if($_SESSION['user']['first_page']){
			header("Location: //".ADMIN_DOMAIN."/".preg_replace("@\#@","", $_SESSION['user']['first_page']).".php");
			exit();
		}else{
			header("Location: //".ADMIN_DOMAIN."/stats.php");
			exit();
		}
		exit();
	}else{
		unset($_SESSION['user']);
		$_SESSION['user']['authenticated'] = 0;
		header("Location: //".ADMIN_DOMAIN."/index.php");
		exit();
	}

}

if(empty($_SESSION['user']['authenticated'])){
	unset($_SESSION['user']);
	$_SESSION['user']['authenticated'] = 0;
	?>
	<html>
	<head>
		<title>Voz Technologies Login</title>
		<link rel="stylesheet" type="text/css" href="//<?php print ADMIN_DOMAIN; ?>/css/campaign.css" />
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
	<style>
	button{
		background:#27ae60;
		border:1px solid #1F8C4D;
		color:#fff;
		padding:20px 70px;
		font-size:24px;
		position:relative;
		transition:padding .2s linear;
		outline:none;
		cursor:pointer;
		min-width:200px;
		-webkit-border-radius:3px;
		-moz-border-radius
		border-radius:3px;
		-webkit-transition:background 0.2s linear;
		-moz-transition:background 0.2s linear;
		transition:background 0.2s linear;
		-webkit-box-shadow:0 2px 2px rgba(0,0,0,0.1);
		-moz-box-shadow:0 2px 2px rgba(0,0,0,0.1);
		box-shadow:0 2px 2px rgba(0,0,0,0.1), inset 0 1px 0 rgba(255,255,255,0.5);
	}
	button:hover{
		 background:#2ecc71;
	}
	button:after{
		content:'';
		display:block;
		position:absolute;
		opacity:0;
		width:30px;
		height:30px;
		border:5px solid rgba(255,255,255,0.3);
		border-right-color:#fff;
		-webkit-border-radius:50%;
		-moz-border-radius:50%;
		border-radius:50%;
		left:-30px;
		top:15px;

		-webkit-transition-property: -webkit-transform;
		-webkit-transition-duration: 1s;

		-moz-transition-property: -moz-transform;
		-moz-transition-duration: 1s;

		-webkit-animation-name: rotate;
		-webkit-animation-duration: 1s;
		-webkit-animation-iteration-count: infinite;
		-webkit-animation-timing-function: linear;

		-moz-animation-name: rotate;
		-moz-animation-duration: 1s;
		-moz-animation-iteration-count: infinite;
		-moz-animation-timing-function: linear;

		transition:all 0.2s linear;
		-webkit-transform:scale(2);
		transform:scale(2);
	}

	button.loading:after {
		opacity:1;
		left:15px;
	}

	@-webkit-keyframes rotate {
		from {-webkit-transform: rotate(0deg);}
		to {-webkit-transform: rotate(360deg);}
	}

	@-moz-keyframes rotate {
		from {-moz-transform: rotate(0deg);}
		to {-moz-transform: rotate(360deg);}
	}

	*{
	  -webkit-box-sizing:border-box;
	  -moz-box-sizing:border-box;
	  box-sizing:border-box;
	}

	body{
	  background:#ededed;
	  font-family:Arial, sans-serif;
	}

	a{
	  color:#7f8c8d;
	}

	.form-container{
	  padding: 50px 40px;
	  background:#fff;
	  width:300px;
	  text-align:center;
	  -webkit-box-shadow:0 2px 3px rgba(0,0,0,0.2);
	  -moz-box-shadow:0 2px 3px rgba(0,0,0,0.2);
	  box-shadow:0 2px 3px rgba(0,0,0,0.2);
	  margin:0 auto;
	  margin-top:100px;
	  -webkit-transition:all 1s linear;
	  -moz-transition:all 1s linear;
	  transition:all 1s linear;
	  position:relative;
	}

	.form-container:after{
	  content:"";
	  display:block;
	  position:absolute;
	  top:0;
	  left:0;
	  width:40px;
	  height:8px;
	  background:#e74c3c;
	  -webkit-box-shadow:65px 0 0 #e67e22, 130px 0 0 #f1c40f, 195px 0 0 #1abc9c, 260px 0 0 #e74c3c;
	  -moz-box-shadow:65px 0 0 #e67e22, 130px 0 0 #f1c40f, 195px 0 0 #1abc9c, 260px 0 0 #e74c3c;
	  box-shadow:65px 0 0 #e67e22, 130px 0 0 #f1c40f, 195px 0 0 #1abc9c, 260px 0 0 #e74c3c;
	}

	.form-container h3{
	  font-size:32px;
	  text-align:center;
	  color:#666;
	  margin:0 0 30px;
	}

	.form-container .login-form > div{
	  margin-bottom:10px;
	}

	.form-container .login-form > div > input{
	  background-color:#fff;
	  border:2px solid #dedede;
	  padding:10px;
	  font-size:18px;
	  color:#666;
	  -webkit-border-radius:3px;
	  -moz-border-radius:3px;
	  border-radius:3px;
	  max-width:220px;
	  outline:none;
	  -webkit-transition:border-color 0.2s linear;
	  -moz-transition:border-color 0.2s linear;
	  transition:border-color 0.2s linear;
	}

	.form-container .login-form > div > input:focus{
	  border-color:#A5A5A5;
	}
	</style>
	</head>
		<body>
			<div ng-app="App">
				<div class="form-container" ng-class="done">
					<form method="post" enctype="application/x-www-form-urlencoded" action="/?r=<?php print $_GET['r'];?>">
						<div class="login-form">
							<img src="/VozTechnologies-small.png" width="225" height="65" alt="Voz Technologies" />
							<div>
								<input name='user' autocomplete="on" placeholder="Username" />
							</div>
							<div>
								<input name='pass' autocomplete="on" type="password" placeholder="Password" />
							</div>
							<button data-loading-btn class="" type="submit" name='submit' value="Login">
								<span>Log in</span>
							</button>
						</div>
					</form
				</div>
			</div>
		</body>
	</html>
	<?php
	exit();
}elseif($_SESSION['user']['first_page']){
	header("Location: //".ADMIN_DOMAIN."/".preg_replace("@\#@","", $_SESSION['user']['first_page']).".php");
	exit();
}else{
	header("Location: //".ADMIN_DOMAIN."/stats.php");
	exit();
}
?>