<?php $commercial = $file->getUsageCommercialId() ?  $file->getUsageCommercial()->getTitle() : "<span style='cursor: pointer;' class='nc'>".__("To inform")."</span>"; ?>

<?php if($role) : ?>
	<br clear="all" style="margin-top: 7px;" />
	<div class='left eotf-label'><b><?php echo __("Commercial")?> : </b></div><div class='eotf-select-commercial left' id='<?php echo $file->getId(); ?>' rel='commercial'><?php echo $commercial; ?></div>
<?php else: ?>
	<b style="line-height: 18px;"><?php echo __("Commercial")?> : </b> <?php echo $commercial; ?>
<?php endif; ?>

<?php if($role) : ?>
	<script>
		function bindBorderSelectCommercial(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotf-select-commercial').bind('mouseover', overTd);
			jQuery('.eotf-select-commercial').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorderSelectCommercial(settings, object)
		{
			jQuery('.eotf-select-commercial').unbind('mouseover');
			jQuery('.eotf-select-commercial').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		jQuery(document).ready(function() {
			jQuery(".eotf-select-commercial").editable(
				"<?php echo url_for("file/field"); ?>",
				{
					type: 'select',
					onchange: "submit",
					loadurl : '<?php echo url_for("file/loadCommercial"); ?>',
					indicator: '<div class="eotf-label"><?php echo __("Saving");?>...</div>',
					placeholder: '',
					cssclass: 'editable-details-file select_110',
					onedit: unbindBorderSelectCommercial,
					onreset: bindBorderSelectCommercial,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						jQuery(this).html(value);
						bindBorderSelectCommercial(settings, this);
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

			jQuery(".eotf-select-commercial").bind("mouseover", overTd);
			jQuery(".eotf-select-commercial").bind("mouseout", outTd);
		});
	</script>
<?php endif; ?>