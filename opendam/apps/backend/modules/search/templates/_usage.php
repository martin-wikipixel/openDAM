<?php
// $distributions = UsageDistributionPeer::getDistributions();
// $licences = LicencePeer::getLicenceInArray();
// $uses = UsageUsePeer::getUses();
$file_ids = $file_ids->getRawValue();
$folder_ids = $folder_ids->getRawValue();

$licences = LicencePeer::getLicenceForFiles($file_ids) + LicencePeer::getLicenceForFolders($folder_ids);
$uses = UsageUsePeer::getUseForFiles($file_ids) + UsageUsePeer::getUseForFolders($folder_ids);
$distributions = UsageDistributionPeer::getDistributionForFiles($file_ids) + UsageDistributionPeer::getDistributionForFolders($folder_ids);
?>

<?php if(!empty($licences) || !empty($uses) || !empty($distributions)) : ?>
	<div class="filterBox">
		<div class="title" style="cursor:pointer;" onclick="toggleContainer('filterbycopyright_container', 'filterbycopyright_container_img')">
			<?php echo image_tag("down-arr.gif", array("align"=>"absmiddle", "id"=>"filterbycopyright_container_img"))?>
			<h4><?php echo __("Filter by copyright")?></h4>
		</div>

		<br clear="all">

		<div id="filterbycopyright_container">
			<div id="filterByInformation">
				<?php if(!empty($licences)) : ?>
					<div class="filterRow">
						<label for="licence"><?php echo __("Licence"); ?></label>
						<select name="licence" id="licence">
							<option value='-1'><?php echo __("Select licence"); ?></option>
							<?php foreach($licences as $licence) : ?>
								<option value="<?php echo $licence->getId(); ?>" <?php echo $sf_params->get("licence") == $licence->getId() ? "selected" : ""; ?>><?php echo $licence->getTitle(); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php if($sf_params->get("licence") == LicencePeer::__CREATIVE_COMMONS) : ?>
					<div class="filterRow">
						<div id="div_creative_commons" <?php echo $sf_params->get("licence") != LicencePeer::__CREATIVE_COMMONS ? "style='display: none;'" : ""; ?>>
							<div id="creative_commons_img">
								<span>
									<a href="javascript: void(0);" class="tooltip" name="<?php echo $sf_params->get("creative_commons_select") ? CreativeCommonsPeer::retrieveByPk($sf_params->get("licence"))->getDescription() : ""; ?>">
										<img src="<?php echo $sf_params->get("creative_commons_select") ? image_path(CreativeCommonsPeer::retrieveByPk($sf_params->get("licence"))->getImagePath()) : image_path("creative_commons/cc.jpg"); ?>" />
									</a>
								</span>
								<a href="javascript: void(0);" class="edit-limitation"></a>
							</div>
							<?php
								// $creative_commons = CreativeCommonsPeer::getCreativeCommons();
								$creative_commons = CreativeCommonsPeer::getCreativeCommonForFiles($file_ids) + CreativeCommonsPeer::getCreativeCommonForFolders($folder_ids);
							?>
							<?php if(!empty($creative_commons)) : ?>
								<div id="edit_creative_commons" style="display: none;">
									<select name="creative_commons_select" id="creative_commons_select" style="float: left; width: 150px;">
										<?php foreach($creative_commons as $creative_common) : ?>
											<option value="<?php echo $creative_common->getId(); ?>" <?php echo $sf_params->get("creative_commons_select") == $creative_common->getId() ? "selected" : ""; ?>><?php echo $creative_common->getTitle(); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<script>
						jQuery(document).ready(function() {
							tooltip();

							jQuery("#creative_commons_img").hover(
								function() {
									jQuery(this).find("a.edit-limitation").fadeIn();
								}, 
								function() {
									jQuery(this).find("a.edit-limitation").fadeOut();
								}
							);

							jQuery("#creative_commons_select").bind("change", function() {
								jQuery(this).trigger("blur");
							});

							jQuery("#creative_commons_select").bind("blur", function() {
								<?php echo $submit_function; ?>
							});

							jQuery("#creative_commons_img a.edit-limitation").bind("click", function() {
								jQuery("#creative_commons_img").fadeOut(200, function() {
									jQuery("#edit_creative_commons").fadeIn(200, function() {
										jQuery("#creative_commons_select").focus();
									});
								});
							});
						});
					</script>
				<?php else: ?>
					<?php if(!empty($uses)) : ?>
						<div class="filterRow">
							<label for="use"><?php echo __("Use"); ?> : </label>
							<select name="use" id="use">
								<option value="-1"><?php echo __("Select use"); ?></option>
								<?php foreach($uses as $use) : ?>
									<option value="<?php echo $use->getId(); ?>" <?php echo $sf_params->get("use") == $use->getId() ? "selected" : ""; ?>><?php echo $use->getTitle(); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if(!empty($distributions)) : ?>
						<div class="filterRow">
							<label for="distribution"><?php echo __("Distribution"); ?></label>
							<select name="distribution" id="distribution">
								<option value='-1'><?php echo __("Select distribution"); ?></option>
								<?php foreach($distributions as $distribution) : ?>
									<option value="<?php echo $distribution->getId(); ?>" <?php echo $sf_params->get("distribution") == $distribution->getId() ? "selected" : ""; ?>><?php echo $distribution->getTitle(); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<?php if($sf_params->get("distribution") == UsageDistributionPeer::__AUTH) : ?>
						<div class="filterRow limitation_div">
							<?php
								$limitations = UsageLimitationPeer::getLimitationForFiles($file_ids) + UsageLimitationPeer::getLimitationForFolders($folder_ids);
							?>

							<ul>
								<?php foreach($limitations as $limitation) : ?>
									<?php $value_hidden = $sf_params->get("limitation_".$limitation->getId()) ? $sf_params->get("limitation_".$limitation->getId()) : ""; ?>
									<li class="text">
										<input type="checkbox" name="check_limitation_<?php echo $limitation->getId(); ?>" id="check_limitation_<?php echo $limitation->getId(); ?>" class="left" style="margin-right: 5px;" <?php echo !empty($value_hidden) ? "checked" : ""; ?> />
										<label for="check_limitation_<?php echo $limitation->getId(); ?>" class="label_limitation">
											<?php echo $limitation->getTitle(); ?>
											<?php switch($limitation->getUsageTypeId()) :
												case UsageTypePeer::__TYPE_TEXT:
													$ro = "";
													$value_text = $value_hidden; ?>
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

																<?php echo $submit_function; ?>
															});
													</script>
												<?php break;

												case UsageTypePeer::__TYPE_NUM:
													$ro = "";
													$value_text = $value_hidden; ?>
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

																<?php echo $submit_function; ?>
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
												<?php break;

												case UsageTypePeer::__TYPE_DATE:
													$ro = "readonly";
													$value_text = $value_hidden; ?>
													<a href='javascript: void(0);' class='edit-limitation' id="date_<?php echo $limitation->getId(); ?>"></a>
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
																		jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(dateText);
																		jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").val(dateText);
																		<?php echo $submit_function; ?>
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
													<a href='javascript: void(0);' class='edit-limitation' id="map_<?php echo $limitation->getId(); ?>"></a>
													<script>
														jQuery(document).ready(function() {
															jQuery("#map_<?php echo $limitation->getId(); ?>").bind("click", function() {
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

																				jQuery(this).dialog("close");

																				<?php echo $submit_function; ?>
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
													<a href='javascript: void(0);' class='edit-limitation' id="support_<?php echo $limitation->getId(); ?>"></a>
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

																				jQuery(this).dialog("close");
																				<?php echo $submit_function; ?>
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
												<?php break;
											endswitch; ?>
											<input type="hidden" name="limitation_<?php echo $limitation->getId(); ?>" id="limitation_<?php echo $limitation->getId(); ?>" value="<?php echo $value_hidden; ?>" />
											<img id="ok_<?php echo $limitation->getId(); ?>" src="<?php echo image_path("icons/accept.png"); ?>" class="ok" />
										</label>
										<br clear="all" />
										<input type="text" name="show_limitation_<?php echo $limitation->getId(); ?>" id="show_limitation_<?php echo $limitation->getId(); ?>" <?php echo $ro; ?> style="width: 200px; clear: both;" <?php echo empty($value_hidden) ? 'disabled="true"' : ""; ?> class="<?php echo empty($value_hidden) ? 'disabled' : ""; ?>" value="<?php echo $value_text; ?>" />
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<br clear="all" />
		</div>
	</div>
	<script>
		jQuery(document).ready(function() {
			jQuery(".label_limitation").hover(
				function () {
					jQuery(this).find("a.edit-limitation").fadeIn();
					jQuery(this).find("img.ui-datepicker-trigger").fadeIn();
				}, 
				function () {
					jQuery(this).find("a.edit-limitation").fadeOut();
					jQuery(this).find("img.ui-datepicker-trigger").fadeOut();
				}
			);

			jQuery("#licence").bind("change", function() {
				if(jQuery(this).val() > 0)
					<?php echo $submit_function; ?>
			});

			jQuery("#distribution").bind("change", function() {
				if(jQuery(this).val() > 0)
					<?php echo $submit_function; ?>
			});

			jQuery("#use").bind("change", function() {
				if(jQuery(this).val() > 0)
					<?php echo $submit_function; ?>
			});
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
				<?php echo $submit_function; ?>
			}
		}
	</script>
<?php endif; ?>