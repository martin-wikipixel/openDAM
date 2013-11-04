<?php $constraint = $file->getUsageConstraintId() ?  $file->getUsageConstraint()->getTitle() : "<span style='cursor: pointer;' class='nc'>".__("To inform")."</span>"; ?>

<?php if($role) : ?>
	<br clear="all" style="margin-top: 7px;" />
	<div class='left eotf-label'><b><?php echo __("Constraint")?> : </b></div><div class='eotf-select-constraint left' id='<?php echo $file->getId(); ?>' rel='constraint'><?php echo $constraint; ?></div>
<?php else: ?>
	<b style="line-height: 18px;"><?php echo __("Constraint")?> : </b> <?php echo $constraint; ?>
<?php endif; ?>

<?php if($role) : ?>
	<script>
		function showLimitations()
		{
			jQuery("#limitation").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");
			jQuery("#limitation").fadeIn(200, function() {
				jQuery.post(
					"<?php echo url_for("file/showLimitations"); ?>",
					{ file_id: <?php echo $file->getId(); ?>, role: <?php echo $role; ?> },
					function(data) {
						// jQuery("#limitation").fadeOut(200, function() {
							// jQuery("#limitation").html(data);
							// jQuery("#limitation").fadeIn();
						// });
						jQuery("#limitation").hide();
						// jQuery("#limitation").html("<div style='width: 100%; border: 1px solid red;'>hello world</div>");
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

		function bindBorderSelectConstraint(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotf-select-constraint').bind('mouseover', overTd);
			jQuery('.eotf-select-constraint').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorderSelectConstraint(settings, object)
		{
			jQuery('.eotf-select-constraint').unbind('mouseover');
			jQuery('.eotf-select-constraint').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		jQuery(document).ready(function() {
			jQuery(".eotf-select-constraint").editable(
				"<?php echo url_for("file/field"); ?>",
				{
					type: 'select',
					onchange: "submit",
					loadurl : '<?php echo url_for("file/loadConstraint"); ?>',
					indicator: '<div class="eotf-label"><?php echo __("Saving");?>...</div>',
					placeholder: '',
					cssclass: 'editable-details-file select_110',
					onedit: unbindBorderSelectConstraint,
					onreset: bindBorderSelectConstraint,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						var obj = jQuery.parseJSON(value);

						jQuery(this).html(obj.title);
						bindBorderSelectConstraint(settings, this);

						if(obj.id == "<?php echo UsageConstraintPeer::__EXTERNAL; ?>")
							showLimitations();
						else
							hideLimitations();
					},
					data: function(value, settings) {
						var regexp = new RegExp("(<?php echo strtolower(__("To inform")); ?>)","g");

						if(regexp.test(value.toLowerCase()))
								return "";

						return value;
					},
					onsubmit: function(settings, original, eventType) {
						if(jQuery(original).find('select[name="value"]').val() > 0 || (eventType == 'blur' && jQuery(original).find('select[name="value"]').val() <= 0))
							return true;

						return false;
					}
				}
			);

			jQuery(".eotf-select-constraint").bind("mouseover", overTd);
			jQuery(".eotf-select-constraint").bind("mouseout", outTd);
		});
	</script>
<?php endif; ?>