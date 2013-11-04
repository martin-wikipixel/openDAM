<div class="text load-thumb">
	<select name="group" id="group" style="float: left; width: 200px;">
		<?php foreach($groups as $groupId => $groupName) : ?>
			<option value="<?php echo $groupId; ?>" <?php echo $groupId == $group_id ? "selected" : ""; ?>><?php echo $groupName; ?></option>
		<?php endforeach; ?>
	</select>
	<div id="container-folder">
		<select name="folder" id="folder" style="float: left; width: 200px; margin-left: 10px;">
			<option value="all"><?php echo __("The entire main folder"); ?></option>
			<?php foreach($folders as $folder_id => $label) : ?>
				<option value="<?php echo $folder_id; ?>"><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<br clear="all" />

	<div class="files-thumb">
		<?php include_partial("file/getFilesThumb", array("files" => $files)); ?>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery("#group").bind("change", function() {
			var group_id = jQuery("#group").val();
			jQuery(".load-thumb").fadeOut(200, function() {
				jQuery(this).html("<p style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></p>");
				jQuery(this).fadeIn(200, function() {
					jQuery.post(
						"<?php echo url_for("file/loadThumb"); ?>",
						{ group_id: group_id },
						function(data) {
							jQuery(".load-thumb").parent().fadeOut(200, function() {
								jQuery(this).html(data);
								jQuery(this).fadeIn();
							});
						}
					);
				})
			});
		});

		jQuery("#folder").bind("change", function() {
			jQuery(".files-thumb").fadeOut(200, function() {
				jQuery(this).html("<p style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></p>");
				jQuery(this).fadeIn(200, function() {
					jQuery.post(
						"<?php echo url_for("file/loadThumb"); ?>",
						{ folder_id: jQuery("#folder").val(), group_id: jQuery("#group").val() },
						function(data) {
							jQuery(".files-thumb").fadeOut(200, function() {
								jQuery(this).html(data);
								jQuery(this).fadeIn();
							});
						}
					);
				})
			});
		});
	});
</script>