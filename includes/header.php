<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Voz Admin <?php if($lang)print $lang['Script_Administration_Header']; ?></title>
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="/assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
	<link href="/assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="/assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="/assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="/assets/css/colors.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/custom.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="/assets/js/plugins/loaders/pace.min.js"></script>
	<script type="text/javascript" src="/assets/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/js/core/libraries/bootstrap.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/loaders/blockui.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/ui/nicescroll.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/ui/drilldown.js"></script>
    <script type="text/javascript" src="/assets/js/plugins/extensions/jquery.animate-colors-min.js"></script>
    <script type="text/javascript" src="/assets/js/core/voz.js"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="/assets/js/plugins/tables/datatables/datatables.min.js"></script>
		<script type="text/javascript" src="/assets/js/core/libraries/jquery_ui/interactions.min.js"></script>
		<script type="text/javascript" src="/assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/forms/selects/select2.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/forms/styling/switchery.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/forms/styling/switch.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/visualization/d3/d3.min.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/visualization/d3/d3_tooltip.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/visualization/dimple/dimple.min.js"></script>
	<script type="text/javascript" src="/assets/js/core/app.js"></script>
	<script type="text/javascript" src="/assets/js/pages/datatables_responsive.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/ui/jquery.blink.js"></script>
	<script type="text/javascript" src="/assets/js/plugins/notifications/pnotify.min.js"></script>

	<script type="text/javascript">
		function AcceptDigits(objtextbox){var exp=/[^\d]/g;objtextbox.value=objtextbox.value.replace(exp,'');}
	</script>
	<!-- /theme JS files -->
</head>
<body>
<?php
include_once(INCLUDE_PATH.'/main_navbar.php');
include_once(INCLUDE_PATH.'/second_navbar.php');
?>

	<!-- Page header -->
	<div class="page-header">
		<div class="page-header-content">
			<div class="page-title">
				<h4>
					&nbsp;&nbsp;<i class="icon-file-empty2 position-left"></i>
					<span class="text-semibold"><?php print $PAGE_NAME; ?></span>
				</h4>
			</div>
            <!-- div class="heading-elements" -->
            <!-- /div -->
		</div>
	</div>
	<!-- /page header -->


	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

<?php
if(!$dontShowSide && in_array_r('sidebar',$_SESSION['user']['pages_allowed'])){
	include_once(INCLUDES.'/voz_sidebar.php');
}elseif(in_array_r('option_sniper_sidebar',$_SESSION['user']['pages_allowed'])){
	include_once(INCLUDES.'/option_sniper_sidebar.php');
}
?>



<!-- Main content -->
<div class="content-wrapper">
