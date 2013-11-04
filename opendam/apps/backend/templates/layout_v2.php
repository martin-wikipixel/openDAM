<?php
	define("__DIR__", dirname(__FILE__));
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php my_include_title("Wikipixel")?>

		<link rel="shortcut icon" href="<?php echo image_path("favicon.ico"); ?>" />

		<link href="<?php echo url_for("@less_index?src=apps/backend/main.less");?>" media="screen" type="text/css" rel="stylesheet">

		<?php require_once __DIR__."/assets/layout_v2/_stylesheets.php";?>
		<?php include_stylesheets()?>

		<?php require_once __DIR__."/assets/layout_v2/_javascripts.php";?>
		<?php include_javascripts()?>
	</head>

	<body>
		<div id="notifications-container" class="notifications center"></div>
	
		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container clearfix">
					<a class="homepage brand" href="<?php echo path("homepage"); ?>">
						<img alt="Wikipixel" src="<?php echo image_path("bootstrap/layout/logo-wikipixel.png"); ?>" />
					</a>
					
					<div class="nav-collapse collapse">
						<?php include_partial("search/top", array());?>
						<?php include_partial("global/top_nav")?>
					</div><!--/.nav-collapse -->
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<?php echo $sf_data->getRaw("sf_content"); ?>
			</div>

			<footer id="footer">
				<a href="<?php echo url_for('public/setCulture?culture=fr'); ?>">Français</a> - 
				<a href="<?php echo url_for('public/setCulture?culture=en'); ?>">English</a>
			
				<span class="pull-right">
					<?php echo __("Provided by")?> <a href="http://www.wikipixel.com">wikipixel</a>
				</span>
			</footer>
		</div>
	
		<script type="text/javascript" src="<?php echo url_for("@wpI18n_calalogues");?>"></script>
		<?php include_partial("global/messages_v2"); ?>
	</body>
</html>
