<?php $limitations = UsageLimitationPeer::getLimitations(); ?>
<?php $presets = $presets->getRawValue(); ?>
<?php $files_array = $files_array->getRawValue(); ?>
<?php $count = count($files_array); ?>

<?php 
if($sf_params->get("navigation") == "create")
	include_partial("folder/navigationCreate", array("selected"=>"edit", "folder"=>$folder));
elseif($sf_params->get("navigation") == "upload")
	include_partial("upload/navigation", array("selected"=>"edit", "folder_id"=>$folder->getId(), "group_id"=>$folder->getGroupeId()));
else
	include_partial("file/navigationManage", array("selected"=>"edit", "folder"=>$folder, "file_ids"=>$files_array)); ?>

<div id="searchResults-popup">
	<div class="inner" id="container">
		<?php foreach($files_array as $file_id): ?>
			<?php $file = FilePeer::retrieveByPk($file_id); ?>

			<form name='editSelected_form_<?php echo $file->getId(); ?>' id='editSelected_form_<?php echo $file->getId(); ?>' class='form'>
				<input type="hidden" name="file_id" id="file_id_<?php echo $file->getId(); ?>" value="<?php echo $file->getId(); ?>" />
				<input type="hidden" name="folder_id" id="folder_id_<?php echo $file->getId(); ?>" value="<?php echo $folder->getId(); ?>" />
				<input type="hidden" name="navigation" id="navigation_<?php echo $file->getId(); ?>" value="<?php echo $sf_params->get("navigation"); ?>" />

				<div class="file_edit">
					<div class="container">
						<div class="thumbnail">
							<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "200")); ?>" />

							<div class="sub_thumb">
								<div class="eotf" id="<?php echo $file->getId(); ?>" rel="name" style="font-weight: bold;"><?php echo $file->getName() ? myTools::longword_break_old($file->getName(), 25) : myTools::longword_break_old($file, 25); ?></div>
								<input type="hidden" name="name_<?php echo $file->getId(); ?>" id="name_<?php echo $file->getId(); ?>" value="<?php echo $file->getName() ? $file->getName() : $file; ?>" />
								<input type="hidden" name="tags_input_<?php echo $file->getId(); ?>" id="tags_input_<?php echo $file->getId(); ?>" />
							</div>
						</div>
						
						<div class="left div_inputs">
							<label style="width: 140px;" for="description_<?php echo $file->getId(); ?>"><?php echo __("Description"); ?></label>
							<textarea name="description_<?php echo $file->getId(); ?>" id="description_<?php echo $file->getId(); ?>" class="left" style="width: 370px;"><?php echo $file->getDescription(); ?></textarea>

							<?php if($count > 1) : ?>
								<div class="right apply">
									<input type="checkbox" name="apply_description_<?php echo $file->getId(); ?>" id="apply_description_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="left apply_description" style="margin-right: 5px; margin-top: 0px;" />
									<label for="apply_description_<?php echo $file->getId(); ?>" style="margin-top: 0px;"><?php echo __("Apply everywhere"); ?></label>
								</div>
							<?php endif; ?>

							<br clear="all" />

							<label style="width: 140px;"><?php echo __("Tags"); ?></label>
							<input type="text" onblur="onTagBlur(this);" onfocus="onTagFocus(this);" value="<?php echo __("Add tag ..."); ?>" id="tag_title_<?php echo $file->getId(); ?>" name="tag_title_<?php echo $file->getId(); ?>" class="nc left" style="width: 350px;" />
							<a href='#' id='addTagRemote_<?php echo $file->getId(); ?>' class="left" style="margin-left: 5px; margin-top: 10px;"><?php echo image_tag("icons/add4Bis.gif", array("align"=>"absmiddle")); ?></a>

							<?php if($count > 1) : ?>
								<div class="right apply">
									<input type="checkbox" name="apply_tags_<?php echo $file->getId(); ?>" id="apply_tags_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="left apply_tags" style="margin-right: 5px; margin-top: 0px;" />
									<label for="apply_tags_<?php echo $file->getId(); ?>" style="margin-top: 0px;"><?php echo __("Apply everywhere"); ?></label>
								</div>
							<?php endif; ?>

							<br clear="all" />

							<label></label>
							<div id="file_tags_<?php echo $file->getId(); ?>" class="left optionsButton" style="width: 398px;">
								<?php include_partial("tag/selectedTagsFiles", array("file"=>$file));?>
							</div>

							<br clear="all" />

							<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)) : ?>
								<div class="filterBox">
									<div class="title thesaurus_title" style="cursor:pointer;" rel="<?php echo $file->getId(); ?>">
										<img src='<?php echo image_path("right-arr.gif"); ?>' id='thesaurus_img_<?php echo $file->getId(); ?>' style='vertical-align: middle;' />
										<h4><?php echo __("Suggested tags (thesaurus)")?></h4>
									</div>

									<div id="thesaurus_<?php echo $file->getId(); ?>" style="display: none;">
										<ul class="thesaurus_tree" id="tree_thesaurus_<?php echo $file->getId(); ?>" style="padding: 5px; border: 1px solid #E6E6E6; min-height: 100px; max-height: 300px; overflow: auto;"></ul>
									</div>
								</div>

								<br clear="all" />
							<?php endif; ?>

							<div class="filterBox">
								<div class="title advanced_info" style="cursor:pointer;" rel="<?php echo $file->getId(); ?>">
									<img src='<?php echo image_path("right-arr.gif"); ?>' id='advanced_container_img_<?php echo $file->getId(); ?>' style='vertical-align: middle;' />
									<h4><?php echo __("Advanced information")?></h4>
								</div>

								<div id="advanced_<?php echo $file->getId(); ?>" style="display: none;">
									<div id="filterByInformation" class="text description-show details-file">
										<?php if($sf_user->isAdmin() || (UserGroupPeer::getRole($sf_user->getId(), $file->getGroupeId()) == RolePeer::__ADMIN) || ($file->getUserId() == $sf_user->getId())): ?>
											<label for="author" style="width: 140px;"><?php echo __("Author")?></label>
											<?php if($exif = ExifPeer::getTag("Author", $file->getId()))
											{
												$author = $exif->getValue();
												$class = "";
											}
											elseif($iptc = IptcPeer::getTag("Writer/Editor", $file->getId()))
											{
												$author = $iptc->getValue();
												$class = "";
											}
											else
											{
												$author = __("To inform");
												$class = "nc";
											}

											if(!preg_match('/[a-zA-Z0-9]/', $author))
											{
												$author = __("To inform");
												$class = "nc";
											} ?>

											<input type="text" style="width: 370px; float: left;" class="<?php echo $class; ?>" value="<?php echo $author; ?>" id="author_<?php echo $file->getId(); ?>" name="author_<?php echo $file->getId(); ?>">

											<?php if($count > 1) : ?>
												<div class="right apply">
													<input type="checkbox" name="apply_author_<?php echo $file->getId(); ?>" id="apply_author_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="left apply_author" style="margin-right: 5px; margin-top: 3px;" />
													<label for="apply_author_<?php echo $file->getId(); ?>" style="margin-top: 0px;"><?php echo __("Apply everywhere"); ?></label>
												</div>
											<?php endif; ?>

											<br clear="all">

											<label for="created_at" style="width: 140px;"><?php echo __("Shooting date")?></label>
											<div class="inputs">
												<?php 
												if($file->getType() == FilePeer::__TYPE_PHOTO)
												{
													$pattern = "/^\d{2}\/\d{2}\/\d{4}\s\d{2}\:\d{2}\:\d{2}$/"; /* DD/MM/YYYY HH:MM:SS */
													$pattern1 = "/^\d{2}\/\d{1}\/\d{4}\s\d{2}\:\d{2}\:\d{2}$/"; /* D/MM/YYYY HH:MM:SS */
													$pattern2 = "/^\d{2}\/\d{1}\/\d{4}\s\d{2}\:\d{2}\:\d{2}$/"; /* DD/M/YYYY HH:MM:SS */
													$pattern3 = "/^\d{2}\/\d{1}\/\d{4}\s\d{2}\:\d{2}\:\d{2}$/"; /* D/M/YYYY HH:MM:SS */

													$pattern4 = "/^\d{4}-\d{2}-\d{2}\s\d{2}\:\d{2}\:\d{2}$/"; /* YYYY-MM-DD HH:MM:SS */
													$pattern5 = "/^\d{4}-\d{1}-\d{2}\s\d{2}\:\d{2}\:\d{2}$/"; /* YYYY-MM-D HH:MM:SS */
													$pattern6 = "/^\d{4}-\d{1}-\d{2}\s\d{2}\:\d{2}\:\d{2}$/"; /* YYYY-M-DD HH:MM:SS */
													$pattern7 = "/^\d{4}-\d{1}-\d{2}\s\d{2}\:\d{2}\:\d{2}$/"; /* YYYY-M-D HH:MM:SS */
													

													if($exif = ExifPeer::getTag("DateTimeOriginal", $file->getId()))
														$created_at = $exif->getValue();
													elseif($iptc = IptcPeer::getTag("Date Created", $file->getId()))
														$created_at = $iptc->getValue();
													else
														$created_at = "0000-00-00 00:00:00";
												}

												if($file->getType() == FilePeer::__TYPE_VIDEO)
												{
													$created_at = "0000-00-00 00:00:00";
												}

												if($created_at != "0000-00-00 00:00:00")
												{
													if(preg_match($pattern, $created_at) || preg_match($pattern1, $created_at) || preg_match($pattern2, $created_at) || preg_match($pattern3, $created_at))
													{
														$temp = explode(" ", $created_at);
														$date = explode("/", $temp[0]);
														$hour = explode(":", $temp[1]);

														$created_at = mktime($hour[0], $hour[1], $hour[2], $date[1], $date[0], $date[2]);
													}
													elseif(preg_match($pattern4, $created_at) || preg_match($pattern5, $created_at) || preg_match($pattern5, $created_at) || preg_match($pattern7, $created_at))
													{
														$temp = explode(" ", $created_at);
														$date = explode("-", $temp[0]);
														$hour = explode(":", $temp[1]);

														$created_at = mktime($hour[0], $hour[1], $hour[2], $date[1], $date[2], $date[0]);
													}
												}
												else
													$created_at = 0; ?>

												<?php echo "<span class=\"ie6cor_select left\" style=\"width: 378px;\">".input_date_tag('created_at_'.$file->getId(), $created_at, "include_custom=--", array("style"=>"float:left; width: 250px;", "culture"=>"fr_FR", "year_start"=>1995, "year_end"=>2020)); ?>
												&nbsp;&nbsp;&nbsp;
												<?php $hour = $minute = $second = array();
														for($i = 0; $i < 24; $i++)
															$hour[(($i < 10) ? '0'.$i : $i)] = (($i < 10) ? '0'.$i : $i);
														for($i = 0; $i < 61; $i++) {
															$minute[(($i < 10) ? '0'.$i : $i)] = (($i < 10) ? '0'.$i : $i);
															$second[(($i < 10) ? '0'.$i : $i)] = (($i < 10) ? '0'.$i : $i);
														} ?>
												<?php echo select_tag('created_at_'.$file->getId().'_hour', options_for_select($hour, empty($created_at) ? "00" : date('H', $created_at))); ?>:<?php echo select_tag('created_at_'.$file->getId().'_minute', options_for_select($minute, empty($created_at) ? "00" : date('i', $created_at))); ?>:<?php echo select_tag('created_at_'.$file->getId().'_second', options_for_select($second, empty($created_at) ? "00" : date('s', $created_at))); ?>
												</span>
											</div>

											<?php if($count > 1) : ?>
												<div class="right apply" style="margin-left: 0px;">
													<input type="checkbox" name="apply_created_at_<?php echo $file->getId(); ?>" id="apply_created_at_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="left apply_created_at" style="margin-right: 5px; margin-top: 3px;" />
													<label for="apply_created_at_<?php echo $file->getId(); ?>" style="margin-top: 0px;"><?php echo __("Apply everywhere"); ?></label>
												</div>
											<?php endif; ?>

											<br clear="all">

											<label style="width: 140px;"><?php echo __("Presets")?></label>
											<select id="preset_<?php echo $file->getId(); ?>" name="preset_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="preset" style="width: 370px; float: left;">
												<option value="0"><?php echo __("Choose"); ?></option>
												<?php foreach($presets as $preset) : ?>
													<option value="<?php echo $preset->getId(); ?>"><?php echo $preset->getName(); ?></option>
												<?php endforeach; ?>
											</select>

											<br clear="all">

											<label style="width: 140px;"><?php echo __("Usage rights")?></label>
											<div class="left" id="div_copyright_<?php echo $file->getId(); ?>">
												<?php echo include_component("file", "copyrightSelected", Array("file" => $file, "preset" => null)); ?>
											</div>

											<?php if($count > 1) : ?>
												<div class="right apply">
													<input type="checkbox" name="apply_usage_right_<?php echo $file->getId(); ?>" id="apply_usage_right_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="left apply_usage_right" style="margin-right: 5px; margin-top: 3px;" />
													<label for="apply_usage_right_<?php echo $file->getId(); ?>" style="margin-top: 0px;"><?php echo __("Apply everywhere"); ?></label>
												</div>
											<?php endif; ?>

											<?php $fields = FieldPeer::retrieveByGroupId($file->getGroupeId()); ?>

											<?php if(!empty($fields)) : ?>
												<br clear="all" />

												<label style="width: 140px;"><?php echo __("Specific field")?></label>
												<div id="fields_folder" style="margin-top: 0px;">
													<?php foreach($fields as $field) :
														$content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE); ?>

														<b style="line-height: 18px; font-weight: bold;" class="text"><?php echo $field->getName(); ?> : </b>

														<?php
															switch($field->getType())
															{
																case FieldPeer::__TYPE_TEXT:
																{ ?>
																	<input type="text" name="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" id="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" value="<?php echo $content ? $content->getValue() : ""; ?>" style="width: 200px;" />
																<?php }
																break;

																case FieldPeer::__TYPE_BOOLEAN:
																{ ?>
																	<input type="checkbox" name="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" id="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" <?php echo $content ? ($content->getValue() ? "checked" : "") : ""; ?> />
																<?php }
																break;

																case FieldPeer::__TYPE_DATE:
																{ ?>
																	<span id="date_field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" class="text"><?php echo $content ? $content->getValue() : "<span class='nc' style='font-size: 11px;'>".__("To inform")."</span>"; ?></span><input type="hidden" name="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" id="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" class="picker_field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" rel="<?php echo $field->getId(); ?>" value="<?php echo $content ? $content->getValue() : ""; ?>" />
																<?php }
																break;

																case FieldPeer::__TYPE_SELECT:
																{
																	$values = unserialize(base64_decode($field->getValues())); ?>
																	<select name="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>" id="field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>">
																		<option value="0"><?php echo __("Choose"); ?></option>
																		<?php foreach($values as $value) : ?>
																			<option value="<?php echo $value; ?>" <?php echo $content && $content->getValue() == $value ? "selected" : ""; ?>><?php echo $value; ?></option>
																		<?php endforeach; ?>
																	</select>
																<?php }
																break;
															}
														?>

														<script>
															jQuery(document).ready(function(){
																jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);
																jQuery(".picker_field_<?php echo $field->getId(); ?>_<?php echo $file->getId(); ?>").datepicker({
																	showOn: "button",
																	buttonImage: "<?php echo image_path("calendar.gif"); ?>",
																	buttonImageOnly: true,
																	timeText: '<?php echo __("Time"); ?>',
																	hourText: '<?php echo __("Hour"); ?>',
																	minuteText: '<?php echo __("Minute"); ?>',
																	secondText: '<?php echo __("Second"); ?>',
																	closeText: '<?php echo __("Save"); ?>',
																	buttonText: '<?php echo __("Date"); ?>',
																	dateFormat: 'dd/mm/yy',
																	firstDay: 1,
																	gotoCurrent: true,
																	showButtonPanel: false,
																	onClose: function(dateText, inst) {
																		self = jQuery(this);

																		if(dateText != "")
																			jQuery("#date_field_" + self.attr("rel") + "_<?php echo $file->getId(); ?>").html(dateText);
																	}
																});
															});
														</script>
														<br clear="all" />
													<?php endforeach; ?>
												</div>

												<?php if($count > 1) : ?>
													<div class="right apply">
														<input type="checkbox" name="apply_fields_<?php echo $file->getId(); ?>" id="apply_fields_<?php echo $file->getId(); ?>" rel="<?php echo $file->getId(); ?>" class="left apply_fields" style="margin-right: 5px; margin-top: 3px;" />
														<label for="apply_fields_<?php echo $file->getId(); ?>" style="margin-top: 0px;"><?php echo __("Apply everywhere"); ?></label>
													</div>
												<?php endif; ?>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		<?php endforeach; ?>


		<br clear="all" />
		<br clear="all" />

		<div id="selected_files">
			<div class="left" id="loading">
				<div>
					<img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" /><?php echo __("File processing")." "; ?><span id='current_file'>1</span> / <span id='max_file'></span>
				</div>
			</div>
		</div>

		<div class="right">
			<?php if($sf_params->get("navigation") == "upload") : ?>
				<a href="#" id="submitButton" onclick="submit();" class="button btnBS"><span><?php echo __("SAVE")?></span></a>
			<?php else: ?>
				<a href="#" onclick="window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
				<a href="#" id="submitButton" onclick="submit();" class="button btnBS"><span><?php echo __("SAVE")?></span></a>
			<?php endif; ?>
		</div>

	</div>
</div>
<script>
var forms = "";

function submit()
{
	forms = jQuery('form');

	jQuery("#max_file").html(forms.length);

	jQuery("#submitButton").fadeOut('slow', function() {
		jQuery("#loading div").fadeIn('slow', function() {
			proceedSubmit(0);
		});
	});
}

function proceedSubmit(i)
{
	if(i < forms.length)
	{
		jQuery(forms[i]).find(':disabled').each(function() {
			jQuery(this).attr("disabled", false);
		});

		jQuery("#current_file").html(i + 1);

		jQuery.post(
			"<?php echo url_for("file/saveSelected"); ?>",
			jQuery(forms[i]).serialize(),
			function(data) {
				proceedSubmit(i + 1);
			}
		);
	}
	else
	{
		<?php if($sf_params->get("navigation") == "upload") : ?>
			window.parent.closeFacebox();
			window.parent.location='<?php echo url_for('folder/show?id='.$folder->getId().'&sortTemp=date_desc', true); ?>';
		<?php else: ?>
			window.location.reload();
		<?php endif; ?>
	}
}

function checkLimitation(obj, limitation_id, file_id)
{
	if(jQuery(obj).is(":checked") == true)
	{
		jQuery("#show_limitation_" + limitation_id + "_" + file_id).attr("disabled", false);
		jQuery("#show_limitation_" + limitation_id + "_" + file_id).removeClass("disabled");
	}
	else
	{
		jQuery("#show_limitation_" + limitation_id + "_" + file_id).attr("disabled", true);
		jQuery("#show_limitation_" + limitation_id + "_" + file_id).addClass("disabled");
		jQuery("#show_limitation_" + limitation_id + "_" + file_id).removeClass("expired");
		jQuery("#show_limitation_" + limitation_id + "_" + file_id).val("");
		jQuery("#limitation_" + limitation_id + "_" + file_id).val("");
	}
}

function deleteTag(object, file_id)
{
	var title = jQuery(object).find("span").html();
	var tags = jQuery("#tags_input_" + file_id).val().split("|");
	var tmp = "";

	for(var i = 0; i < tags.length; i++)
	{
		if(tags[i] != title && tags[i] != "")
			tmp += tags[i] + "|";
	}

	jQuery("#tags_input_" + file_id).val(tmp);
	jQuery(object).remove();
}

function onTagBlur(object)
{
	if(jQuery(object).val() == "")
	{
		jQuery(object).val("<?php echo __("Add tag ..."); ?>");
		jQuery(object).addClass("nc");
	}
}

function onTagFocus(object)
{
	if(jQuery(object).val() == "<?php echo __("Add tag ..."); ?>")
	{
		jQuery(object).val("");
		jQuery(object).removeClass("nc");
	}
}

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
	jQuery(".label_limitation").live({
		mouseenter: function () {
			if(jQuery(this).parent().find("input[type=checkbox]").attr("disabled") != true)
			{
				jQuery(this).find("a.edit-limitation").fadeIn();
				jQuery(this).find("img.ui-datepicker-trigger").fadeIn();
			}
		},
		mouseleave: function () {
			if(jQuery(this).parent().find("input[type=checkbox]").attr("disabled") != true)
			{
				jQuery(this).find("a.edit-limitation").fadeOut();
				jQuery(this).find("img.ui-datepicker-trigger").fadeOut();
			}
		}
	});

	jQuery(".preset").bind("change", function() {
		var presetId = jQuery(this).val();
		var fileId = jQuery(this).attr("rel");

		jQuery.post(
			"<?php echo url_for("file/loadPresetSelected"); ?>",
			{ file_id: fileId, id: presetId },
			function(data) {
				jQuery("#div_copyright_" + fileId).html(data);
			}
		);
	});

	jQuery(".licence").live("change", function() {
		if(jQuery(this).val() == "<?php echo LicencePeer::__CREATIVE_COMMONS; ?>")
			jQuery("#div_creative_commons_" + jQuery(this).attr("rel")).fadeIn();
		else
			jQuery("#div_creative_commons_" + jQuery(this).attr("rel")).fadeOut();
	});

	jQuery(".distribution").live("change", function() {
		if(jQuery(this).val() == "<?php echo UsageDistributionPeer::__AUTH; ?>")
			jQuery("#div_limitation_" + jQuery(this).attr("rel")).fadeIn();
		else
			jQuery("#div_limitation_" + jQuery(this).attr("rel")).fadeOut();
	});

	<?php foreach($files_array as $file_id): ?>
		jQuery("#creative_commons_img_<?php echo $file_id; ?>").live({
			mouseenter: function () {
				jQuery(this).find("a.edit-limitation").fadeIn();
			}, 
			mouseleave: function () {
				jQuery(this).find("a.edit-limitation").fadeOut();
			}
		});

		jQuery("#creative_commons_select_<?php echo $file_id; ?>").live("change", function() {
			jQuery(this).trigger("blur");
		});

		jQuery("#creative_commons_select_<?php echo $file_id; ?>").live("blur", function() {
			jQuery.post(
				"<?php echo url_for("right/getCreativeCommons"); ?>",
				{ value: jQuery("#creative_commons_select_<?php echo $file_id; ?>").val() },
				function(data) {
					jQuery("#creative_commons_img_<?php echo $file_id; ?> span").html("<a href='javascript: void(0);' class='tooltip' name='" + data.description + "'><img src='" + data.img + "' /></a>");
					tooltip();
					jQuery("#edit_creative_commons_<?php echo $file_id; ?>").fadeOut(200, function() {
						jQuery("#creative_commons_img_<?php echo $file_id; ?>").fadeIn();
					});
				},
				"json"
			);
		});

		jQuery("#creative_commons_img_<?php echo $file_id; ?> a.edit-limitation").live("click", function() {
			jQuery("#creative_commons_img_<?php echo $file_id; ?>").fadeOut(200, function() {
				jQuery("#edit_creative_commons_<?php echo $file_id; ?>").fadeIn(200, function() {
					jQuery("#creative_commons_select_<?php echo $file_id; ?>").focus();
				});
			});
		});

		jQuery("#author_<?php echo $file_id; ?>").bind("focus", function() {
			if(jQuery(this).val() == "<?php echo __("To inform"); ?>")
			{
				jQuery(this).val("");
				jQuery(this).removeClass("nc");
			}
		});

		jQuery("#author_<?php echo $file_id; ?>").bind("blur", function() {
			if(jQuery(this).val() == "")
			{
				jQuery(this).val("<?php echo __("To inform"); ?>");
				jQuery(this).addClass("nc");
			}
		});

		jQuery('#addTagRemote_<?php echo $file_id; ?>').bind("click", function() {
			if(jQuery.trim(jQuery('#tag_title_<?php echo $file_id; ?>').val()).length > 0 && jQuery('#tag_title_<?php echo $file_id; ?>').val() != "<?php echo __("Add tag ...")?>")
			{
				jQuery("#tags_input_<?php echo $file_id; ?>").val(jQuery("#tags_input_<?php echo $file_id; ?>").val() + jQuery('#tag_title_<?php echo $file_id; ?>').val() + "|");

				var a = jQuery("<a href='#' onclick='deleteTag(this, <?php echo $file_id; ?>);'><span>" + jQuery('#tag_title_<?php echo $file_id; ?>').val() + "</span><em id=''></em></a>");
				jQuery('#file_tags_<?php echo $file_id; ?> .list_tags').append(a);
				jQuery('#tag_title_<?php echo $file_id; ?>').val("<?php echo __("Add tag ..."); ?>");
				jQuery('#tag_title_<?php echo $file_id; ?>').addClass("nc");
			}
		});

		jQuery("#tag_title_<?php echo $file_id; ?>").bind("keydown", function(event) {
			var code = (event.keyCode ? event.keyCode : event.which);

			if(code == 13)
			{
				jQuery('#addTagRemote_<?php echo $file_id; ?>').trigger("click");
				jQuery("#tag_title_<?php echo $file_id; ?>").blur();
			}
		});

		jQuery("#tag_title_<?php echo $file_id; ?>").autocomplete({
			source: '<?php echo url_for("tag/fetchTags"); ?>',
			minLength: 3
		});
	<?php endforeach; ?>

	jQuery(".eotf").editable(
		function(value, settings) {
			jQuery("#name_" + jQuery(this).attr("id")).val(value);
			return(value);
		},
		{
			indicator: '<?php echo __("Saving");?>...',
			placeholder: '',
			cssclass: 'editable-details-file',
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

	jQuery(".eotf").bind("mouseover", overTd);
	jQuery(".eotf").bind("mouseout", outTd);

	jQuery(".advanced_info").bind("click", function() {
		if(jQuery("#advanced_" + jQuery(this).attr("rel")).is(":visible"))
		{
			jQuery(this).find("img").attr("src", "<?php echo image_path("right-arr.gif"); ?>");
			jQuery("#advanced_" + jQuery(this).attr("rel")).slideUp();
		}
		else
		{
			jQuery(this).find("img").attr("src", "<?php echo image_path("down-arr.gif"); ?>");
			jQuery("#advanced_" + jQuery(this).attr("rel")).slideDown();
		}
	});

	jQuery(".apply_fields").bind("click", function() {
		if(jQuery(this).is(":checked") == true)
		{
			<?php foreach($fields as $field) : ?>
				var val_<?php echo $field->getId(); ?> = jQuery("#field_<?php echo $field->getId(); ?>_" + jQuery(this).attr("rel")).val();
				var check_<?php echo $field->getId(); ?> = jQuery("#field_<?php echo $field->getId(); ?>_" + jQuery(this).attr("rel")).is(":checked");

				<?php foreach($files_array as $file_id): ?>
					if(jQuery(this).attr("rel") != <?php echo $file_id; ?>)
					{
						if(!jQuery("#advanced_<?php echo $file_id; ?>").is(":visible"))
						{
							jQuery("#advanced_container_img_<?php echo $file_id; ?>").attr("src", "<?php echo image_path("down-arr.gif"); ?>");
							jQuery("#advanced_<?php echo $file_id; ?>").slideDown();
						}

						jQuery("#field_<?php echo $field->getId(); ?>_<?php echo $file_id; ?>").parent().fadeOut(200, function() {
							<?php
								switch($field->getType())
								{
									case FieldPeer::__TYPE_DATE: ?>
										jQuery("#date_field_<?php echo $field->getId(); ?>_<?php echo $file_id; ?>").html(val_<?php echo $field->getId(); ?>);
										jQuery("#field_<?php echo $field->getId(); ?>_<?php echo $file_id; ?>").val(val_<?php echo $field->getId(); ?>);
									<?php break;

									case FieldPeer::__TYPE_BOOLEAN: ?>
										jQuery("#field_<?php echo $field->getId(); ?>_<?php echo $file_id; ?>").attr("checked", check_<?php echo $field->getId(); ?>);
									<?php break;

									default: ?>
										jQuery("#field_<?php echo $field->getId(); ?>_<?php echo $file_id; ?>").val(val_<?php echo $field->getId(); ?>);
									<?php break;
								}
							?>

							jQuery(this).fadeIn();
						});

						jQuery("#apply_fields_<?php echo $file_id; ?>").attr("checked", false);
						jQuery("#apply_fields_<?php echo $file_id; ?>").attr("disabled", true);
					}
					else
					{
						jQuery("#apply_fields_<?php echo $file_id; ?>").attr("disabled", false);
					}
				<?php endforeach; ?>
			<?php endforeach; ?>
		}
		else
		{
			<?php foreach($files_array as $file_id): ?>
				jQuery("#apply_fields_<?php echo $file_id; ?>").attr("checked", false);
				jQuery("#apply_fields_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
	});

	jQuery(".apply_usage_right").live("click", function() {
		if(jQuery(this).is(":checked") == true)
		{
			var licence = jQuery("#licence_" + jQuery(this).attr("rel")).val();
			var creative_commons = jQuery("#creative_commons_select_" + jQuery(this).attr("rel")).val();
			var creative_commons_img = jQuery("#creative_commons_img_" + jQuery(this).attr("rel") + " span").html();
			var use = jQuery("#use_" + jQuery(this).attr("rel")).val();
			var distribution = jQuery("#distribution_" + jQuery(this).attr("rel")).val();
			var constraint = jQuery("#constraint_" + jQuery(this).attr("rel")).val();

			<?php foreach($limitations as $limitation) : ?>
					var check_<?php echo $limitation->getId(); ?> = jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_" + jQuery(this).attr("rel")).is(":checked");
					var value_hidden_<?php echo $limitation->getId(); ?> = jQuery("#limitation_<?php echo $limitation->getId(); ?>_" + jQuery(this).attr("rel")).val();
					var value_text_<?php echo $limitation->getId(); ?> = jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_" + jQuery(this).attr("rel")).val();
			<?php endforeach; ?>

			<?php foreach($files_array as $file_id): ?>
				if(jQuery(this).attr("rel") != <?php echo $file_id; ?>)
				{
					if(!jQuery("#advanced_<?php echo $file_id; ?>").is(":visible"))
					{
						jQuery("#advanced_container_img_<?php echo $file_id; ?>").attr("src", "<?php echo image_path("down-arr.gif"); ?>");
						jQuery("#advanced_<?php echo $file_id; ?>").slideDown();
					}

					jQuery("#licence_<?php echo $file_id; ?>").val(licence);
					jQuery("#licence_<?php echo $file_id; ?>").trigger("change");

					jQuery("#creative_commons_select_<?php echo $file_id; ?>").val(creative_commons);
					jQuery("#creative_commons_img_<?php echo $file_id; ?> span").html(creative_commons_img);

					jQuery("#use_<?php echo $file_id; ?>").val(use);
					jQuery("#use_<?php echo $file_id; ?>").trigger("change");

					jQuery("#distribution_<?php echo $file_id; ?>").val(distribution);
					jQuery("#distribution_<?php echo $file_id; ?>").trigger("change");

					jQuery("#constraint_<?php echo $file_id; ?>").val(constraint);
					jQuery("#constraint_<?php echo $file_id; ?>").trigger("change");

					<?php foreach($limitations as $limitation) : ?>
						jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file_id; ?>").attr("checked", check_<?php echo $limitation->getId(); ?>);
						checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file_id; ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file_id; ?>);

						jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file_id; ?>").val(value_hidden_<?php echo $limitation->getId(); ?>);
						jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file_id; ?>").val(value_text_<?php echo $limitation->getId(); ?>);
					<?php endforeach; ?>

					jQuery("#apply_usage_right_<?php echo $file_id; ?>").attr("checked", false);
					jQuery("#apply_usage_right_<?php echo $file_id; ?>").attr("disabled", true);
				}
				else
					jQuery("#apply_usage_right_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
		else
		{
			<?php foreach($files_array as $file_id): ?>
				jQuery("#apply_usage_right_<?php echo $file_id; ?>").attr("checked", false);
				jQuery("#apply_usage_right_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
	});

	jQuery(".apply_created_at").bind("click", function() {
		if(jQuery(this).is(":checked") == true)
		{
			var day = jQuery("#created_at_" + jQuery(this).attr("rel") + "_day").val();
			var month = jQuery("#created_at_" + jQuery(this).attr("rel") + "_month").val();
			var year = jQuery("#created_at_" + jQuery(this).attr("rel") + "_year").val();

			var hour = jQuery("#created_at_" + jQuery(this).attr("rel") + "_hour").val();
			var minute = jQuery("#created_at_" + jQuery(this).attr("rel") + "_minute").val();
			var second = jQuery("#created_at_" + jQuery(this).attr("rel") + "_second").val();

			<?php foreach($files_array as $file_id): ?>
				if(jQuery(this).attr("rel") != <?php echo $file_id; ?>)
				{
					if(!jQuery("#advanced_<?php echo $file_id; ?>").is(":visible"))
					{
						jQuery("#advanced_container_img_<?php echo $file_id; ?>").attr("src", "<?php echo image_path("down-arr.gif"); ?>");
						jQuery("#advanced_<?php echo $file_id; ?>").slideDown();
					}

					jQuery("#created_at_<?php echo $file_id; ?>_day").parent().fadeOut(200, function() {
						jQuery("#created_at_<?php echo $file_id; ?>_day").val(day);
						jQuery("#created_at_<?php echo $file_id; ?>_month").val(month);
						jQuery("#created_at_<?php echo $file_id; ?>_year").val(year);
						jQuery("#created_at_<?php echo $file_id; ?>_hour").val(hour);
						jQuery("#created_at_<?php echo $file_id; ?>_minute").val(minute);
						jQuery("#created_at_<?php echo $file_id; ?>_second").val(second);

						jQuery("#created_at_<?php echo $file_id; ?>_day").attr("disabled", true);
						jQuery("#created_at_<?php echo $file_id; ?>_month").attr("disabled", true);
						jQuery("#created_at_<?php echo $file_id; ?>_year").attr("disabled", true);
						jQuery("#created_at_<?php echo $file_id; ?>_hour").attr("disabled", true);
						jQuery("#created_at_<?php echo $file_id; ?>_minute").attr("disabled", true);
						jQuery("#created_at_<?php echo $file_id; ?>_second").attr("disabled", true);

						jQuery(this).fadeIn();
					});

					jQuery("#apply_created_at_<?php echo $file_id; ?>").attr("checked", false);
					jQuery("#apply_created_at_<?php echo $file_id; ?>").attr("disabled", true);
				}
				else
				{
					jQuery("#created_at_<?php echo $file_id; ?>_day").attr("disabled", false);
					jQuery("#created_at_<?php echo $file_id; ?>_month").attr("disabled", false);
					jQuery("#created_at_<?php echo $file_id; ?>_year").attr("disabled", false);
					jQuery("#created_at_<?php echo $file_id; ?>_hour").attr("disabled", false);
					jQuery("#created_at_<?php echo $file_id; ?>_minute").attr("disabled", false);
					jQuery("#created_at_<?php echo $file_id; ?>_second").attr("disabled", false);

					jQuery("#apply_created_at_<?php echo $file_id; ?>").attr("disabled", false);
				}
			<?php endforeach; ?>
		}
		else
		{
			<?php foreach($files_array as $file_id): ?>
				jQuery("#created_at_<?php echo $file_id; ?>_day").attr("disabled", false);
				jQuery("#created_at_<?php echo $file_id; ?>_month").attr("disabled", false);
				jQuery("#created_at_<?php echo $file_id; ?>_year").attr("disabled", false);
				jQuery("#created_at_<?php echo $file_id; ?>_hour").attr("disabled", false);
				jQuery("#created_at_<?php echo $file_id; ?>_minute").attr("disabled", false);
				jQuery("#created_at_<?php echo $file_id; ?>_second").attr("disabled", false);

				jQuery("#apply_created_at_<?php echo $file_id; ?>").attr("checked", false);
				jQuery("#apply_created_at_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
	});

	jQuery(".apply_author").bind("click", function() {
		if(jQuery(this).is(":checked") == true)
		{
			var author = jQuery("#author_" + jQuery(this).attr("rel")).val();

			<?php foreach($files_array as $file_id): ?>
				if(jQuery(this).attr("rel") != <?php echo $file_id; ?>)
				{
					if(!jQuery("#advanced_<?php echo $file_id; ?>").is(":visible"))
					{
						jQuery("#advanced_container_img_<?php echo $file_id; ?>").attr("src", "<?php echo image_path("down-arr.gif"); ?>");
						jQuery("#advanced_<?php echo $file_id; ?>").slideDown();
					}

					jQuery("#author_<?php echo $file_id; ?>").parent().fadeOut(200, function() {
						jQuery("#author_<?php echo $file_id; ?>").val(author);
						jQuery("#author_<?php echo $file_id; ?>").removeClass("nc");

						jQuery("#author_<?php echo $file_id; ?>").attr("disabled", true);
						jQuery(this).fadeIn();
					});

					jQuery("#apply_author_<?php echo $file_id; ?>").attr("checked", false);
					jQuery("#apply_author_<?php echo $file_id; ?>").attr("disabled", true);
				}
				else
				{
					jQuery("#author_<?php echo $file_id; ?>").attr("disabled", false);
					jQuery("#apply_author_<?php echo $file_id; ?>").attr("disabled", false);
				}
			<?php endforeach; ?>
		}
		else
		{
			<?php foreach($files_array as $file_id): ?>
				jQuery("#author_<?php echo $file_id; ?>").attr("disabled", false);
				jQuery("#apply_author_<?php echo $file_id; ?>").attr("checked", false);
				jQuery("#apply_author_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
	});

	jQuery(".apply_description").bind("click", function() {
		if(jQuery(this).is(":checked") == true)
		{
			var description = jQuery("#description_" + jQuery(this).attr("rel")).val();

			<?php foreach($files_array as $file_id): ?>
				if(jQuery(this).attr("rel") != <?php echo $file_id; ?>)
				{
					jQuery("#description_<?php echo $file_id; ?>").parent().fadeOut(200, function() {
						jQuery("#description_<?php echo $file_id; ?>").val(description);
						jQuery("#description_<?php echo $file_id; ?>").attr("disabled", true);

						jQuery(this).fadeIn();
					});

					jQuery("#apply_description_<?php echo $file_id; ?>").attr("checked", false);
					jQuery("#apply_description_<?php echo $file_id; ?>").attr("disabled", true);
				}
				else
				{
					jQuery("#description_<?php echo $file_id; ?>").attr("disabled", false);
					jQuery("#apply_description_<?php echo $file_id; ?>").attr("disabled", false);
				}
			<?php endforeach; ?>
		}
		else
		{
			<?php foreach($files_array as $file_id): ?>
				jQuery("#description_<?php echo $file_id; ?>").attr("disabled", false);
				jQuery("#apply_description_<?php echo $file_id; ?>").attr("checked", false);
				jQuery("#apply_description_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
	});

	jQuery(".apply_tags").bind("click", function() {
		if(jQuery(this).is(":checked") == true)
		{
			var tags = jQuery("#file_tags_" + jQuery(this).attr("rel") + " .list_tags").html();
			var tags_input = jQuery("#tags_input_" + jQuery(this).attr("rel")).val();

			<?php foreach($files_array as $file_id): ?>
				if(jQuery(this).attr("rel") != <?php echo $file_id; ?>)
				{
					jQuery("#file_tags_<?php echo $file_id; ?>").parent().fadeOut(200, function() {
						jQuery("#file_tags_<?php echo $file_id; ?>").html("<span class='list_tags'>" + tags + "</span>");
						jQuery("#tags_input_<?php echo $file_id; ?>").val(tags_input);

						jQuery(this).fadeIn();
					});

					jQuery("#apply_tags_<?php echo $file_id; ?>").attr("checked", false);
					jQuery("#apply_tags_<?php echo $file_id; ?>").attr("disabled", true);
				}
				else
					jQuery("#apply_tags_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
		else
		{
			<?php foreach($files_array as $file_id): ?>
				jQuery("#apply_tags_<?php echo $file_id; ?>").attr("checked", false);
				jQuery("#apply_tags_<?php echo $file_id; ?>").attr("disabled", false);
			<?php endforeach; ?>
		}
	});

	<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)) : ?>
		jQuery(".thesaurus_tree").treeview({
			url: "<?php echo url_for("thesaurus/tree"); ?>",
			ajax: {
				data: {
					"culture": function() {
						return "<?php echo $sf_user->getCulture(); ?>";
					}
				},
				type: "post"
			}
		});

		jQuery(".thesaurus_title").bind("click", function() {
			if(jQuery("#thesaurus_" + jQuery(this).attr("rel")).is(":visible"))
			{
				jQuery(this).find("img").attr("src", "<?php echo image_path("right-arr.gif"); ?>");
				jQuery("#thesaurus_" + jQuery(this).attr("rel")).slideUp();
			}
			else
			{
				jQuery(this).find("img").attr("src", "<?php echo image_path("down-arr.gif"); ?>");
				jQuery("#thesaurus_" + jQuery(this).attr("rel")).slideDown();
			}
		});

		<?php foreach($files_array as $file_id): ?>
			jQuery("#thesaurus_<?php echo $file_id; ?> a.addThesaurus").live("click", function() {
				
				var current = jQuery(this).parent().parent();
				var new_object = jQuery(this).parent().clone().prependTo(current).css({'position' : 'absolute'});

				if(jQuery("#file_tags_<?php echo $file_id; ?> .list_tags").children('a:last').length > 0)
					var to = jQuery("#file_tags_<?php echo $file_id; ?> .list_tags").children('a:last');
				else
					var to = jQuery("#file_tags_<?php echo $file_id; ?>");

				var toX = to.offset().left + to.width();
				var toY = to.offset().top;

				var fromX = jQuery(new_object).offset().left;
				var fromY = jQuery(new_object).offset().top;

				var gotoX = toX - fromX;
				var gotoY = toY - fromY;

				jQuery(new_object)
					.animate({opacity: 0.4}, 100)
					.animate({opacity: 0.2, marginLeft: gotoX, marginTop: gotoY}, 1200, function() {
						jQuery(this).remove();

						jQuery("#tags_input_<?php echo $file_id; ?>").val(jQuery("#tags_input_<?php echo $file_id; ?>").val() + jQuery(current).find("span:first").text() + "|");

						var a = jQuery("<a href='#' onclick='deleteTag(this, <?php echo $file_id; ?>);'><span>" + jQuery(current).find("span:first").text() + "</span><em id=''></em></a>");
						jQuery('#file_tags_<?php echo $file_id; ?> .list_tags').append(a);
					});
			});
		<?php endforeach; ?>
	<?php endif; ?>
});
</script>