<?php $usage = $file->getUsageUseId() ? $file->getUsageUse()->getTitle() : "<span style='cursor: pointer;' class='nc'>".__("To inform")."</span>"; ?>

<?php if($role) : ?>
	<div class='left eotf-label'><b><?php echo __("Use")?> : </b></div><div class='eotf-select-use left' id='<?php echo $file->getId(); ?>' rel='use'><?php echo $usage; ?></div>
<?php else: ?>
	<b style="line-height: 18px;"><?php echo __("Use")?> : </b> <?php echo $usage; ?>
<?php endif; ?>

<div id="commercial" <?php echo $file->getUsageUseId() != UsageUsePeer::__COMMERCIAL ? "style='display: none;'" : ""; ?>>
	<?php include_partial("file/commercials", array("file" => $file, "role" => $role)); ?>
</div>

<?php $distribution = $file->getUsageDistributionId() ?  $file->getUsageDistribution()->getTitle() : "<span style='cursor: pointer;' class='nc'>".__("To inform")."</span>"; ?>

<?php if($role) : ?>
	<br clear="all" style="margin-top: 7px;" />
	<div class='left eotf-label'><b><?php echo __("Distribution")?> : </b></div><div class='eotf-select-distribution left <?php echo $file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH ? "require_field" : ""; ?>' id='<?php echo $file->getId(); ?>' rel='distribution'><?php echo $distribution; ?></div>
<?php else: ?>
	<b style="line-height: 18px;"><?php echo __("Distribution")?> : </b> <?php echo $distribution; ?>
<?php endif; ?>

<div id="constraint" <?php echo $file->getUsageDistributionId() != UsageDistributionPeer::__LIMITED ? "style='display: none;'" : ""; ?>>
	<?php include_partial("file/constraints", array("file" => $file, "role" => $role)); ?>
</div>

<div id="limitation" <?php echo $file->getUsageConstraintId() != UsageConstraintPeer::__EXTERNAL ? "style='display: none;'" : ""; ?>>
	<?php include_partial("file/limitations", array("file" => $file, "role" => $role)); ?>
</div>

<?php if($role) : ?>
	<script>
		function bindBorderSelectDistribution(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotf-select-distribution').bind('mouseover', overTd);
			jQuery('.eotf-select-distribution').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorderSelectDistribution(settings, object)
		{
			jQuery('.eotf-select-distribution').unbind('mouseover');
			jQuery('.eotf-select-distribution').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		function showConstraints()
		{
			jQuery("#constraint").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");
			jQuery("#constraint").fadeIn(200, function() {
				jQuery.post(
					"<?php echo url_for("file/showConstraints"); ?>",
					{ file_id: <?php echo $file->getId(); ?>, role: <?php echo $role; ?> },
					function(data) {
						jQuery("#constraint").fadeOut(200, function() {
							jQuery("#constraint").html(data);
							jQuery("#constraint").fadeIn();
						});
					}
				);
			});
		}

		function hideConstraints()
		{
			jQuery("#constraint").fadeOut(200, function() {
				jQuery("#constraint").html("");
				hideLimitations();
			});
		}

		function bindBorderSelectUse(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotf-select-use').bind('mouseover', overTd);
			jQuery('.eotf-select-use').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorderSelectUse(settings, object)
		{
			jQuery('.eotf-select-use').unbind('mouseover');
			jQuery('.eotf-select-use').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		function showCommercials()
		{
			jQuery("#commercial").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");
			jQuery("#commercial").fadeIn(200, function() {
				jQuery.post(
					"<?php echo url_for("file/showCommercials"); ?>",
					{ file_id: <?php echo $file->getId(); ?>, role: <?php echo $role; ?> },
					function(data) {
						jQuery("#commercial").fadeOut(200, function() {
							jQuery("#commercial").html(data);
							jQuery("#commercial").fadeIn();
						});
					}
				);
			});
		}

		function hideCommercials()
		{
			jQuery("#commercial").fadeOut(200, function() {
				jQuery("#commercial").html("");
			});
		}

		jQuery(document).ready(function() {
			jQuery(".eotf-select-use").editable(
				"<?php echo url_for("file/field"); ?>",
				{
					type: 'select',
					onchange: "submit",
					loadurl : '<?php echo url_for("file/loadUse"); ?>',
					indicator: '<div class="eotf-label"><?php echo __("Saving");?>...</div>',
					placeholder: '',
					cssclass: 'editable-details-file select_120',
					onedit: unbindBorderSelectUse,
					onreset: bindBorderSelectUse,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						var obj = jQuery.parseJSON(value);

						jQuery(this).html(obj.title);
						bindBorderSelectUse(settings, this);

						if(obj.id == "<?php echo UsageUsePeer::__COMMERCIAL; ?>")
							showCommercials();
						else
							hideCommercials();
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

			jQuery(".eotf-select-distribution").editable(
				"<?php echo url_for("file/field"); ?>",
				{
					type: 'select',
					onchange: "submit",
					loadurl : '<?php echo url_for("file/loadDistribution"); ?>',
					indicator: '<div class="eotf-label"><?php echo __("Saving");?>...</div>',
					placeholder: '',
					cssclass: 'editable-details-file select_120',
					onedit: unbindBorderSelectDistribution,
					onreset: bindBorderSelectDistribution,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						var obj = jQuery.parseJSON(value);

						jQuery(this).html(obj.title);
						bindBorderSelectDistribution(settings, this);

						if(obj.id == "<?php echo UsageDistributionPeer::__UNAUTH; ?>" || ("<?php echo $file->getUsageDistributionId(); ?>" == "<?php echo UsageDistributionPeer::__UNAUTH; ?>" && obj.id != "<?php echo UsageDistributionPeer::__UNAUTH; ?>"))
							window.location.reload();
						else if(obj.id == "<?php echo UsageDistributionPeer::__LIMITED; ?>")
						{
							jQuery(this).removeClass("require_field");
							showConstraints();
						}
						else
						{
							jQuery(this).removeClass("require_field");
							hideConstraints();
						}
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

			jQuery(".eotf-select-distribution").bind("mouseover", overTd);
			jQuery(".eotf-select-use").bind("mouseover", overTd);
			jQuery(".eotf-select-distribution").bind("mouseout", outTd);
			jQuery(".eotf-select-use").bind("mouseout", outTd);
		});
	</script>
<?php endif; ?>