<div id="supports">
	<?php foreach($supports as $support) : ?>
		<div class='support'>
			<input type="checkbox" name="support_<?php echo $support->getId(); ?>" id="support_<?php echo $support->getId(); ?>" rel="<?php echo $support->getTitle(); ?>" value="<?php echo $support->getId(); ?>" class="left check_support" style="margin-right: 5px;" />
			<label for="support_<?php echo $support->getId(); ?>" style="width: auto;"><?php echo $support->getTitle(); ?></label>
		</div>
	<?php endforeach; ?>
</div>

<br clear="all" />

<div style="width: 100%; border-top: 1px dashed #737373;margin-top: 20px; padding-top: 5px;">
	<input type="text" name="new_support" id="new_support" style="width: 200px;" class="left nc" value="<?php echo __("New support"); ?>" />

	<button id="add_support" class="btn btn-primary"><?php echo __("Add");?></button>
	<span style="clear: both; float: left;" class="require_field" id="error_support"></span>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery("#new_support").bind("focus", function() {
			if(jQuery(this).val() == "<?php echo __("New support"); ?>")
			{
				jQuery(this).val("");
				jQuery(this).removeClass("nc");
			}
		});

		jQuery("#new_support").bind("blur", function() {
			if(jQuery(this).val() == "")
			{
				jQuery(this).val("<?php echo __("New support"); ?>");
				jQuery(this).addClass("nc");
			}
		});

		jQuery("#add_support").bind("click", function() {
			jQuery("#error_support").fadeOut(200, function() {
				if(jQuery.trim(jQuery('#new_support').val()).length <= 0 || jQuery('#new_support').val() == "<?php echo __("New support"); ?>")
				{
					jQuery("#error_support").html("<?php echo __("Support is required."); ?>");
					jQuery("#error_support").fadeIn();
				}
				else
				{
					jQuery.post(
						"<?php echo url_for("right/saveSupport"); ?>",
						{ support: jQuery('#new_support').val() },
						function(data) {
							if(data.code > 0)
							{
								jQuery("#error_support").html(data.html);
								jQuery("#error_support").fadeIn();
							}
							else
							{
								jQuery("#supports").fadeOut(200, function() {
									jQuery(this).append(data.html);
									jQuery('#new_support').val("");
									jQuery(this).fadeIn();
								});
							}
						},
						"json"
					);
				}
			});
		});
	});
</script>
