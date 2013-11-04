<?php $uses = UsageUsePeer::getUses(); ?>
<?php $distributions = UsageDistributionPeer::getDistributions(); ?>

<?php if($role) : ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Use"); ?> :
		</div>
	</div>

	<br clear="all" />

	<?php $i = 0; ?>
	<?php foreach($uses as $use) : ?>
		<div class="rub" style="width: 100%;">
			<div class="value-right" style="width: 100%;">
				<input type="radio" name="uses" id="uses_<?php echo $use->getId(); ?>" value="<?php echo $use->getId(); ?>" <?php echo $use->getId() == $file->getUsageUseId() ? "checked" : ""; ?> class="left" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" /> 
				<label class="left" for="uses_<?php echo $use->getId(); ?>">
					<?php echo $use->getTitle(); ?> 
					<?php if($use->getDescription()) : ?>
						<a class="tooltipLeft" href="javascript: void(0);" name="<?php echo $use->getDescription(); ?>"><i class="icon-question-sign"></i></a>
					<?php endif; ?>
					<i class="icon-ok-sign" style="display: none;"></i>
				</label>
			</div>
		</div>

		<br clear="all" />
		<?php $i++; ?>
	<?php endforeach; ?>
<?php else: ?>
	<?php $usage = $file->getUsageUseId() ? $file->getUsageUse() : ""; ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Use"); ?> :
		</div>

		<div class="value-right" style="width: 50%;">
			<?php if(!empty($usage)) : ?>
				<?php echo $usage->getTitle; ?>
				<?php if($usage->getDescription()) : ?>
					<a class="tooltipLeft" href="javascript: void(0);" name="<?php echo $usage->getDescription(); ?>"><i class="icon-question-sign"></i></a>
				<?php endif; ?>
			<?php else: ?>
				<span class='text'><?php echo __("To inform"); ?></span>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<br clear="all" />

<?php if($role) : ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Distribution"); ?> :
		</div>
	</div>

	<br clear="all" />

	<?php $i = 0; ?>
	<?php foreach($distributions as $distribution) : ?>
		<div class="rub" style="width: 100%;">
			<div class="value-right" style="width: 100%;">
				<input type="radio" name="distributions" id="distributions_<?php echo $distribution->getId(); ?>" value="<?php echo $distribution->getId(); ?>" <?php echo $distribution->getId() == $file->getUsageDistributionId() ? "checked" : ""; ?> class="left" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" />
				<label class="left" for="distributions_<?php echo $distribution->getId(); ?>">
					<?php echo $distribution->getTitle(); ?> <i class="<?php echo $distribution->getId() == UsageDistributionPeer::__AUTH ? "icon-unlock" : "icon-lock"; ?>"></i>
					<?php if($distribution->getDescription()) : ?>
						<a class="tooltipLeft" href="javascript: void(0);" name="<?php echo $distribution->getDescription(); ?>"><i class="icon-question-sign"></i></a>
					<?php endif; ?>
					<i class="icon-ok-sign" style="display: none;"></i>
				</label>
			</div>
		</div>

		<br clear="all" />
		<?php $i++; ?>
	<?php endforeach; ?>
<?php else: ?>
	<?php $distribution = $file->getUsageDistributionId() ?  $file->getUsageDistribution() : ""; ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Distribution"); ?> :
		</div>

		<div class="value-right" style="width: 50%;">
			<?php if(!empty($distribution)) : ?>
				<?php echo $distribution->getTitle(); ?>
				<?php if($distribution->getDescription()) : ?>
					<a class="tooltipLeft" href="javascript: void(0);" name="<?php echo $distribution->getDescription(); ?>"><i class="icon-question-sign"></i></a>
				<?php endif; ?>
			<?php else: ?>
				<span class='text'><?php echo __("To inform"); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<br clear="all" />
<?php endif; ?>

<br clear="all" />

<div id="limitation" <?php echo $file->getUsageDistributionId() != UsageDistributionPeer::__AUTH ? "style='display: none;'" : ""; ?>>
	<?php include_partial("file/limitationsShow", array("file" => $file, "role" => $role)); ?>
</div>

<?php if($role) : ?>
	<script>
		function showLimitations()
		{
			jQuery("#limitation").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");
			jQuery("#limitation").fadeIn(200, function() {
				jQuery.post(
					"<?php echo url_for("file/showLimitationsShow"); ?>",
					{ file_id: <?php echo $file->getId(); ?>, role: <?php echo $role; ?> },
					function(data) {
						jQuery("#limitation").hide();
						jQuery("#limitation").html(data);
						jQuery("#limitation").show();
					}
				);
			});
		}

		function hideLimitations()
		{
			jQuery("#limitation").fadeOut(200, function() {
				jQuery("#limitation").html("");
			});
		}

		jQuery(document).ready(function() {
			tooltipLeft();

			jQuery('input[name="uses"]').bind("click", function() {
				var object = jQuery(this);

				jQuery.post(
					"<?php echo url_for("file/field"); ?>",
					{ id: <?php echo $file->getId(); ?>, field: "use", value: object.val() },
					function(data) {
						object.parent().find(".icon-ok-sign").fadeIn('slow').delay(1000).fadeOut('slow');
					}
				);
			});

			jQuery('input[name="distributions"]').bind("click", function() {
				var object = jQuery(this);

				jQuery.post(
					"<?php echo url_for("file/field"); ?>",
					{ id: <?php echo $file->getId(); ?>, field: "distribution", value: object.val() },
					function(data) {
						object.parent().find(".icon-ok-sign").fadeIn('slow').delay(1000).fadeOut('slow');

						if(object.val() == "<?php echo UsageDistributionPeer::__UNAUTH; ?>" || ("<?php echo $file->getUsageDistributionId(); ?>" == "<?php echo UsageDistributionPeer::__UNAUTH; ?>" && object.val() != "<?php echo UsageDistributionPeer::__UNAUTH; ?>"))
						{
							jQuery("#loading-distribution").dialog({
								title: "<span class='first-title'><?php echo __("Loading..."); ?></span>",
								resizable: false,
								draggable: false,
								modal: true,
								width: "350",
								height: "160",
								show: 'fade',
								hide: 'fade'
							});

							setTimeout(function() {
								window.location.reload();
							}, 1000);
						}
						else if(object.val() == "<?php echo UsageDistributionPeer::__AUTH; ?>")
							showLimitations();
						else
							hideLimitations();
					}
				);
			});
		});
	</script>
<?php endif; ?>