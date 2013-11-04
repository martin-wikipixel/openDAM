<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Licence"); ?> : </label>
<select name="licence_<?php echo $file->getId(); ?>" id="licence_<?php echo $file->getId(); ?>" class="licence" rel="<?php echo $file->getId(); ?>">
	<option value="-1" <?php echo $preset ? ($preset->getLicenceId() == null ? "selected" : "") : ($file->getLicenceId() == null ? "selected" : ""); ?>><?php echo __("To inform"); ?></option>
	<?php foreach($licences as $licence) : ?>
		<option value="<?php echo $licence->getId(); ?>" <?php echo $preset ? ($preset->getLicenceId() == $licence->getId() ? "selected" : "") : ($file->getLicenceId() == $licence->getId() ? "selected" : ""); ?>><?php echo $licence->getTitle(); ?></option>
	<?php endforeach; ?>
</select>

<div id="div_creative_commons_<?php echo $file->getId(); ?>" <?php echo $preset ? ($preset->getLicenceId() != LicencePeer::__CREATIVE_COMMONS ? "style='display: none;'" : "") : ($file->getLicenceId() != LicencePeer::__CREATIVE_COMMONS ? "style='display: none;'" : ""); ?>>
	<div id="creative_commons_img_<?php echo $file->getId(); ?>">
		<span>
			<a href="javascript: void(0);" class="tooltip" name="<?php echo $preset ? ($preset->getCreativeCommonsId() ? $preset->getCreativeCommons()->getDescription() : "") : ($file->getCreativeCommonsId() ? $file->getCreativeCommons()->getDescription() : ""); ?>">
				<img src="<?php echo $preset ? ($preset->getCreativeCommonsId() ? image_path($preset->getCreativeCommons()->getImagePath()) : image_path("creative_commons/cc.jpg")) : ($file->getCreativeCommonsId() ? image_path($file->getCreativeCommons()->getImagePath()) : image_path("creative_commons/cc.jpg")); ?>" />
			</a>
		</span>
		<a href="javascript: void(0);" class="edit-limitation"></a>
	</div>
	<div id="edit_creative_commons_<?php echo $file->getId(); ?>" style="display: none;">
		<select name="creative_commons_select_<?php echo $file->getId(); ?>" id="creative_commons_select_<?php echo $file->getId(); ?>" style="float: left; width: 150px;">
			<?php $creative_commons = CreativeCommonsPeer::getCreativeCommons(); ?>
			<?php foreach($creative_commons as $creative_common) : ?>
				<option value="<?php echo $creative_common->getId(); ?>" <?php echo $preset ? ($preset->getCreativeCommonsId() == $creative_common->getId() ? "selected" : "") : ($file->getCreativeCommonsId() == $creative_common->getId() ? "selected" : ""); ?>><?php echo $creative_common->getTitle(); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<br clear="all">

<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Use"); ?> : </label>
<select name="use_<?php echo $file->getId(); ?>" id="use_<?php echo $file->getId(); ?>" class="use" rel="<?php echo $file->getId(); ?>">
	<option value="-1" <?php echo $preset ? ($preset->getUsageUseId() == null ? "selected" : "") : ($file->getUsageUseId() == null ? "selected" : ""); ?>><?php echo __("To inform"); ?></option>
	<?php foreach($uses as $use) : ?>
		<option value="<?php echo $use->getId(); ?>" <?php echo $preset ? ($preset->getUsageUseId() == $use->getId() ? "selected" : "") : ($file->getUsageUseId() == $use->getId() ? "selected" : ""); ?>><?php echo $use->getTitle(); ?></option>
	<?php endforeach; ?>
</select>

<br clear="all">

<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Distribution"); ?> : </label>
<select name="distribution_<?php echo $file->getId(); ?>" id="distribution_<?php echo $file->getId(); ?>" class="distribution" rel="<?php echo $file->getId(); ?>">
	<!--<option value="-1" <?php echo $file->getUsageDistributionId() == null ? "selected" : ""; ?>><?php echo __("To inform"); ?></option>-->
	<?php foreach($distributions as $distribution) : ?>
		<option value="<?php echo $distribution->getId(); ?>" <?php echo $preset ? ($preset->getUsageDistributionId() == $distribution->getId() ? "selected" : "") : ($file->getUsageDistributionId() == $distribution->getId() ? "selected" : ""); ?>><?php echo $distribution->getTitle(); ?></option>
	<?php endforeach; ?>
</select>

<div id="div_limitation_<?php echo $file->getId(); ?>" <?php echo $preset ? ($preset->getUsageDistributionId() != UsageDistributionPeer::__AUTH ? "style='display: none;'" : "") : ($file->getUsageDistributionId() != UsageDistributionPeer::__AUTH ? "style='display: none;'" : ""); ?> class="limitation_div">
	<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Distribution restrictions"); ?> : </label>
	<ul style="margin-top: 5px;">
		<?php foreach($limitations as $limitation) : ?>
			<?php $addClass = ""; ?>
			<?php $file_right = $preset ? FileRightPeer::retrieveByTypeAndLimitation($preset->getId(), 4, $limitation->getId()) : FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId()); ?>
			<?php $value_hidden = $file_right ? $file_right->getValue() : ""; ?>
			<li class="text">
				<input type="checkbox" name="check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" value="<?php echo $limitation->getId(); ?>" id="check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" class="left check_limitation_<?php echo $file->getId(); ?>" style="margin-right: 5px;" <?php echo !empty($value_hidden) ? "checked" : ""; ?> />
				<label for="check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" class="label_limitation">
					<?php echo $limitation->getTitle(); ?>
					<?php switch($limitation->getUsageTypeId()) :
						case UsageTypePeer::__TYPE_BOOLEAN: ?>
							<script>
								jQuery(document).ready(function() {
									if(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").is(":checked"))
									{
										var val = jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val();

										jQuery(".check_limitation").each(function() {
											if(jQuery(this).val() != val)
											{
												jQuery(this).attr("disabled", "disabled");
												jQuery(this).attr("checked", false);
												jQuery("#show_limitation_" + jQuery(this).val()).addClass("disabled");
												jQuery("#show_limitation_" + jQuery(this).val()).val("");
												jQuery("#limitation_" + jQuery(this).val()).val("");
											}
										});
									}

									jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										var val = jQuery(this).val();

										if(jQuery(this).is(":checked"))
										{
											jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(1);

											jQuery(".check_limitation_<?php echo $file->getId(); ?>").each(function() {
												if(jQuery(this).val() != val)
												{
													jQuery(this).attr("disabled", "disabled");
													jQuery(this).attr("checked", false);
													jQuery("#show_limitation_" + jQuery(this).val() + "_<?php echo $file->getId(); ?>").addClass("disabled");
													jQuery("#show_limitation_" + jQuery(this).val() + "_<?php echo $file->getId(); ?>").val("");
													jQuery("#limitation_" + jQuery(this).val() + "_<?php echo $file->getId(); ?>").val("");
												}
											});
										}
										else
										{
											jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(0);

											jQuery(".check_limitation_<?php echo $file->getId(); ?>").each(function() {
												if(jQuery(this).val() != val)
													jQuery(this).attr("disabled", "");
											});
										}
									});
								});
							</script>
						<?php break;

						case UsageTypePeer::__TYPE_TEXT:
							$ro = "";
							$value_text = $value_hidden; ?>
							<script>
								jQuery(document).ready(function() {
									jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("blur", function() {
										jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(jQuery(this).val());

										if(jQuery(this).val() == "")
										{
											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
											checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
										}
									});

									jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
									});
								});
							</script>
						<?php break;

						case UsageTypePeer::__TYPE_NUM:
							$ro = "";
							$value_text = $value_hidden; ?>
							<script>
								jQuery(document).ready(function() {
									jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
									});

									jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("blur", function() {
										jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(jQuery(this).val());

										if(jQuery(this).val() == "")
										{
											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
											checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
										}
									});

									jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").keydown(function(event) {
										// Allow: backspace, delete, tab and escape
										if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || 
											 // Allow: Ctrl+A
											(event.keyCode == 65 && event.ctrlKey === true) || 
											 // Allow: home, end, left, right
											(event.keyCode >= 35 && event.keyCode <= 39)) {
												 // let it happen, don't do anything
												 return;
										}
										else
										{
											// Ensure that it is a number and stop the keypress
											if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
												event.preventDefault(); 
											}
											else if(event.keyCode >= 48 && event.keyCode <= 57)
											{
												var val = jQuery(this).val();

												switch(event.keyCode)
												{
													case 48: jQuery(this).val(val + "0"); break;
													case 49: jQuery(this).val(val + "1"); break;
													case 50: jQuery(this).val(val + "2"); break;
													case 51: jQuery(this).val(val + "3"); break;
													case 52: jQuery(this).val(val + "4"); break;
													case 53: jQuery(this).val(val + "5"); break;
													case 54: jQuery(this).val(val + "6"); break;
													case 55: jQuery(this).val(val + "7"); break;
													case 56: jQuery(this).val(val + "8"); break;
													case 57: jQuery(this).val(val + "9"); break;
												}

												 event.preventDefault();
											}
										}
									});
								});
							</script>
						<?php break;

						case UsageTypePeer::__TYPE_DATE:
							$ro = "readonly";
							$value_text = $value_hidden;

							if(!empty($value_hidden))
							{
								$temp = explode(" ", $value_hidden);
								$date = explode("/", $temp[0]);

								if(time() > mktime(0, 0, 0, $date[1], $date[0], $date[2]))
									$addClass = 'expired';
							} ?>
							<a href='javascript: void(0);' class='edit-limitation' id="date_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"></a>
							<script>
								jQuery(document).ready(function() {
									jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);
									jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").datepicker({
										showOn: "focus",
										currentText: '<?php echo addslashes(__("Now")); ?>',
										closeText: '<?php echo __("Save"); ?>',
										buttonText: '',
										dateFormat: 'dd/mm/yy',
										firstDay: 1,
										gotoCurrent: true,
										minDate: 0,
										beforeShow: function(input, inst) {
											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", true);
											checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
										},
										onClose: function(dateText, inst) {
											if(dateText == "")
											{
												jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
												checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
											}
										},
										onSelect: function(dateText, inst) {
											if(dateText != "")
											{
												var selected = jQuery(this).datepicker('getDate');
												var now = new Date();

												if(now.getTime() > selected.getTime())
													jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").addClass("expired");
												else
													jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").removeClass("expired");

												jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(dateText);
												jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(dateText);
												jQuery(this).datepicker("hide");
											}
										}
									});

									jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										if(jQuery(this).is(":checked") == true)
											jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").focus();

										checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
									});

									jQuery("#date_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").focus();
									});
								});
							</script>
						<?php break;

						case UsageTypePeer::__TYPE_GEO:
							$ro = "readonly";
							$value_text = $value_hidden;

							if(!empty($value_text))
							{
								$countries = explode(";", $value_text);

								$value_text = "";
								$ids = Array();

								foreach($countries as $country_id)
								{
									if(!empty($country_id))
									{
										$country = CountryPeer::retrieveByPk($country_id);
										$value_text .= $country->getTitle().", ";
										$ids[] = $country->getId();
									}
								}

								$value_text = substr($value_text, 0, -2);

								if($text = ContinentPeer::referToContinent($ids))
									$value_text = $text;
							} ?>
							<a href='javascript: void(0);' class='edit-limitation' id="map_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"></a>
							<script>
								jQuery(document).ready(function() {
									jQuery("#map_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", true);
										checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);

										var div = jQuery("<div id='map_dialog'><div style='width: 100%; text-align: center;'><img src='<?php echo image_path('loader-rotate.gif'); ?>' /></div></div>").insertAfter(this);

										jQuery(div).dialog({
											title: "<span class='first-title'><?php echo __("Select continent / country"); ?></span>",
											resizable: false,
											draggable: false,
											modal: true,
											width: "630",
											height: "400",
											show: 'fade',
											hide: 'fade',
											buttons: [
												{
													text: "<?php echo __("Save"); ?>",
													click: function() {
														var text = "";
														var ids = "";

														jQuery(".check_country:checked").each(function() {
															jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val() + jQuery(this).val() + ";");
															text += jQuery(this).attr("rel") + ", ";
															ids += jQuery(this).val() + ";";
														});

														jQuery(".continent").each(function() {
															if(jQuery(this).attr("rel") == ids)
																text = jQuery(this).val();
														});

														jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(text);

														if(jQuery(".check_country:checked").length == 0)
														{
															jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
															checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
														}

														jQuery(this).dialog("close");
													}
												}
											],
											open: function(event, ui) {
												jQuery.post(
													"<?php echo url_for("right/loadGeo"); ?>",
													function(data) {
														jQuery(div).fadeOut(200, function() {
															jQuery(div).html(data);

															var ids = jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val().split(";");

															for(var i = 0; i < ids.length; i++)
																jQuery('.check_country[value="' + ids[i] + '"]').attr("checked", true);

															jQuery(".continent").each(function() {
																if(jQuery(this).attr("rel") == jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val())
																	jQuery('#continent_' + jQuery(this).attr('title')).attr('checked', true);
															});

															jQuery(div).fadeIn();
														});
													}
												);
											},
											close: function(event, ui) {
												if(jQuery(".check_country:checked").length == 0)
												{
													jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
													checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
												}

												jQuery(this).remove();
											}
										});
									});

									jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										if(jQuery(this).is(":checked") == true)
											jQuery("#map_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").trigger("click");

										checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
									});
								});
							</script>
						<?php break;

						case UsageTypePeer::__TYPE_SUPPORT:
							$ro = "readonly";
							$value_text = $value_hidden;

							if(!empty($value_text))
							{
								$supports = explode(";", $value_text);

								$value_text = "";
								foreach($supports as $support_id)
								{
									if(!empty($support_id))
									{
										$support = UsageSupportPeer::retrieveByPk($support_id);
										$value_text .= $support->getTitle().", ";
									}
								}

								$value_text = substr($value_text, 0, -2);
							} ?>
							<a href='javascript: void(0);' class='edit-limitation' id="support_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"></a>
							<script>
								jQuery(document).ready(function() {
									jQuery("#support_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", true);
										checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);

										var div = jQuery("<div id='support_dialog'><div style='width: 100%; text-align: center;'><img src='<?php echo image_path('loader-rotate.gif'); ?>' /></div></div>").insertAfter(this);

										jQuery(div).dialog({
											title: "<span class='first-title'><?php echo __("Select support"); ?></span>",
											resizable: false,
											draggable: false,
											modal: true,
											width: "630",
											height: "400",
											show: 'fade',
											hide: 'fade',
											buttons: [
												{
													text: "<?php echo __("Save"); ?>",
													click: function() {
														var text = "";

														jQuery(".check_support:checked").each(function() {
															jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val() + jQuery(this).val() + ";");
															text += jQuery(this).attr("rel") + ", ";
														});

														jQuery("#show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val(text);

														if(jQuery(".check_support:checked").length == 0)
														{
															jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
															checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
														}

														jQuery(this).dialog("close");
													}
												}
											],
											open: function(event, ui) {
												jQuery.post(
													"<?php echo url_for("right/loadSupport"); ?>",
													function(data) {
														jQuery(div).fadeOut(200, function() {
															jQuery(div).html(data);

															var ids = jQuery("#limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").val().split(";");

															for(var i = 0; i < ids.length; i++)
																jQuery('.check_support[value="' + ids[i] + '"]').attr("checked", true);

															jQuery(div).fadeIn();
														});
													}
												);
											},
											close: function(event, ui) {
												if(jQuery(".check_support:checked").length == 0)
												{
													jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").attr("checked", false);
													checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>"), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
												}

												jQuery(this).remove();
											}
										});
									});

									jQuery("#check_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").bind("click", function() {
										if(jQuery(this).is(":checked") == true)
											jQuery("#support_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>").trigger("click");

										checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>, <?php echo $file->getId(); ?>);
									});
								});
							</script>
						<?php break;
					endswitch; ?>
					<input type="hidden" name="limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" id="limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" value="<?php echo $value_hidden; ?>" />
					<img id="ok_<?php echo $limitation->getId(); ?>" src="<?php echo image_path("icons/accept.png"); ?>" class="ok" />
				</label>
				<?php if($limitation->getUsageTypeId() != UsageTypePeer::__TYPE_BOOLEAN) : ?>
					<br clear="all" />
					<input type="text" name="show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" id="show_limitation_<?php echo $limitation->getId(); ?>_<?php echo $file->getId(); ?>" <?php echo $ro; ?> style="width: 200px; clear: both;" <?php echo empty($value_hidden) ? 'disabled="true"' : ""; ?> class="<?php echo empty($value_hidden) ? 'disabled' : ""; ?> <?php echo $addClass; ?>" value="<?php echo $value_text; ?>" />
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>