<?php $fields = $fields->getRawValue(); ?>

<table style="width: 100%;">
	<tr>
		<td class="text" style="background-color: #eee"><?php echo __("Name"); ?></td>
		<td class="text" style="background-color: #eee"><?php echo __("Type"); ?></td>
		<td class="text" style="background-color: #eee"><?php echo __("Values"); ?></td>
		<td class="text" style="background-color: #eee"><?php echo __("Actions"); ?></td>
	</tr>
	<?php if(empty($fields)) : ?>
		<tr>
			<td class="no-border" colspan="4"><div class="info"><?php echo __("No field found.")?></div></td>
		</tr>
	<?php endif; ?>
	<?php foreach($fields as $field) : ?>
		<tr class="admin_tab_border_bottom">
			<td class="no-border text"><div class="eotf" id="<?php echo $field->getId(); ?>" rel="name"><?php echo $field->getName(); ?></div></td>
			<td class="no-border text"><div class="eotf-select" id="<?php echo $field->getId(); ?>" rel="type"><?php echo $field->getStringType(); ?></div></td>
			<td class="no-border text">
				<?php if($field->getType() == FieldPeer::__TYPE_SELECT) : ?>
					<div class="eotf-fake" id="<?php echo $field->getId(); ?>"><?php echo $field->getValues() ? implode("<br />", unserialize(base64_decode($field->getValues()))) : "<span class='nc'>".__("Add values")."</span>"; ?></div>
				<?php else: ?>
					-
				<?php endif; ?>
			</td>
			<td class="no-border text">

					<a href="javascript: void(0);" class="but_admin remove_field" rel="<?php echo $field->getId(); ?>"><span><?php echo __("Remove"); ?></span></a>

			</td>
		</tr>
	<?php endforeach; ?>
</table>
<div id="edit_values"></div>

	<script>
		function bindBorder(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotf').bind('mouseover', overTd);
			jQuery('.eotf').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorder(settings, object)
		{
			jQuery('.eotf').unbind('mouseover');
			jQuery('.eotf').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		function bindBorderSelect(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotf-select').bind('mouseover', overTd);
			jQuery('.eotf-select').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorderSelect(settings, object)
		{
			jQuery('.eotf-select').unbind('mouseover');
			jQuery('.eotf-select').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		function overTd(event)
		{
			jQuery(event.currentTarget).css('border-color', '#E6E6E6'); 
			jQuery(event.currentTarget).css('background-color', '#FAFAFA'); 
		}

		function outTd(event)
		{
			jQuery(event.currentTarget).css('border-color', '#FFFFFF'); 
			jQuery(event.currentTarget).css('background-color', '#FFFFFF'); 
		}
		
		jQuery(document).ready(function() {
			jQuery(".eotf-fake").bind("click", function() {
				objectP = jQuery(this);

				jQuery("#edit_values").dialog({
					title: "<?php echo __("Edit values"); ?>",
					modal: true,
					resizable: false,
					draggable: false,
					show: 'fade',
					hide: 'fade',
					width: 500,
					height: 300,
					/*buttons: {
						"<?php echo __("Validate"); ?>": function() {
							jQuery(this).dialog("close");
						}
					},*/
					open: function(event, ui) {
						var object = jQuery(this);

						jQuery.post(
							"<?php echo url_for("group/loadFieldValue"); ?>",
							{ id: objectP.attr("id") },
							function(data) {
								jQuery(object).fadeOut(200, function() {
									jQuery(object).html(data);
									jQuery(object).show();
									jQuery(object).find(".dialog").fadeIn();
								});
							}
						);
					},
					close: function(event, ui) {
						self2 = jQuery(this);
						var temp = jQuery("#sortable").sortable('toArray');
						var myObject = new Object();

						for(var k = 0; k < temp.length; k++)
							myObject[k] = temp[k];

						jQuery.ajaxSetup({ async:false });

						jQuery.post(
							"<?php echo url_for("group/fieldsOfField"); ?>",
							{ id: objectP.attr("id"), value: jQuery.param(myObject), field: 'values' },
							function(data) {
								objectP.html(data);
								self2.html("");
							}
						);

						jQuery.ajaxSetup({ async:true });
					}
				});
			});

			jQuery(".eotf").editable(
				"<?php echo url_for("group/fieldsOfField"); ?>",
				{
					indicator: '<?php echo __("Saving");?>...',
					placeholder: '',
					cssclass: 'editable',
					onedit: unbindBorder,
					onreset: bindBorder,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						jQuery(this).html(value);
						bindBorder(settings, this);
					}

				}
			);

			jQuery(".eotf-select").editable(
					"<?php echo url_for("group/fieldsOfField"); ?>",
					{
						type: 'select',
						onchange: "submit",
						loadurl : '<?php echo url_for("group/loadFieldType"); ?>',
						indicator: '<div class="eotf-label"><?php echo __("Saving");?>...</div>',
						placeholder: '',
						cssclass: 'editable-details-file select_120',
						onedit: unbindBorderSelect,
						onreset: bindBorderSelect,
						onblur: "submit",
						width: "100%",
						callback : function(value, settings) {
							jQuery(this).html(value);
							bindBorderSelect(settings, this);
						}
					}
				);

			jQuery(".eotf").bind("mouseover", overTd);
			jQuery(".eotf").bind("mouseout", outTd);
			jQuery(".eotf-fake").bind("mouseover", overTd);
			jQuery(".eotf-fake").bind("mouseout", outTd);
			jQuery(".eotf-select").bind("mouseover", overTd);
			jQuery(".eotf-select").bind("mouseout", outTd);

			jQuery(".remove_field").bind("click", function() {
				object = jQuery(this);

				if(confirm("<?php echo __("Are you sur to want to remove this field?"); ?>"))
				{
					jQuery.post(
						"<?php echo url_for("group/removeField"); ?>",
						{ id: object.attr("rel"), group_id: <?php echo $group->getId(); ?> },
						function(data) {
							jQuery("#listFields").fadeOut("slow", function() {
								jQuery("#listFields").html(data.message).fadeIn("slow");
							});
						},
						"json"
					);
				}
			});
		});
	</script>
