<?php define("__DIR__", dirname(__FILE__)); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<?php include_title() ?>
		<?php require_once __DIR__."/assets/layout/_stylesheets.php";?>
		<?php include_stylesheets()?>

		<?php require_once __DIR__."/assets/layout/_javascripts.php";?>
		<?php include_javascripts()?>
		<link rel="shortcut icon" href="<?php echo image_path("favicon.ico"); ?>" />
		<script type="text/javascript">
			function noError(){
				return true;
			}
			window.onerror = noError;
			var configPath = "<?php echo url_for('@homepage', true); ?>";
		</script>
	</head>
	<body>
		<div id="iframe">
			<?php echo $sf_data->getRaw('sf_content'); ?>
		</div>
	</body>
</html>