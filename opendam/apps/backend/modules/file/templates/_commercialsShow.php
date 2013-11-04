<?php $commercials = UsageCommercialPeer::getCommercials(); ?>

<?php if($role) : ?>
	<?php $i = 0; ?>
	<?php foreach($commercials as $commercial) : ?>
		<div class="rub" style="width: 100%;">
			<div class="label-right">
				<?php if($i > 0) : ?>
					<span style="visibility: hidden;"><?php echo __("Commercial"); ?> :</span>
				<?php else: ?>
					<?php echo __("Commercial"); ?> :
				<?php endif; ?>
			</div>

			<div class="value-right" style="width: 50%;">
				<input type="radio" name="commercials" id="commercials_<?php echo $commercial->getId(); ?>" value="<?php echo $commercial->getId(); ?>" <?php echo $commercial->getId() == $file->getUsageCommercialId() ? "checked" : ""; ?> class="left" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" /> <label class="left" for="commercials_<?php echo $commercial->getId(); ?>"><?php echo $commercial->getTitle(); ?></label>
			</div>
		</div>

		<br clear="all" />
		<?php $i++; ?>
	<?php endforeach; ?>
<?php else: ?>
	<?php $commercial = $file->getUsageCommercialId() ?  $file->getUsageCommercial()->getTitle() : "<span class='text'>".__("To inform")."</span>"; ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Commercial"); ?> :
		</div>

		<div class="value-right" style="width: 50%;">
			<?php echo $commercial; ?>
		</div>
	</div>

	<br clear="all" />
<?php endif; ?>

<br clear="all" />

<?php if($role) : ?>
	<script>
		jQuery(document).ready(function() {
			jQuery('input[name="commercials"]').bind("click", function() {
				var object = jQuery(this);

				jQuery.post(
					"<?php echo url_for("file/field"); ?>",
					{ id: <?php echo $file->getId(); ?>, field: "commercial", value: object.val() }
				);
			});
		});
	</script>
<?php endif; ?>