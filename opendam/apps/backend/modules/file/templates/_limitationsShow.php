<?php $limitations = UsageLimitationPeer::getLimitations(); ?>

<div class="rub" style="width: 100%;">
	<div class="value-right">
		<a href="javascript: void(0);" id="display-restriction"><i class="icon-exclamation-sign"></i> <?php echo __("Distribution restrictions"); ?></a>
	</div>
</div>

<br clear="all" />

<div id="div_restriction" style="display: none;">
	<br clear="all" />

	<?php foreach($limitations as $limitation) : ?>
		<?php if($role) : ?>
			<?php $addClass = ""; ?>
			<?php $file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId()); ?>
			<?php $value_hidden = $file_right ? $file_right->getValue() : ""; ?>
			<div class="rub" style="width: 100%;">
				<div class="value-right no-border" style="width: 100%; margin-bottom: 10px;">
					<input type="checkbox" name="check_limitation_<?php echo $limitation->getId(); ?>" value="<?php echo $limitation->getId(); ?>" id="check_limitation_<?php echo $limitation->getId(); ?>" class="left check_limitation" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" <?php echo !empty($value_hidden) ? "checked" : ""; ?> />
					<label for="check_limitation_<?php echo $limitation->getId(); ?>" class="label_limitation" style="margin-bottom: 3px; width: 170px; float: left;">
						<?php echo $limitation->getTitle(); ?>
						<?php switch($limitation->getUsageTypeId()) :
							case UsageTypePeer::__TYPE_BOOLEAN: ?>
								<script>
									jQuery(document).ready(function() {
										if(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").is(":checked"))
										{
											var val = jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").val();

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

										jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
											var val = jQuery(this).val();

											if(jQuery(this).is(":checked"))
											{
												jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(1);

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
											else
											{
												jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(0);

												jQuery(".check_limitation").each(function() {
													if(jQuery(this).val() != val)
														jQuery(this).attr("disabled", "");
												});
											}

											saveLimitation(<?php echo $limitation->getId(); ?>);
										});
									});
								</script>
							<?php break;

							case UsageTypePeer::__TYPE_TEXT:
								$ro = "";
								$value_text = $value_hidden; ?>
								<?php if($role) : ?>
									<script>
										jQuery(document).ready(function() {
											jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").bind("blur", function() {
												jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(jQuery(this).val());

												if(jQuery(this).val() == "")
												{
													jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
													checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
												}
												else
													saveLimitation(<?php echo $limitation->getId(); ?>);
											});

											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
												checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
											});
										});
									</script>
								<?php endif; ?>
							<?php break;

							case UsageTypePeer::__TYPE_NUM:
								$ro = "";
								$value_text = $value_hidden; ?>
								<?php if($role) : ?>
									<script>
										jQuery(document).ready(function() {
											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
												checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
											});

											jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").bind("blur", function() {
												jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(jQuery(this).val());

												if(jQuery(this).val() == "")
												{
													jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
													checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
												}
												else
													saveLimitation(<?php echo $limitation->getId(); ?>);
											});

											jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").keydown(function(event) {
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
								<?php endif; ?>
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
								<a href='javascript: void(0);' class='edit-limitation-show' id="date_<?php echo $limitation->getId(); ?>"><i class="icon-pencil"></i></a>
								<?php if($role) : ?>
									<script>
										jQuery(document).ready(function() {

											jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);
											jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").datepicker({
												showOn: "focus",
												currentText: '<?php echo addslashes(__("Now")); ?>',
												closeText: '<?php echo __("Save"); ?>',
												buttonText: '',
												dateFormat: 'dd/mm/yy',
												firstDay: 1,
												gotoCurrent: true,
												minDate: 0,
												beforeShow: function(input, inst) {
													jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", true);
													checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
												},
												onClose: function(dateText, inst) {
													if(dateText == "")
													{
														jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
														checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
													}
												},
												onSelect: function(dateText, inst) {
													if(dateText != "")
													{
														var selected = jQuery(this).datepicker('getDate');
														var now = new Date();

														if(now.getTime() > selected.getTime())
															jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").addClass("expired");
														else
															jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").removeClass("expired");

														jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(dateText);
														jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").val(dateText);
														saveLimitation(<?php echo $limitation->getId(); ?>);
														jQuery(this).datepicker("hide");
													}
												}
											});

											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
												if(jQuery(this).is(":checked") == true)
													jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").focus();

												checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
											});

											jQuery("#date_<?php echo $limitation->getId(); ?>").bind("click", function() {
												jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").focus();
											});
										});
									</script>
								<?php endif; ?>
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
								<a href='javascript: void(0);' class='edit-limitation-show' id="map_<?php echo $limitation->getId(); ?>"><i class="icon-pencil"></i></a>
								<?php if($role) : ?>
									<script>
										jQuery(document).ready(function() {
											jQuery("#map_<?php echo $limitation->getId(); ?>").bind("click", function(event) {
												jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", true);
												checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
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

																checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>, true);
																
																jQuery(".check_country:checked").each(function() {
																	jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(jQuery("#limitation_<?php echo $limitation->getId(); ?>").val() + jQuery(this).val() + ";");
																	text += jQuery(this).attr("rel") + ", ";
																	ids += jQuery(this).val() + ";";
																});

																jQuery(".continent").each(function() {
																	if(jQuery(this).attr("rel") == ids)
																		text = jQuery(this).val();
																});

																jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").val(text);

																if(jQuery(".check_country:checked").length == 0)
																{
																	jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
																	checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
																}
																else
																	saveLimitation(<?php echo $limitation->getId(); ?>);

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

																	var ids = jQuery("#limitation_<?php echo $limitation->getId(); ?>").val().split(";");

																	for(var i = 0; i < ids.length; i++)
																		jQuery('.check_country[value="' + ids[i] + '"]').attr("checked", true);

																	jQuery(".continent").each(function() {
																		if(jQuery(this).attr("rel") == jQuery("#limitation_<?php echo $limitation->getId(); ?>").val())
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
															jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
															checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
														}

														jQuery(this).remove();
													}
												});
											});

											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
												if(jQuery(this).is(":checked") == true)
													jQuery("#map_<?php echo $limitation->getId(); ?>").trigger("click");

												checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
											});
										});
									</script>
								<?php endif; ?>
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
								<a href='javascript: void(0);' class='edit-limitation-show' id="support_<?php echo $limitation->getId(); ?>"><i class="icon-pencil"></i></a>
								<?php if($role) : ?>
									<script>
										jQuery(document).ready(function() {
											jQuery("#support_<?php echo $limitation->getId(); ?>").bind("click", function() {
												jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", true);
												checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);

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
																	jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(jQuery("#limitation_<?php echo $limitation->getId(); ?>").val() + jQuery(this).val() + ";");
																	text += jQuery(this).attr("rel") + ", ";
																});

																jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").val(text);

																if(jQuery(".check_support:checked").length == 0)
																{
																	jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
																	checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
																}
																else
																	saveLimitation(<?php echo $limitation->getId(); ?>);

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

																	var ids = jQuery("#limitation_<?php echo $limitation->getId(); ?>").val().split(";");

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
															jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
															checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
														}

														jQuery(this).remove();
													}
												});
											});

											jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
												if(jQuery(this).is(":checked") == true)
													jQuery("#support_<?php echo $limitation->getId(); ?>").trigger("click");

												checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
											});
										});
									</script>
								<?php endif; ?>
							<?php break;
						endswitch; ?>
						<input type="hidden" name="limitation_<?php echo $limitation->getId(); ?>" id="limitation_<?php echo $limitation->getId(); ?>" value="<?php echo $value_hidden; ?>" />
						<span id="ok_<?php echo $limitation->getId(); ?>" class="ok"><i class=""></i></span>
					</label>

					<?php if($limitation->getUsageTypeId() != UsageTypePeer::__TYPE_BOOLEAN) : ?>
						<input type="text" name="show_limitation_<?php echo $limitation->getId(); ?>" id="show_limitation_<?php echo $limitation->getId(); ?>" <?php echo $ro; ?> style="margin-top: 0px; margin-bottom: 0px; width: 160px;" <?php echo empty($value_hidden) ? 'disabled="true"' : ""; ?> class="left <?php echo empty($value_hidden) ? 'disabled' : ""; ?> <?php echo $addClass; ?>" value="<?php echo $value_text; ?>" />
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<?php
				$file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId());
				$value = $file_right ? $file_right->getValue() : "";
			?>
			<div class="rub" style="width: 100%;">
				<div class="value-right no-border" style="width: 100%; margin-bottom: 10px;">
					<?php if($limitation->getUsageTypeId() == UsageTypePeer::__TYPE_BOOLEAN) : ?>
						<input type="checkbox" class="left" disabled="true" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" <?php echo !empty($value) ? "checked" : ""; ?> />
					<?php endif; ?>

					<label style="margin-bottom: 3px; width: 170px; float: left;"><?php echo $limitation->getTitle(); ?></label>

					<?php if($limitation->getUsageTypeId() != UsageTypePeer::__TYPE_BOOLEAN) : ?>
						<input type="text" value="<?php echo $value; ?>" style="margin-top: 0px; margin-bottom: 0px; width: 160px;" disabled="true" class="disabled" />
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<br clear="all" />
	<?php endforeach; ?>
</div>

<script>
	jQuery(document).ready(function() {
		jQuery("#display-restriction").bind("click", function() {
			if(jQuery("#div_restriction").is(":visible"))
				jQuery("#div_restriction").slideUp("slow");
			else
				jQuery("#div_restriction").slideDown("slow");
		});
	});
</script>
<?php if($role) : ?>
	<script>
		jQuery(document).ready(function() {
			jQuery(".label_limitation").hover(
				function () {
					if(jQuery(this).parent().find("input[type=checkbox]").attr("disabled") != true)
					{
						jQuery(this).find("a.edit-limitation-show").fadeIn();
						jQuery(this).find("img.ui-datepicker-trigger").fadeIn();
					}
				}, 
				function () {
					if(jQuery(this).parent().find("input[type=checkbox]").attr("disabled") != true)
					{
						jQuery(this).find("a.edit-limitation-show").fadeOut();
						jQuery(this).find("img.ui-datepicker-trigger").fadeOut();
					}
				}
			);
		});

		function checkLimitation(obj, limitation_id)
		{
			if(jQuery(obj).is(":checked") == true)
			{
				jQuery("#show_limitation_" + limitation_id).attr("disabled", false);
				jQuery("#show_limitation_" + limitation_id).removeClass("disabled");
			}
			else
			{
				jQuery("#show_limitation_" + limitation_id).attr("disabled", true);
				jQuery("#show_limitation_" + limitation_id).addClass("disabled");
				jQuery("#show_limitation_" + limitation_id).val("");
				jQuery("#limitation_" + limitation_id).val("");
				saveLimitation(limitation_id);
			}
		}

		function saveLimitation(limitation_id)
		{
			jQuery.post(
				"<?php echo url_for("file/saveLimitation"); ?>",
				{ file_id: "<?php echo $file->getId(); ?>", id: limitation_id, value: jQuery("#limitation_" + limitation_id).val() },
				function(data) {
					jQuery("#ok_" + limitation_id).fadeIn('slow').delay(1000).fadeOut('slow');
				}
			);
		}
	</script>
<?php endif; ?>