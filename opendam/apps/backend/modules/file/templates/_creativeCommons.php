<div id="creative_commons_img">
	<span>
		<a href="javascript: void(0);" <?php echo $file->getCreativeCommonsId() ? 'class="tooltip"' : ""; ?> name="<?php echo $file->getCreativeCommonsId() ? $file->getCreativeCommons()->getDescription() : ""; ?>">
			<img src="<?php echo $file->getCreativeCommonsId() ? image_path($file->getCreativeCommons()->getImagePath()) : image_path("creative_commons/cc.jpg"); ?>" />
		</a>
	</span>
	<a href="javascript: void(0);" class="edit-limitation"></a>
</div>
<div id="edit_creative_commons" style="display: none;">
	<select name="creative_commons_select" id="creative_commons_select" style="float: left; width: 150px;">
		<?php $creative_commons = CreativeCommonsPeer::getCreativeCommons(); ?>
		<?php foreach($creative_commons as $creative_common) : ?>
			<option value="<?php echo $creative_common->getId(); ?>" <?php echo $file->getCreativeCommonsId() == $creative_common->getId() ? "selected" : ""; ?>><?php echo $creative_common->getTitle(); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<?php if($role) : ?>
	<script>
	jQuery(document).ready(function() {
		jQuery("#creative_commons_img").hover(
			function() {
				jQuery(this).find("a.edit-limitation").fadeIn();
			}, 
			function() {
				jQuery(this).find("a.edit-limitation").fadeOut();
			}
		);

		jQuery("#creative_commons_select").bind("change", function() {
			jQuery(this).trigger("blur");
		});

		jQuery("#creative_commons_select").bind("blur", function() {
			jQuery.post(
				"<?php echo url_for("file/field"); ?>",
				{ field: "creativecommons", value: jQuery("#creative_commons_select").val(), id: <?php echo $file->getId(); ?> },
				function(data) {
					jQuery("#creative_commons_img span").html("<a href='javascript: void(0);' class='tooltip' name='" + data.description + "'><img src='" + data.img + "' /></a>");
					tooltip();
					jQuery("#edit_creative_commons").fadeOut(200, function() {
						jQuery("#creative_commons_img").fadeIn();
					});
				},
				"json"
			);
		});

		jQuery("#creative_commons_img a.edit-limitation").bind("click", function() {
			jQuery("#creative_commons_img").fadeOut(200, function() {
				jQuery("#edit_creative_commons").fadeIn(200, function() {
					jQuery("#creative_commons_select").focus();
				});
			});
		});
	});
	</script>
<?php endif; ?>