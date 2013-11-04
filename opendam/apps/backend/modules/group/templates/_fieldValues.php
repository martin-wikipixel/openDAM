<div class="dialog">
	<label><?php echo __("List of values:"); ?></label>
	<div class="left" style="clear: both;">
		<span id="empty_values" class="description" style="margin-left: 0px; <?php echo empty($values) ? "" : "display: none;" ?>"><?php echo __("Empty"); ?></span>
		<ul id="sortable" class="left">
			<?php if(!empty($values)) : ?>
				<?php foreach($values as $value) : ?>
					<li class='text' id='<?php echo $value; ?>' style="padding-top: 0px; padding-bottom: 0px;"><img src='<?php echo image_path("rightarr.png"); ?>' /><?php echo $value; ?><a href="javascript: void(0);" class="delete_value_link"><img class="delete_value" src="<?php echo image_path("icons/delete12.png"); ?>" style="margin-top: 4px;" /></a></li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</div>

	<br clear="all" />
	<br clear="all" />

	<label><?php echo __("New value:"); ?></label>
	<br clear="all" />
	<input type="text" name="new_value" id="new_value" style="float: left; width: 200px;" />
	<a href="javascript: void(0);" class="but_admin" id="add_new_value"><span><?php echo __("Add to list"); ?></span></a>
	<span class="require_field" style="clear: both; float: left; display: none;" id="error_value"></span>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery("#sortable").sortable({
			axis: 'y',
			opacity: 0.6,
			revert: true
		});

		jQuery("#sortable").disableSelection();

		jQuery("#add_new_value").bind("click", function() {
			if(jQuery("#new_value").val() != "")
			{
				jQuery("#error_value").fadeOut('slow', function() {
					jQuery(this).html("");

					jQuery("#empty_values").fadeOut('slow', function() {
						jQuery("#sortable").fadeOut('slow', function() {
							li = jQuery("<li class='text' id='" + jQuery("#new_value").val() + "' style='padding-top: 0px; padding-bottom: 0px;'><img src='<?php echo image_path("rightarr.png"); ?>' />" + jQuery("#new_value").val() + "<a href='javascript: void(0);' class='delete_value_link'><img class='delete_value' src='<?php echo image_path("icons/delete12.png"); ?>' style='margin-top: 4px;' /></a></li>");
							jQuery("#sortable").append(li);
							jQuery("#new_value").val('');
							jQuery("#sortable").fadeIn('slow');
						});
					});
				});
			}
			else
				jQuery("#error_value").html("<?php echo __("New value is required."); ?>").fadeIn('slow');
		});

		jQuery(".delete_value_link").live("click", function() {
			if(confirm("<?php echo __("Are you sur to want to delete this value?"); ?>"))
				jQuery(this).parent().fadeOut('slow', function() { jQuery(this).remove() });
		});
	});
</script>