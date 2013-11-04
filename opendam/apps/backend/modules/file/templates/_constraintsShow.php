<?php $constraints = UsageConstraintPeer::getConstraints(); ?>

<?php if($role) : ?>
	<?php $i = 0; ?>
	<?php foreach($constraints as $constraint) : ?>
		<div class="rub" style="width: 100%;">
			<div class="label-right">
				<?php if($i > 0) : ?>
					<span style="visibility: hidden;"><?php echo __("Constraint"); ?> :</span>
				<?php else: ?>
					<?php echo __("Constraint"); ?> :
				<?php endif; ?>
			</div>

			<div class="value-right" style="width: 50%;">
				<input type="radio" name="constraints" id="constraints_<?php echo $constraint->getId(); ?>" value="<?php echo $constraint->getId(); ?>" <?php echo $constraint->getId() == $file->getUsageConstraintId() ? "checked" : ""; ?> class="left" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" /> <label class="left" for="constraints_<?php echo $constraint->getId(); ?>"><?php echo $constraint->getTitle(); ?> <i class="icon-ok-sign" style="display: none;"></i></label>
			</div>
		</div>

		<br clear="all" />
		<?php $i++; ?>
	<?php endforeach; ?>
<?php else: ?>
	<?php $constraint = $file->getUsageConstraintId() ?  $file->getUsageConstraint()->getTitle() : "<span style='cursor: pointer;' class='nc'>".__("To inform")."</span>"; ?>
	<div class="rub" style="width: 100%;">
		<div class="label-right">
			<?php echo __("Constraint"); ?> :
		</div>

		<div class="value-right" style="width: 50%;">
			<?php echo $constraint; ?>
		</div>
	</div>

	<br clear="all" />
<?php endif; ?>

<br clear="all" />

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
			jQuery('input[name="constraints"]').bind("click", function() {
				var object = jQuery(this);

				jQuery.post(
					"<?php echo url_for("file/field"); ?>",
					{ id: <?php echo $file->getId(); ?>, field: "constraint", value: object.val() },
					function(data) {
						object.parent().find(".icon-ok-sign").fadeIn('slow').delay(1000).fadeOut('slow');

						if(object.val() == "<?php echo UsageConstraintPeer::__EXTERNAL; ?>")
							showLimitations();
						else
							hideLimitations();
					}
				);
			});
		});
	</script>
<?php endif; ?>