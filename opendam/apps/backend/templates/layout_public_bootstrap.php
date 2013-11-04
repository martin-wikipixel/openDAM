<?php define("__DIR__", dirname(__FILE__)); ?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<?php include_title() ?>
		<?php require_once __DIR__."/assets/layout/_stylesheetsPublic.php";?>
		<?php include_stylesheets()?>

		<?php require_once __DIR__."/assets/layout/_javascripts.php";?>
		<?php include_javascripts()?>
		
		<link rel="shortcut icon" href="<?php echo image_path("favicon.ico"); ?>" />
		
		<script type="text/javascript">
			var configPath = "<?php echo url_for('@homepage', true); ?>";
			<?php
				$sizes = "var facebox_sizes = new Array(";

				foreach(sfConfig::get("app_facebox_iframe_sizes") as $size)
				{
					$temp = "new Array(";
					foreach(sfConfig::get("app_facebox_".$size."_pages") as $url)
						$temp .= "'".$url."',";

					$temp = substr($temp, 0, -1).")";

					$sizes .= "new faceboxSizes(".$size.",".$temp."),";
				}

				$sizes = substr($sizes, 0, -1).");";

				echo $sizes;
			?>
		</script>
	</head>
	<body>
		<div id="notifications-container" class="notifications center"></div>
		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<?php if (has_slot('homepage_link')): ?>
						<a href="<?php include_slot('homepage_link') ?>" class="brand scroller">
							<img alt="logo" src="<?php echo image_path("bootstrap/layout/logo-wikipixel.png"); ?>" />
						</a>
					<?php else: ?>
						<a href="<?php echo url_for("@homepage"); ?>" class="brand scroller">
							<img alt="logo" src="<?php echo image_path("bootstrap/layout/logo-wikipixel.png"); ?>" />
						</a>
					<?php endif; ?>

					<p class="navbar-text">
						<span><?php echo __("Digital Asset Management Software"); ?></span>
					</p>
				</div>
			</div>
		</div>

		<?php echo $sf_data->getRaw("sf_content"); ?>
		<script type="text/javascript" src="<?php echo url_for("@wpI18n_calalogues");?>"></script>
	</body>
</html>