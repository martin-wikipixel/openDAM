<?php $creative_commons = CreativeCommonsPeer::getCreativeCommons(); ?>

<?php if($role) : ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Creative commons"); ?> :
		</div>
	</div>

	<br clear="all" />

	<?php $i = 0; ?>
	<?php foreach($creative_commons as $creative_common) : ?>
		<div class="rub" style="width: 100%;">
			<div class="value-right" style="width: 100%;">
				<input type="radio" name="creatives" id="creatives_<?php echo $creative_common->getId(); ?>" value="<?php echo $creative_common->getId(); ?>" <?php echo $creative_common->getId() == $file->getCreativeCommonsId() ? "checked" : ""; ?> class="left" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" /> <label class="left tooltipLeft" for="creatives_<?php echo $creative_common->getId(); ?>"  name="<?php echo $creative_common->getDescription(); ?>"><?php echo $creative_common->getTitle(); ?> <img src="<?php echo image_path($creative_common->getImagePath()); ?>" style="vertical-align: -3px;" /> <i class="icon-ok-sign" style="display: none;"></i></label>
			</div>
		</div>

		<br clear="all" />
		<?php $i++; ?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Creative commons"); ?> :
		</div>

		<?php if($file->getCreativeCommonsId()) : ?>
			<div class="value-right no-border" style="width: 50%;">
				<?php $creative_common = $file->getCreativeCommons(); ?>
				<label class="left tooltipLeft" name="<?php echo $creative_common->getDescription(); ?>"><?php echo $creative_common->getTitle(); ?> <img src="<?php echo image_path($creative_common->getImagePath()); ?>" style="vertical-align: -3px;" /></label>
			</div>
		<?php else: ?>
			<div class="value-right" style="width: 50%;">
				<span class='text'><?php echo __("To inform"); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<br clear="all" />
<?php endif; ?>

<br clear="all" />
<?php if($role) : ?>
	<script>
	jQuery(document).ready(function() {
		tooltipLeft();

		jQuery('input[name="creatives"]').bind("click", function() {
			var object = jQuery(this);

			jQuery.post(
				"<?php echo url_for("file/field"); ?>",
				{ id: <?php echo $file->getId(); ?>, field: "creativecommons", value: object.val() },
				function(data) {
					object.parent().find(".icon-ok-sign").fadeIn('slow').delay(1000).fadeOut('slow');
				}
			);
		});
	});
	</script>
<?php endif; ?>