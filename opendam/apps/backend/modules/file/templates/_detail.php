<div class="cat-right">
	<a href="javascript: void(0);" class="deploy-cat">
		<i class="icon-info-sign"></i> <?php echo __("Details")?><i class="icon-chevron-down right"></i>
	</a>
	<div class="content" style="display: none;">
		<div class="rub">
			<div class="label-right"><?php echo __("Reference"); ?> : </div> <div class="value-right"><?php echo $file->getId(); ?></div>
		</div>

		<br clear="all" />

		<?php if($file->getName() != $file->getOriginal()) : ?>
			<div class="rub">
				<div class="label-right"><?php echo __("Filename"); ?> : </div> <div class="value-right"><?php echo $file->getOriginal(); ?></div>
			</div>

			<br clear="all" />
		<?php endif; ?>

		<?php
		$retouchType = explode(";",ConfigurationPeer::retrieveByType("_no_retouch_format")->getValue());

		if(!in_array($file->getExtention(), $retouchType)) : ?>
			<div class="rub">
				<div class="label-right"><?php echo __("Dimentions")?> : </div>
				<div class="value-right">
					<?php $demintion = getimagesize(sfConfig::get("app_path_upload_dir")."/".$file->getDisk()->getPath()."/cust-".$file->getCustomerId()."/folder-".$file->getFolderId()."/".$file->getOriginal())?>
					<?php if(!empty($demintion)) : ?>
						<?php echo $demintion[0]." x ".$demintion[1]." px";?>
					<?php else: ?>
						<?php if($file->getWidth() && $file->getHeight()) : ?>
							<?php echo $file->getWidth()." x ".$file->getHeight()." px";?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>

			<br clear="all" />
		<?php endif; ?>

		<div class="rub">
			<div class="label-right"><?php echo __("Format")?> : </div> <div class="value-right"><?php echo strtoupper($file->getExtention());?></div>
		</div>

		<br clear="all" />

		<div class="rub">
			<div class="label-right"><?php echo __("Size")?> : </div> <div class="value-right"><?php echo MyTools::getSize($file->getSize()); ?></div>
		</div>

		<br clear="all" />

		<div class="rub">
			<div class="label-right"><?php echo __("Imported at")?> : </div> <div class="value-right"><?php echo my_format_date_time($file->getCreatedAt())?></div>
		</div>

		<br clear="all" />

		<div class="rub">
			<div class="label-right"><?php echo __("By")?> : </div> <div class="value-right"><?php echo $file->getUser()->getFullname(); ?></div>
		</div>

		<br clear="all" />

		<?php
			if($exif = ExifPeer::getTag("Author", $file->getId()))
				$author = myTools::longword_break_old($exif->getValue(), 22);
			elseif($iptc = IptcPeer::getTag("Writer/Editor", $file->getId()))
				$author = myTools::longword_break_old($iptc->getValue(), 22);
			else
				$author = "<span style='cursor: pointer;'>".__("To inform")."</span>";

			if(!preg_match('/[a-zA-Z0-9]/', $author))
				$author = "<span style='cursor: pointer;'>".__("To inform")."</span>";
		?>

		<?php if($role) : ?>
			<div class="rub">
				<div class='label-right'><?php echo __("Author")?> : </div><div class='eotf left' id='<?php echo $file->getId(); ?>' rel='author'><?php echo $author; ?></div>
			</div>
		<?php else: ?>
			<div class="rub">
				<div class="label-right"><?php echo __("Author")?> : </div> <div class="value-right"><?php echo myTools::longword_break_old($author, 22); ?></div>
			</div>
		<?php endif; ?>

		<br clear="all" />

		<?php $source = $file->getSource() ? $file->getSource() : "<span style='cursor: pointer;'>".__("To inform")."</span>"; ?>

		<?php if($role) : ?>
			<div class="rub">
				<div class='label-right'><?php echo __("Source")?> : </div><div class='eotf left' id='<?php echo $file->getId(); ?>' rel='source'><?php echo $source; ?></div>
			</div>
		<?php else: ?>
			<div class="rub">
				<div class="label-right"><?php echo __("Source")?> : </div> <div class="value-right"><?php echo $source; ?></div>
			</div>
		<?php endif; ?>

		<br clear="all" />

		<?php $fields = FieldPeer::retrieveByGroupId($file->getGroupeId()); ?>

		<?php if(!empty($fields)) : ?>
			<?php foreach($fields as $field) :
				$content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE);

				if((!$role && $content) || $role) : ?>
					<div class="rub">
						<div class='label-right'><?php echo $field->getName(); ?> : </div>

						<?php
							switch($field->getType())
							{
								case FieldPeer::__TYPE_TEXT:
								{ ?>
									<?php if($role) : ?>
										<div class='eotf-field left' id='<?php echo json_encode(Array($file->getId(), $field->getId())); ?>'><?php echo $content ? $content->getValue() : "<span style='cursor: pointer;'>".__("To inform")."</span>"; ?></div>
									<?php else: ?>
										<?php echo $content->getValue(); ?>
									<?php endif; ?>
								<?php }
								break;

								case FieldPeer::__TYPE_BOOLEAN:
								{ ?>
									<?php if($role) : ?>
										<div class='left' style="margin-left: 4px; border: 1px solid transparent;"><input class="boolean_field" style="float: left; margin: 0px;" type="checkbox" name="check_field_<?php echo $field->getId(); ?>" id="check_field_<?php echo $field->getId(); ?>" <?php echo $content ? ($content->getValue() ? "checked" : "") : ""; ?> rel='<?php echo json_encode(Array($file->getId(), $field->getId())); ?>' /><span class="flag_save" style="margin-top: 2px;"><img src="<?php echo image_path("icons/accept.png"); ?>" /></span></div>
									<?php else: ?>
										<?php echo $content->getValue() ? __("Yes") : __("No"); ?>
									<?php endif; ?>
								<?php }
								break;

								case FieldPeer::__TYPE_DATE:
								{ ?>
									<?php if($role) : ?>
										<div class='left' style="margin-left: 4px;"><span id="date_<?php echo $field->getId(); ?>"><?php echo $content ? $content->getValue() : "<span style='cursor: pointer;'>".__("To inform")."</span>"; ?></span><input class="picker_field" type="hidden" name="datepicker_field_<?php echo $field->getId(); ?>" id="datepicker_field_<?php echo $field->getId(); ?>" rel='<?php echo json_encode(Array($file->getId(), $field->getId())); ?>' value="<?php echo $content ? $content->getValue() : ""; ?>" /></div>
									<?php else: ?>
										<?php echo $content->getValue(); ?>
									<?php endif; ?>
								<?php }
								break;

								case FieldPeer::__TYPE_SELECT:
								{
									$values = unserialize(base64_decode($field->getValues())); ?>
									<?php if($role) : ?>
										<div class='eotf-field-select left' id='<?php echo json_encode(Array($file->getId(), $field->getId())); ?>'><?php echo $content ? $content->getValue() : "<span style='cursor: pointer;'>".__("To inform")."</span>"; ?></div>
									<?php else: ?>
										<?php echo in_array($content->getValue(), $values) ? $content->getValue() : "-"; ?>
									<?php endif; ?>
								<?php }
								break;
							}
						?>
					</div>
				<?php endif; ?>

				<br clear="all" />
			<?php endforeach; ?>


				<script>
					function bindBorderField(settings, object)
					{
						jQuery(object).css('border-color', '#FFFFFF'); 
						jQuery(object).css('background-color', '#FFFFFF'); 
						jQuery('.eotf-field').bind('mouseover', overTd);
						jQuery('.eotf-field').bind('mouseout', outTd);
						jQuery(object).css('padding-left', '4px'); 
						jQuery(object).css('padding-right', '4px'); 

						return true;
					}

					function unbindBorderField(settings, object)
					{
						jQuery('.eotf-field').unbind('mouseover');
						jQuery('.eotf-field').unbind('mouseout');  

						jQuery(object).css('padding', '0px');
						jQuery(object).css('padding-left', '4px');

						return true;
					}

					function bindBorderFieldSelect(settings, object)
					{
						jQuery(object).css('border-color', '#FFFFFF'); 
						jQuery(object).css('background-color', '#FFFFFF'); 
						jQuery('.eotf-field-select').bind('mouseover', overTd);
						jQuery('.eotf-field-select').bind('mouseout', outTd);
						jQuery(object).css('padding-left', '4px'); 
						jQuery(object).css('padding-right', '4px'); 

						return true;
					}

					function unbindBorderFieldSelect(settings, object)
					{
						jQuery('.eotf-field-select').unbind('mouseover');
						jQuery('.eotf-field-select').unbind('mouseout');  

						jQuery(object).css('padding', '0px');
						jQuery(object).css('padding-left', '4px');

						return true;
					}

					function overTdField(event)
					{
						jQuery(event.currentTarget).css('border-color', '#E6E6E6'); 
						jQuery(event.currentTarget).css('background-color', '#FAFAFA'); 
					}

					function outTdField(event)
					{
						jQuery(event.currentTarget).css('border-color', '#FFFFFF'); 
						jQuery(event.currentTarget).css('background-color', '#FFFFFF'); 
					}

					jQuery(document).ready(function() {
						jQuery(".boolean_field").bind("click", function() {
							object = jQuery(this);

							jQuery.post(
								"<?php echo url_for("file/valueOfField"); ?>",
								{ id: object.attr("rel"), value: object.is(":checked") },
								function(data) {
									object.parent().find(".flag_save").fadeIn('slow').delay(1000).fadeOut('slow');
								}
							);
						});

						jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);
						jQuery(".picker_field").datepicker({
							showOn: "button",
							buttonImage: "<?php echo image_path("icons/calendar.png"); ?>",
							buttonImageOnly: true,
							timeText: '<?php echo __("Time"); ?>',
							hourText: '<?php echo __("Hour"); ?>',
							minuteText: '<?php echo __("Minute"); ?>',
							secondText: '<?php echo __("Second"); ?>',
							closeText: '<?php echo __("Save"); ?>',
							dateFormat: 'dd/mm/yy',
							firstDay: 1,
							gotoCurrent: true,
							showButtonPanel: false,
							onClose: function(dateText, inst) {
								object = jQuery(this);
								temp = JSON.parse(object.attr("rel"));

								if(dateText != "")
								{
									jQuery.post(
										"<?php echo url_for("file/valueOfField"); ?>",
										{ id: object.attr("rel"), value: dateText },
										function(data) {
											jQuery("#date_" + temp[1]).html(dateText);
										}
									);
								}
							}
						});

						jQuery(".eotf-field").editable(
							"<?php echo url_for("file/valueOfField"); ?>",
							{
								indicator: '<?php echo __("Saving");?>...',
								placeholder: '',
								cssclass: 'editable-details-file',
								onedit: unbindBorderField,
								onreset: bindBorderField,
								onblur: "submit",
								width: "100%",
								callback : function(value, settings) {
									jQuery(this).html(value);
									bindBorderField(settings, this);
								},
								data: function(value, settings) {
									var regexp = new RegExp("(<?php echo strtolower(__("To inform")); ?>)","g");

									if(regexp.test(value.toLowerCase()))
											return "";

									return value;
								}
							}
						);

						
						jQuery(".eotf-field-select").editable(
							"<?php echo url_for("file/valueOfField"); ?>",
							{
								type: 'select',
								onchange: "submit",
								loadurl : '<?php echo url_for("file/loadFieldValue"); ?>',
								indicator: '<?php echo __("Saving");?>...',
								placeholder: '',
								cssclass: 'editable-details-file select_110',
								onedit: unbindBorderFieldSelect,
								onreset: bindBorderFieldSelect,
								onblur: "submit",
								width: "100%",
								callback : function(value, settings) {
									jQuery(this).html(value);
									bindBorderFieldSelect(settings, this);
								},
								data: function(value, settings) {
									var regexp = new RegExp("(<?php echo strtolower(__("To inform")); ?>)","g");

									if(regexp.test(value.toLowerCase()))
											return "";

									return value;
								}
							}
						);

						jQuery(".eotf-field").bind("mouseover", overTdField);
						jQuery(".eotf-field").bind("mouseout", outTdField);
						jQuery(".eotf-field-select").bind("mouseover", overTdField);
						jQuery(".eotf-field-select").bind("mouseout", outTdField);
					});
				</script>
			<?php endif; ?>


		<?php $view = FilePeer::getView($file->getId()); ?>

		<?php if(!empty($view)) : ?>
			<br clear="all">
			<div class="rub">
				<div class="value-right">
					<?php if($view > 1) : ?>
						<span class='viewFile'><?php echo $view; ?></span> <?php echo __("views"); ?>
					<?php else: ?>
						<span class='viewFile'><?php echo $view; ?></span> <?php echo __("view"); ?>
					<?php endif; ?>
				</div>
			</div>

			<br clear="all">
		<?php endif; ?>
	</div>
</div>