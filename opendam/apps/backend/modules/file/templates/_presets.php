<?php $presets = PresetPeer::retrieveByCustomerId($sf_user->getCustomerId()); ?>

<div class="rub">
	<div class="label-right"><?php echo __("Presets:"); ?></div>
</div>

<br clear="all" />

<div class="rub">
	<div class="value-right">
		<select name="presets_choice" id="presets_choice" style="width: 150px; margin-top: 0px; margin-bottom: 0px;">
			<option value="0"><?php echo __("Choose"); ?></option>
			<?php foreach($presets as $preset) : ?>
				<option value="<?php echo $preset->getId(); ?>"><?php echo $preset->getName(); ?></option>
			<?php endforeach; ?>
		</select>
		<div id="loader_load" style="display: none; float: left; width: 214px;"><img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" style="vertical-align: -3px;" /><br clear="all" /></div>
	</div>
</div>

<br clear="all" />
<br clear="all" />


	<script>
		jQuery(document).ready(function() {
			jQuery("#presets_choice").bind("change", function() {
					if(confirm("<?php echo __("Are your sure to want to apply this preset to this file?"); ?>"))
					{
						jQuery("#presets_choice").fadeOut('slow', function() {
							jQuery("#loader_load").fadeIn('slow', function() {
								jQuery.post(
									"<?php echo url_for("preset/applyPreset"); ?>",
									{ id: jQuery("#presets_choice").val(), file_id: <?php echo $file->getId(); ?> },
									function(data) {
										window.location.reload();
									}
								);
							});
						});
					}
			});
		});
	</script>

