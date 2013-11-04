<?php 
	$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
	$relative = image_path("no-access-file-200x200.png");
	$size = getimagesize($absolute);
?>
<div class="group item-group item" id="add-group">
	<div class="contain">
		<div class="thumbnail">
			<img src="<?php echo $relative; ?>" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" />
		</div>
		<div class="info-group edition">
			<div class="title">
				<input name="group-name" id="group-name" class="input-medium" placeholder="<?php echo __("New album"); ?>" />
				<button class="btn btn-primary" data-role="submit"><i class="icon-ok"></i></button>
				<button class="btn" data-role="cancel"><i class="icon-remove"></i></button>
			</div>
		</div>
	</div>
</div>
<script defer src="/<?php echo sfConfig::get("app_path_js_dir_name"); ?>/apps/backend/src/modules/album/new.js"></script>