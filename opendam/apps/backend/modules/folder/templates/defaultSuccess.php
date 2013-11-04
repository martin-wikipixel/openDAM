<?php include_partial("folder/navigationManage", array("selected"=>"edit", "folder"=>$folder)); ?>
<?php include_partial("folder/subMenubarInformations", array("selected"=>"default", "folder"=>$folder));?>

<div id="searchResults-popup">
	<div class="inner">
		<form name="form_default" id="form_default" method="post" action="<?php echo url_for("folder/default"); ?>">
			<?php echo $form['_csrf_token']->render(); ?>
			<?php echo $form['id']->render(); ?>

			<!-- LOCATION START -->
			<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_GEOLOCALISATION)) : ?>
				<label for="location" style="width: 140px;"><?php echo __("Location")?> :</label>
				<?php echo $form['address']->render(); ?>
				<input class="search_btn" type="button" value="" onclick="searchLocation()" style="margin-top:9px; float: left; margin-left: 5px;"/>
				<span id="map_indicator" style="display:none;"><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif'"); ?>' style='vertical-align: middle;' /></span>
			  
				<br clear="all">

				<label style="width: 140px;">&nbsp; </label>
				<div id="map_canvas" align="center" style="float: left; margin-left:0px; width:390px; height:300px; background-color: rgb(229, 227, 223);"></div>

				<div class="description">
					<?php echo __("Zoom in - Double click on the map. Zoom out - Double right click on the map. Place a marker - Single click on the map. Remove the marker - Double click on the marker. Only one marker can be made times.")?>  
				</div>
				<!-- LOCATION END -->
			<?php endif; ?>

			<input type="hidden" name="lat" id="lat" value="<?php echo $lat; ?>" />
			<input type="hidden" name="lng" id="lng" value="<?php echo $lng; ?>" />

			<br clear="all">

			<!-- TAGS START -->
			<input type="hidden" name="tags_input" id="tags_input" />
			<label for="tag_key" style="width: 140px;"><?php echo __("Tags")?> :</label>
			<input type="text" onblur="onTagBlur(this);" onfocus="onTagFocus(this);" value="<?php echo __("Add tag ..."); ?>" id="tag_title" name="tag_title" class="nc left" style="width: 350px;" />
			<a href='#' id='addTagRemote' class="left" style="margin-left: 5px; margin-top: 10px;"><?php echo image_tag("icons/add4Bis.gif", array("align"=>"absmiddle")); ?></a>

			<br clear="all" />

			<div id="file_tags" class="left optionsButton" style="width: 388px; margin-left: 148px;">
				<?php
					$fileTags = $sf_params->get("tags_input") ? $sf_params->get("tags_input") : null;
					if(!$fileTags)
					{
						$fileTags = FileTagPeer::retrieveByFileIdType(2, $folder->getId());

						if(!sizeof($fileTags))
							$fileTags = FileTagPeer::retrieveByFileIdType(1, $folder->getGroupeId());
					}
				?>
				<?php include_partial("tag/selectedTagsFolders", array("folders_tags" => $fileTags));?>
			</div>

			<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)) : ?>
				<br clear="all">

				<label style="width: 140px;"><?php echo __("Suggested tags (thesaurus)")?></label>
				<ul class="thesaurus_tree" id="tree_thesaurus" style="padding: 5px; border: 1px solid #E6E6E6; min-height: 100px; max-height: 300px; overflow: auto; margin-top: 9px; width: 388px;"></ul>
	 
				<!-- TAGS END -->
			<?php endif; ?>

			<br clear="all">

			<label style="width: 140px;"><?php echo __("Usage rights")?> :</label>

			<?php $distributions = UsageDistributionPeer::getDistributions(); ?>
			<?php $limitations = UsageLimitationPeer::getLimitations(); ?>
			<?php $licences = LicencePeer::getLicenceInArray(); ?>
			<?php $uses = UsageUsePeer::getUses(); ?>
			<div class="left">
				<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Licence"); ?> : </label>
				<select name="licence" id="licence">
					<option value="-1" <?php echo $folder->getLicenceId() == null ? "selected" : ""; ?>><?php echo __("To inform"); ?></option>
					<?php foreach($licences as $licence) : ?>
						<option value="<?php echo $licence->getId(); ?>" <?php echo $folder->getLicenceId() == $licence->getId() ? "selected" : ""; ?>><?php echo $licence->getTitle(); ?></option>
					<?php endforeach; ?>
				</select>

				<div id="div_creative_commons" <?php echo $folder->getLicenceId() != LicencePeer::__CREATIVE_COMMONS ? "style='display: none;'" : ""; ?>>
					<div id="creative_commons_img">
						<span>
							<a href="javascript: void(0);" class="tooltip" name="<?php echo $folder->getCreativeCommonsId() ? $folder->getCreativeCommons()->getDescription() : ""; ?>">
								<img src="<?php echo $folder->getCreativeCommonsId() ? image_path($folder->getCreativeCommons()->getImagePath()) : image_path("creative_commons/cc.jpg"); ?>" />
							</a>
						</span>
						<a href="javascript: void(0);" class="edit-limitation"></a>
					</div>
					<div id="edit_creative_commons" style="display: none;">
						<select name="creative_commons_select" id="creative_commons_select" style="float: left; width: 150px;">
							<?php $creative_commons = CreativeCommonsPeer::getCreativeCommons(); ?>
							<?php foreach($creative_commons as $creative_common) : ?>
								<option value="<?php echo $creative_common->getId(); ?>" <?php echo $folder->getCreativeCommonsId() == $creative_common->getId() ? "selected" : ""; ?>><?php echo $creative_common->getTitle(); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<span id="copyrights">
					<br clear="all">

					<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Use"); ?> : </label>
					<select name="use" id="use">
						<option value="-1" <?php echo $folder->getUsageUseId() == null ? "selected" : ""; ?>><?php echo __("To inform"); ?></option>
						<?php foreach($uses as $use) : ?>
							<option value="<?php echo $use->getId(); ?>" <?php echo $folder->getUsageUseId() == $use->getId() ? "selected" : ""; ?>><?php echo $use->getTitle(); ?></option>
						<?php endforeach; ?>
					</select>

					<br clear="all">

					<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Distribution"); ?> : </label>
					<select name="distribution" id="distribution">
						<option value="-1" <?php echo $folder->getUsageDistributionId() == null ? "selected" : ""; ?>><?php echo __("To inform"); ?></option>
						<?php foreach($distributions as $distribution) : ?>
							<option value="<?php echo $distribution->getId(); ?>" <?php echo $folder->getUsageDistributionId() == $distribution->getId() ? "selected" : ""; ?>><?php echo $distribution->getTitle(); ?></option>
						<?php endforeach; ?>
					</select>

					<div id="div_limitation" <?php echo $folder->getUsageDistributionId() != UsageDistributionPeer::__AUTH ? "style='display: none;'" : ""; ?> class="limitation_div">
						<label style='font-weight: bold; margin-top: 12px; width: auto!important;'><?php echo __("Distribution restrictions"); ?> : </label>
						<ul style="margin-top: 5px;">
							<?php foreach($limitations as $limitation) : ?>
								<?php $addClass = ""; ?>
								<?php $file_right = FileRightPeer::retrieveByTypeAndLimitation($folder->getId(), 2, $limitation->getId()); ?>
								<?php $value_hidden = $file_right ? $file_right->getValue() : ""; ?>
								<li class="text">
									<input type="checkbox" name="check_limitation_<?php echo $limitation->getId(); ?>" value="<?php echo $limitation->getId(); ?>" id="check_limitation_<?php echo $limitation->getId(); ?>" class="left check_limitation" style="margin-right: 5px;" <?php echo !empty($value_hidden) ? "checked" : ""; ?> />
									<label for="check_limitation_<?php echo $limitation->getId(); ?>" class="label_limitation">
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
														});
													});
												</script>
											<?php break;

											case UsageTypePeer::__TYPE_TEXT:
												$ro = "";
												$value_text = $value_hidden; ?>
												<script>
													jQuery(document).ready(function() {
														jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").bind("blur", function() {
															jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(jQuery(this).val());

															if(jQuery(this).val() == "")
															{
																jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
																checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
															}
														});

														jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
															checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
														});
													});
												</script>
											<?php break;

											case UsageTypePeer::__TYPE_NUM:
												$ro = "";
												$value_text = $value_hidden; ?>
												<script>
													jQuery(document).ready(function() {
														jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").bind("blur", function() {
															jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(jQuery(this).val());

															if(jQuery(this).val() == "")
															{
																jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").attr("checked", false);
																checkLimitation(jQuery("#check_limitation_<?php echo $limitation->getId(); ?>"), <?php echo $limitation->getId(); ?>);
															}
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

														jQuery("#check_limitation_<?php echo $limitation->getId(); ?>").bind("click", function() {
															checkLimitation(jQuery(this), <?php echo $limitation->getId(); ?>);
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
																	var selected = jQuery(this).datepicker('getDate');
																	var now = new Date();

																	if(now.getTime() > selected.getTime())
																		jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").addClass("expired");
																	else
																		jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").removeClass("expired");

																	jQuery("#limitation_<?php echo $limitation->getId(); ?>").val(dateText);
																	jQuery("#show_limitation_<?php echo $limitation->getId(); ?>").val(dateText);
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

															if($country)
															{
																$value_text .= $country->getTitle().", ";
																$ids[] = $country->getId();
															}
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

															if($support)
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
									<?php if($limitation->getUsageTypeId() != UsageTypePeer::__TYPE_BOOLEAN) : ?>
										<br clear="all" />
										<input type="text" name="show_limitation_<?php echo $limitation->getId(); ?>" id="show_limitation_<?php echo $limitation->getId(); ?>" <?php echo $ro; ?> style="width: 200px; clear: both;" <?php echo empty($value_hidden) ? 'disabled="true"' : ""; ?> class="<?php echo empty($value_hidden) ? 'disabled' : ""; ?> <?php echo $addClass; ?>" value="<?php echo $value_text; ?>" />
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</span>
			</div>
			<script>
				jQuery(document).ready(function() {
					jQuery("#licence").bind("change", function() {
						if(jQuery(this).val() == "<?php echo LicencePeer::__CREATIVE_COMMONS; ?>")
						{
							jQuery("#div_creative_commons").fadeIn();
							// jQuery("#copyrights").fadeOut();
						}
						else
						{
							jQuery("#div_creative_commons").fadeOut();
							// jQuery("#copyrights").fadeIn();
						}
					});

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
						jQuery.post(
							"<?php echo url_for("right/getCreativeCommons"); ?>",
							{ value: jQuery("#creative_commons_select").val() },
							function(data) {
								jQuery("#creative_commons_img span").html("<a href='javascript: void(0);' class='tooltip' name='" + data.description + "'><img src='" + data.img + "' /></a>");
								tooltip();
								jQuery("#edit_creative_commons").fadeOut(200, function() {
									jQuery("#creative_commons_img").fadeIn();
								});
							},
							"json"
						);
					});

					jQuery("#creative_commons_img a.edit-limitation").bind("click", function() {
						jQuery("#creative_commons_img").fadeOut(200, function() {
							jQuery("#edit_creative_commons").fadeIn(200, function() {
								jQuery("#creative_commons_select").focus();
							});
						});
					});

					jQuery(".label_limitation").hover(
						function () {
							if(jQuery(this).parent().find("input[type=checkbox]").attr("disabled") != true)
							{
								jQuery(this).find("a.edit-limitation").fadeIn();
								jQuery(this).find("img.ui-datepicker-trigger").fadeIn();
							}
						}, 
						function () {
							if(jQuery(this).parent().find("input[type=checkbox]").attr("disabled") != true)
							{
								jQuery(this).find("a.edit-limitation").fadeOut();
								jQuery(this).find("img.ui-datepicker-trigger").fadeOut();
							}
						}
					);

					jQuery("#distribution").bind("change", function() {
						if(jQuery(this).val() == "<?php echo UsageDistributionPeer::__AUTH; ?>")
							jQuery("#div_limitation").fadeIn();
						else
							jQuery("#div_limitation").fadeOut();
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
						jQuery("#show_limitation_" + limitation_id).removeClass("expired");
						jQuery("#show_limitation_" + limitation_id).val("");
						jQuery("#limitation_" + limitation_id).val("");
					}
				}
			</script>

			<?php $fields = FieldPeer::retrieveByGroupId($folder->getGroupeId()); ?>

			<?php if(!empty($fields)) : ?>
				<br clear="all" />

				<label style="width: 140px;"><?php echo __("Specific field")?> :</label>
				<div id="fields_folder" style="margin-top: 0px;">
					<?php foreach($fields as $field) :
						$content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $folder->getId(), FieldContentPeer::__FOLDER); ?>

						<b style="line-height: 18px; font-weight: bold;" class="text"><?php echo $field->getName(); ?> : </b>

						<?php
							switch($field->getType())
							{
								case FieldPeer::__TYPE_TEXT:
								{ ?>
									<input type="text" name="field_<?php echo $field->getId(); ?>" id="field_<?php echo $field->getId(); ?>" value="<?php echo $content ? $content->getValue() : ""; ?>" style="width: 200px;" />
								<?php }
								break;

								case FieldPeer::__TYPE_BOOLEAN:
								{ ?>
									<input type="checkbox" name="field_<?php echo $field->getId(); ?>" id="field_<?php echo $field->getId(); ?>" <?php echo $content ? ($content->getValue() ? "checked" : "") : ""; ?> />
								<?php }
								break;

								case FieldPeer::__TYPE_DATE:
								{ ?>
									<span id="date_field_<?php echo $field->getId(); ?>" class="text"><?php echo $content ? $content->getValue() : "<span class='nc' style='font-size: 11px;'>".__("To inform")."</span>"; ?></span><input type="hidden" name="field_<?php echo $field->getId(); ?>" id="field_<?php echo $field->getId(); ?>" class="picker_field" rel="<?php echo $field->getId(); ?>" value="<?php echo $content ? $content->getValue() : ""; ?>" />
								<?php }
								break;

								case FieldPeer::__TYPE_SELECT:
								{
									$values = unserialize(base64_decode($field->getValues())); ?>
									<select name="field_<?php echo $field->getId(); ?>" id="field_<?php echo $field->getId(); ?>">
										<option value="0"><?php echo __("Choose"); ?></option>
										<?php foreach($values as $value) : ?>
											<option value="<?php echo $value; ?>" <?php echo $content && $content->getValue() == $value ? "selected" : ""; ?>><?php echo $value; ?></option>
										<?php endforeach; ?>
									</select>
								<?php }
								break;
							}
						?>
						<br clear="all" />
					<?php endforeach; ?>
					<script>
						jQuery(document).ready(function(){
							jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);
							jQuery(".picker_field").datepicker({
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
										jQuery("#date_field_" + self.attr("rel")).html(dateText);
								}
							});
						});
					</script>
					<br clear="all" />
				</div>
			<?php endif; ?>

			<br clear="all" />
			<br clear="all" />

			<label style="width: 140px;"></label>
			<input type="checkbox" name="recurs" id="recurs" class="left" style="margin-right: 5px;" />
			<label for="recurs" style="width: auto;"><?php echo __("Recursive modification"); ?></label>
			<a class="tooltip" name="<?php echo __("All files in this folder and subfolders will be updated."); ?>"><img src="<?php echo image_path("help.gif"); ?>" class='left' style="vertical-align: middle; margin-left: 0px; margin-top: 7px;" /></a>
		</form>

		<br clear="all" />

		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>

				<a href="#" onClick="jQuery('#form_default').submit();" class="button btnBS"><span><?php echo __("Confirm")?></span></a>

		</div>
	</div>
</div>
<script type="text/javascript">
function onFocus(obj){
  if(obj.value.toLowerCase() == "<?php echo __("address, city, region")?>"){
    obj.value = "";
  }
}

function onBlur(obj){
  if(obj.value == ""){
    obj.value = "<?php echo __("Address, City, Region")?>";
  }
}

function searchLocation(){
	searchLocationL(jQuery('#data_address').val());
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

function deleteTag(object)
{
	var title = jQuery(object).find("span").html();
	var tags = jQuery("#tags_input").val().split("|");
	var tmp = "";

	for(var i = 0; i < tags.length; i++)
	{
		if(tags[i] != title && tags[i] != "")
			tmp += tags[i] + "|";
	}

	jQuery("#tags_input").val(tmp);
	jQuery(object).remove();
}

function loadMap()
{
	jQuery.getScript("/js/leaflet/key.js");
	jQuery.getScript("/js/leaflet/leaflet.core.min.js", function(data, textStatus, jqxhr) {
		jQuery.getScript("/js/leaflet/leaflet.min.js", function(data, textStatus, jqxhr) {
			initializeL('map_canvas', '<?php echo !$folder->getLat() && !$folder->getLng() ? $sf_user->getInstance()->getCountry()->getTitle() : ""; ?>', '<?php echo $folder->getLat()?>', '<?php echo $folder->getLng(); ?>', 'add');
		});
	});
}

var id;
jQuery(document).ready(function() {
	tooltip();
	id = setTimeout(loadMap, 500);

	jQuery(window).bind("resize", function() {
		clearTimeout(id);
		id = setTimeout(loadMap, 500);
	});

	jQuery('#data_address').bind('keydown', function(event) {
		var code = (event.keyCode ? event.keyCode : event.which);

		if(code == 13)
			searchLocation();
	});

	jQuery('#addTagRemote').bind("click", function() {
		if(jQuery.trim(jQuery('#tag_title').val()).length > 0 && jQuery('#tag_title').val() != "<?php echo __("Add tag ...")?>")
		{
			jQuery("#tags_input").val(jQuery("#tags_input").val() + jQuery('#tag_title').val() + "|");

			var a = jQuery("<a href='#' onclick='deleteTag(this);'><span>" + jQuery('#tag_title').val() + "</span><em id=''></em></a>");
			jQuery('#file_tags .list_tags').append(a);
			jQuery('#tag_title').val("<?php echo __("Add tag ..."); ?>");
			jQuery('#tag_title').addClass("nc");
		}
	});

	jQuery("#tag_title").bind("keydown", function(event) {
		var code = (event.keyCode ? event.keyCode : event.which);

		if(code == 13)
		{
			jQuery('#addTagRemote').trigger("click");
			jQuery("#tag_title").blur();
		}
	});

	jQuery("#tag_title").autocomplete({
		source: '<?php echo url_for("tag/fetchTags"); ?>',
		minLength: 3
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

		jQuery("#tree_thesaurus a.addThesaurus").live("click", function() {
			var current = jQuery(this).parent().parent();
			var new_object = jQuery(this).parent().clone().prependTo(current).css({'position' : 'absolute'});

			if(jQuery("#file_tags .list_tags").children('a:last').length > 0)
				var to = jQuery("#file_tags .list_tags").children('a:last');
			else
				var to = jQuery("#file_tags");

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

					jQuery("#tags_input").val(jQuery("#tags_input").val() + jQuery(current).find("span:first").text() + "|");

					var a = jQuery("<a href='#' onclick='deleteTag(this);'><span>" + jQuery(current).find("span:first").text() + "</span><em id=''></em></a>");
					jQuery('#file_tags .list_tags').append(a);
				});
		});
	<?php endif; ?>
})
</script>
