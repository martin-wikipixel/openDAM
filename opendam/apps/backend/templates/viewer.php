<?php set_time_limit(0); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<?php include_title() ?>
		<script type="text/javascript" src="<?php echo url_for("@wpI18n_calalogues");?>"></script>
	</head>
	<body>
		<?php echo $sf_data->getRaw('sf_content'); ?>
	</body>
</html>