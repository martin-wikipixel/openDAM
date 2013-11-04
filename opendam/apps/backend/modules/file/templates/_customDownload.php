<div class="left">
	<input type="hidden" name="ratio_value" id="ratio_value" value="<?php echo round($file->getWidth() / $file->getHeight(), 6); ?>" />
	<input type="hidden" name="min_width" id="min_width" value="<?php echo $file->getWidth(); ?>" />
	<input type="hidden" name="min_height" id="min_height" value="<?php echo $file->getHeight(); ?>" />

	<label><?php echo __("Format"); ?></label>

	<br clear="all" />

	<select name="format" id="format" class="left" style="width: 206px;">
		<option value=""><?php echo __("Select format"); ?></option>
		<?php foreach($formats as $format) : ?>
			<?php if($file->getType() == FilePeer::__TYPE_VIDEO) : ?>
				<?php $temp = explode("|", $format); $format_display = array_key_exists(1, $temp) ? $temp[0]." (".$temp[1].")" : $format; ?>
			<?php else: ?>
				<?php $format_display = $format; ?>
			<?php endif; ?>

			<option value="<?php echo $format; ?>"><?php echo $format_display; ?></option>
		<?php endforeach; ?>
	</select>

	<br clear="all" />

	<label><?php echo __("Width"); ?></label>

	<br clear="all" />

	<input type="text" name="width" id="width" value="<?php echo $file->getWidth(); ?>" class="left" style="width: 200px;" />

	<br clear="all" />

	<label><?php echo __("Height"); ?></label>

	<br clear="all" />

	<input type="text" name="height" id="height" value="<?php echo $file->getHeight(); ?>" class="left" style="width: 200px;" />

	<br clear="all" />

	<input type="checkbox" name="ratio" id="ratio" checked />
	<div class="left text" style="margin-left: 5px;"><?php echo __("Keep ratio"); ?></div>

	<br clear="all" />
	<br clear="all" />

	<div class="require_field" id="error"></div>
	<form method="post" id="form_download" name="form_download" target="iframe_download"></form>
	
</div>
<script>
	jQuery(document).ready(function() {
		jQuery("#width").bind("keyup", function() {
			if(jQuery("#ratio").is(":checked"))
			{
				var temp = Math.round(jQuery(this).val() / jQuery("#ratio_value").val());
				jQuery("#height").val(temp);
			}
		});

		jQuery("#width").bind("blur", function() {
			if(parseInt(jQuery(this).val()) > parseInt(jQuery("#min_width").val()))
			{
				jQuery("#width").val(jQuery("#min_width").val());
				jQuery("#height").val(jQuery("#min_height").val());
			}
		});

		jQuery("#height").bind("keyup", function() {
			if(jQuery("#ratio").is(":checked"))
			{
				var temp = Math.round(jQuery(this).val() * jQuery("#ratio_value").val());
				jQuery("#width").val(temp);
			}
		});

		jQuery("#height").bind("blur", function() {
			if(parseInt(jQuery(this).val()) > parseInt(jQuery("#min_height").val()))
			{
				jQuery("#width").val(jQuery("#min_width").val());
				jQuery("#height").val(jQuery("#min_height").val());
			}
		});
	});
</script>