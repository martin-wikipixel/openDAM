<?php define("__DIR__", dirname(__FILE__)); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php include_title() ?>

		<link href="<?php echo url_for("@less_index?src=apps/backend/main.less");?>" media="screen" type="text/css" rel="stylesheet">

		<?php require_once __DIR__."/assets/layout_v2/_stylesheets.php";?>
		<?php include_stylesheets()?>

		<?php require_once __DIR__."/assets/layout_v2/_javascripts.php";?>
		<?php include_javascripts()?>
	</head>

	<body class="no-padding">
		<div id="notifications-container" class="notifications center"></div>

		<?php echo $sf_data->getRaw("sf_content"); ?>

		<script type="text/javascript" src="<?php echo url_for("@wpI18n_calalogues");?>"></script>
		<?php include_partial("global/messages_v2"); ?>
	</body>
</html>
