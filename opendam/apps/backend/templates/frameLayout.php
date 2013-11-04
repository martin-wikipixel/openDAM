<?php define("__DIR__", dirname(__FILE__)); ?>
<!DOCTYPE html>
<html>
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
	
	<link href="/css/jquery-ui-1.7.1.custom.css" media="screen" type="text/css" rel="stylesheet">
	
		<?php require_once __DIR__."/assets/layout/_stylesheets.php";?>
		<?php include_stylesheets()?>

		<?php require_once __DIR__."/assets/layout/_javascripts.php";?>
		<?php include_javascripts()?>
	
	<script type="text/javascript" src="<?php echo url_for("@wpI18n_calalogues");?>"></script>
	
    <link rel="shortcut icon" href="<?php echo image_path("favicon.ico"); ?>" />
	<script>
		var configPath = "<?php echo url_for('@homepage', true); ?>";
	</script>
  </head>
  <body class="oldBody" style="text-align: left; width: 100%;">
  	<div id="notifications-container" class="notifications center"></div>
	<?php $title = get_slot('title'); ?>
    <?php if(isset($title)) echo "<h2>".$title."</h2>";?>
    <?php include_partial("global/messages", array());?>
    <?php echo $sf_data->getRaw('sf_content') ?>
  </body>
</html>