<div class="container margin-file">
	<?php if ($roleGroup <= RolePeer::__ADMIN && $file->getState() == FilePeer::__STATE_WAITING_VALIDATE): ?>
		<div class="row">
			<div class="span12">
				<div class="validate-file text-center">
					<?php echo __("This file is pending validation."); ?>
					<?php echo __("Click on \"Validate\" to add file or on \"Delete\" to remove this file."); ?>
					<br />
					<a href="javascript: void(0);" class="custom-button mini accept-waiting" data-type="validation" data-state="<?php echo FileWaitingPeer::__STATE_VALIDATE; ?>"><i class="icon-ok"></i> <?php echo __("Validate"); ?></a>
					<a href="javascript: void(0);" class="custom-button mini cancel deny-waiting" data-type="validation" data-state="<?php echo FileWaitingPeer::__STATE_DELETE; ?>"><i class="icon-trash"></i> <?php echo __("Delete"); ?></a>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($roleGroup <= RolePeer::__ADMIN && FileWaitingPeer::retrieveByFileIdAndType($file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE)): ?>
		<div class="row">
			<div class="span12">
				<div class="validate-file text-center">
					<?php echo __("This file is pending deletion."); ?>
					<?php echo __("Click on \"Delete\" to validate deletion of file or on \"Cancel\" to restore this file."); ?>
					<br />
					<a href="javascript: void(0);" class="custom-button mini accept-waiting" data-type="deletion" data-state="<?php echo FileWaitingPeer::__STATE_DELETE; ?>"><i class="icon-trash"></i> <?php echo __("Delete"); ?></a>
					<a href="javascript: void(0);" class="custom-button mini cancel deny-waiting" data-type="deletion" data-state="<?php echo FileWaitingPeer::__STATE_VALIDATE; ?>"><i class="icon-ban-circle"></i> <?php echo __("Cancel"); ?>
					</a>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class="row">
		<div class="span9">
			<div align="center" id="<?php echo $file->getId()?>" class="file-div file-primary">
				<?php
					switch($file->getType())
					{
						case FilePeer::__TYPE_PHOTO :
						{
							if ($file->exists())
							{
								switch($file->getExtention())
								{
									case 'swf':
									{ ?>
										<object type="application/x-shockwave-flash" data="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "original")); ?>" width="600" height="400">
											<param name="movie" value="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "original")); ?>" />
											<param name="wmode" value="transparent" />
											<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "web")); ?>" class="main" alt="<?php echo $file?>" />
										</object>
									<?php }
									break;

									default:
									{
										if($file->getThumbMobW() && $file->existsThumbMobW())
											$img = path("@file_thumbnail", array("id" => $file->getId(), "format" => "mobw"));
										else
											$img = path("@file_thumbnail", array("id" => $file->getId(), "format" => "web"));
									?>
										<div class="resize">
											<i class="icon-resize-full"></i>
										</div>

										<a href="javascript: void(0);"><img class="main" data-image-id="<?php echo $file->getId(); ?>" src="<?php echo $img; ?>" alt="<?php echo $file; ?>" /></a>

										<div id="cropping">
											<?php echo __('Select the area to crop and click "Save crop" to finish.'); ?>
											<br clear="all" />
											<a href="javascript: void(0);" class="button btnBS" id="valid_crop"><span><?php echo __("Save crop"); ?></span></a>
											<a href="javascript: void(0);" class="button btnBSG" id="cancel_crop" style="margin-left: 10px;"><span><?php echo __("Cancel"); ?></span></a>
											<input type="hidden" name="h" id="h" value="0" />
											<input type="hidden" name="w" id="w" value="0" />
											<input type="hidden" name="x" id="x" value="0" />
											<input type="hidden" name="y" id="y" value="0" />
										</div>
									<?php }
									break;
								}
							}
						}
						break;

						case FilePeer::__TYPE_VIDEO :
						{
							$videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());

							if(in_array($file->getExtention(), $videoFormat))
							{
								if($file->existsVideoMp4() && $file->existsVideoWebm()) : ?>
									<video class="video-js vjs-default-skin" controls preload="auto" width="640" height="385" poster="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "poster")); ?>" title="<?php echo $file; ?>" data-setup=''>
										<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "webm")); ?>" type="video/webm" />
										<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "mp4")); ?>" type="video/mp4" />
									</video>
									<script type="text/javascript">
										jQuery(document).ready(function() {
											videojs.options.flash.swf = "/flash/videojs/video-js.swf";
										});
									</script>
								<?php else: ?>
									<div class='no-player'>
										<div class='text'>
											<h4><?php echo __("Video encoding in progress"); ?>...</h4>
										</div>
										<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "poster")); ?>" style='height: 385px;' class="main" alt="<?php echo $file?>" />
									</div>
								<?php endif;
							}
						}
						break;

						case FilePeer::__TYPE_AUDIO :
						{
							$audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());

							if(in_array($file->getExtention(), $audioFormat)) :
								if($file->existsAudioWav() && $file->existsAudioMp3()) : ?>
									<audio controls>
										<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "mp3")); ?>">
										<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "wav")); ?>">
									</audio>
								<?php else: ?>
									<div class='no-player'>
										<div class='text'>
											<h4><?php echo __("Media encoding in progress"); ?>...</h4>
										</div>
										<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "web")); ?>" style='height: 385px;' class="main" alt="<?php echo $file?>" />
									</div>
								<?php endif;
							endif;
						}
						break;

						case FilePeer::__TYPE_DOCUMENT : ?>
							<iframe src="<?php echo url_for("file/viewDocument?file_id=".$file->getId()); ?>" style="width: 760px; height: 640px; border: 0px;" frameborder="0"></iframe>
						<?php break;
					}
				?>
			</div>

			<div class="clearfix"></div>

			<?php $menuActive = false; ?>
			<div id="nav-bottom-file">
				<ul>
					<?php if($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__COMMENT, RolePeer::__READER)) : ?>
						<li class="first"><a href="javascript: void(0);" class="active" rel="content-comment"><?php echo __("Comments"); ?></a></li>
						<?php $menuActive = true; ?>
					<?php endif; ?>

					<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_PERMALINK)) : ?>
						<?php if($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__PERMALINK_FILE) && $sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__SHARE, RolePeer::__READER)) : ?>
							<li class="in"><a <?php echo $file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH ? "class='inactive tooltip' name='".__("Unauthorized disclosure")."'" : (!$menuActive ? "class='active'" : ""); ?> href="javascript: void(0);" rel="content-share"><?php echo __("Sharing"); ?></a></li>
							<?php $menuActive = true; ?>
						<?php endif; ?>
					<?php endif; ?>

					<li class="in"><a href="javascript: void(0);" rel="content-tags" <?php echo !$menuActive ? "class='active'" : ""; ?>><?php echo __("Tags"); ?></a></li>
					<?php $menuActive = true; ?>

					<li class="last"><a href="javascript: void(0);" rel="content-related" <?php echo !$menuActive ? "class='active'" : ""; ?>><?php echo __("Related medias"); ?></a></li>
				</ul>
			</div>

			<div id="content-bottom-file">
				<?php if($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__COMMENT, RolePeer::__READER)) : ?>
					<div id="content-comment" class="content-bottom" data-position="1">
						<div class="comment_file" style="min-height: 300px;">
							<?php include_partial("comment/comment", array("file"=>$file));?>
						</div>
					</div>
				<?php endif; ?>

				<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_PERMALINK)) : ?>
					<?php if($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__PERMALINK_FILE) && $sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__SHARE, RolePeer::__READER)) : ?>
						<?php if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH) : ?>
							<div id="content-share" class="content-bottom" style="display: none;" data-position="2">
								<div class="left" style="min-height: 300px; width: 100%;" id="permalink_container">
									<?php include_component('permalink', 'show', array('file' => $file));?>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>

				<div id="content-tags" class="content-bottom" style="display: none;" data-position="4">
					<div class="comment_file" style="min-height: 300px;">
						<?php include_partial("file/tags", array("file" => $file, "role" => $role)); ?>
					</div>

					<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)) : ?>
						<?php if($role): ?>
							<div class="comment_file" style="margin-left: 2%; min-height: 300px;">
								<?php include_partial("file/thesaurus", array("file" => $file)); ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<div id="content-related" class="content-bottom" style="display: none;" data-position="5">
					<div class="comment_file" id="related_container" style="min-height: 300px;">
						<?php include_partial("file/related", array("file" => $file, "role" => $role)); ?>
					</div>
					<div class="comment_file" style="margin-left: 2%; min-height: 300px;">
						<div id="choice_media" style="display: none;">
							<div style="width: 100%; text-align: center;">
								<img src="<?php echo image_path("loader-rotate.gif"); ?>" />
							</div>
						</div>
						<div id="buttons_choice_media" style="display: none;">
							<a href="javascript:void(0);" class="button btnBS" id="validate_choice">
								<span><?php echo __("Validate")?></span>
							</a>
							<a href="javascript:void(0);" class="button btnBSG" id="cancel_choice">
								<span><?php echo __("Cancel")?></span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="span3">
			<div class="right-column-new">
				<?php include_partial("file/sidebar", array("file" => $file, "role" => $role));?>

				<?php if((!$sf_user->haveAccessModule(ModulePeer::__MOD_HIDE_COPYRIGHTS)) || $sf_user->isAdmin()) : ?>
					<div class="cat-right">
						<a href="javascript: void(0);" class="deploy-cat">
							<i class="icon-cog"></i> <?php echo __("Rights and uses"); ?><i class="icon-chevron-down right"></i>
						</a>
						<div class="content" style="display: none;">
							<?php if($role): ?>
								<?php include_partial("file/presets", array("file" => $file, "role" => $role)); ?>
							<?php endif; ?>

							<?php $customer = $sf_user->getInstance()->getCustomer(); ?>
							<?php if($role) : ?>
								<div class="rub" style="width: 100%;">
									<div class="label-right">
										<?php echo __("Licence"); ?> :
									</div>
								</div>

								<br clear="all" />

								<?php $i = 0; ?>
								<?php foreach($licences as $licence) : ?>
									<div class="rub" style="width: 100%;">
										<div class="value-right" style="width: 100%;">
											<input type="radio" name="licences" id="licences_<?php echo $licence->getId(); ?>" value="<?php echo $licence->getId(); ?>" <?php echo $licence->getId() == $file->getLicenceId() ? "checked" : ""; ?> class="left" style="margin-right: 5px; margin-top: 0px; margin-bottom: 0px;" /> 
											<label class="left" for="licences_<?php echo $licence->getId(); ?>">
												<?php echo $licence->getTitle(); ?> 
												<?php if($licence->getDescription()) : ?>
													<a class="tooltipLeft" href="javascript: void(0);" name="<?php echo str_replace("$*COMPANY*$", $customer->getCompany(), $licence->getDescription()); ?>"><i class="icon-question-sign"></i></a>
												<?php endif; ?>
												<i class="icon-ok-sign" style="display: none;"></i>
											</label>
										</div>
									</div>

									<br clear="all" />
									<?php $i++; ?>
								<?php endforeach; ?>
							<?php else: ?>
								<?php $licence = $file->getLicenceId() ? $file->getLicence() : ""; ?>
								<div class="rub" style="width: 100%;">
									<div class="label-right">
										<?php echo __("Licence"); ?> :
									</div>

									<div class="value-right" style="width: 50%;">
										<?php if(!empty($licence)) : ?>
											<?php echo $licence->getTitle; ?>
											<?php if($licence->getDescription()) : ?>
												<a class="tooltipLeft" href="javascript: void(0);" name="<?php echo str_replace("$*COMPANY*$", $customer->getCompany(), $licence->getDescription()); ?>"><i class="icon-question-sign"></i></a>
											<?php endif; ?>
										<?php else: ?>
											<span class='text'><?php echo __("To inform"); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>

							<br clear="all" />

							<div id="creative_commons" <?php echo $file->getLicenceId() != LicencePeer::__CREATIVE_COMMONS ? "style='display: none;'" : ""; ?>>
								<?php include_partial("file/creativeCommonsShow", array("file" => $file, "role" => $role)); ?>
							</div>

							<div id="copyrights">
								<?php include_partial("file/copyrightsShow", array("file" => $file, "role" => $role)); ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if($file->getType() != FilePeer::__TYPE_DOCUMENT) : ?>
					<div class="cat-right">
						<a href="javascript: void(0);" class="deploy-cat">
							<i class="icon-camera"></i> <?php echo __("Geolocation"); ?><i class="icon-chevron-down right"></i>
						</a>
						<div class="content" style="display: none;">
							<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
								<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
									<?php
									if($exif = ExifPeer::getTag("DateTimeOriginal", $file->getId()))
										$shootingDate = $exif->getValue();
									elseif($iptc = IptcPeer::getTag("Date Created", $file->getId()))
										$shootingDate = $iptc->getValue();
									else
										$shootingDate = "<span class='text'>".__("To inform")."</span>";
									?>
								<?php endif; ?>

								<?php if($role) : ?>
									<div class="rub">
										<div class='label-right'><?php echo __("Date")?> : </div><div class="value-right"><span id='shooting-date'><?php echo $shootingDate; ?></span><input type="hidden" name="datepicker" id="datepicker" value="<?php echo $shootingDate; ?>" /></div>
									</div>
									<script>
										jQuery(function() {
											jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);
											jQuery("#datepicker").datetimepicker({
												showOn: "button",
												buttonImage: "<?php echo image_path("icons/calendar.png"); ?>",
												buttonImageOnly: true,
												timeText: '<?php echo __("Time"); ?>',
												hourText: '<?php echo __("Hour"); ?>',
												minuteText: '<?php echo __("Minute"); ?>',
												secondText: '<?php echo __("Second"); ?>',
												closeText: '<?php echo __("Save"); ?>',
												dateFormat: 'dd/mm/yy',
												timeFormat: 'hh:mm:ss',
												firstDay: 1,
												gotoCurrent: true,
												showButtonPanel: false,
												onClose: function(dateText, inst) {
													if(dateText != "")
													{
														jQuery.post(
															"<?php echo url_for("file/field"); ?>",
															{ id: "<?php echo $file->getId(); ?>", field: "shooting-date", value: dateText },
															function(data) {
																jQuery("#shooting-date").html(dateText);
															}
														);
													}
												}
											});
										});
									</script>
								<?php else: ?>
									<div class="rub">
										<div class="label-right"><?php echo __("Date")?> : </div> <div class="value-right"><?php echo $shootingDate; ?></div>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_GEOLOCALISATION)) : ?>
								<?php if($file->getLng() && $file->getLat()) : ?>
									<?php $locationName = showLocationName($file->getLat(), $file->getLng()); ?>
									<?php $locations = GeolocationPeer::retrieveByObjectIdAndObjectTypeAndCulture($file->getId(), GeolocationPeer::__TYPE_FILE, $sf_user->getCulture()); ?>
									<?php $hover = ""; ?>
									<?php foreach($locations as $location) : ?>
										<?php $hover .= "<b>".$location->getGeolocationType()->getTitle()."</b> : ".$location->getValue()."<br />"; ?>
									<?php endforeach; ?>

									<div class="rub">
										<div class="label-right"><?php echo __("Location")?> : </div>
										<div class="value-right">
											<?php if($role) : ?>
												<a href="<?php echo url_for("map/singleFile?file_id=".$file->getId()); ?>" class="link_right_col tooltipLeft" <?php echo !empty($hover) ? 'name="'.$hover.'"' : ''; ?> rel="facebox"><?php echo $locationName; ?></a>
											<?php else: ?>
												<?php echo $locationName; ?>
											<?php endif; ?>
										</div>
									</div>
								<?php else : ?>
									<div class="rub">
										<div class="label-right"><?php echo __("Location")?> : </div>
										<div class="value-right">
											<?php if($role) : ?>
												<a href="<?php echo url_for("map/singleFile?file_id=".$file->getId()); ?>" class="link_right_col" rel="facebox"><?php echo __("To inform"); ?></a>
											<?php else: ?>
												<?php echo __("To inform"); ?>
											<?php endif; ?>
										</div>
									</div>
								<?php endif; ?>
								<br clear="all">
							<?php else: ?>
								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("Make", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Camera")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("Model", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Camera model")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("COMPUTED.ApertureFNumber", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Focal")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("ExposureTime", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Exposure time")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?> <?php echo __("seconds"); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("ISOSpeedRatings", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("ISO speed")?> : </div> <div class="value-right">ISO-<?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("ExposureMode", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Compensation")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("FocalLength", $file->getId())) : ?>
								<?php $temp = explode("/", $exif->getValue()); ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Focal length")?> : </div> <div class="value-right"><?php echo round($temp[0] / $temp[1]); ?> <?php echo __("mm"); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("MaxApertureValue", $file->getId())) : ?>
								<?php $temp = explode("/", $exif->getValue()); ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Maxixum aperture")?> : </div> <div class="value-right"><?php echo $temp[0] / $temp[1]; ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("MeteringMode", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Metering Mode")?> : </div> 
									<div class="value-right">
										<?php
											switch($exif->getValue()) :
												case 0: echo __("Unknown"); break;
												case 1: echo __("Average"); break;
												case 2: echo __("CenterWeightedAverage"); break;
												case 3: echo __("Spot"); break;
												case 4: echo __("MultiSpot"); break;
												case 5: echo __("Pattern"); break;
												case 6: echo __("Partial"); break;
												case 255: echo __("Other"); break;
											endswitch;
										?>
									</div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("SubjectDistanceRange", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Subject distance range")?> : </div>
									<div class="value-right">
										<?php
											switch($exif->getValue()) :
												case 0: echo __("Unknown"); break;
												case 1: echo __("Macro"); break;
												case 2: echo __("Close view"); break;
												case 3: echo __("Distant view"); break;
											endswitch;
										?>
									</div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("Flash", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Flash mode")?> : </div> 
									<div class="value-right">
										<?php
											switch($exif->getValue()) :
												case 0: echo __("Flash did not fire"); break;
												case 1: echo __("Flash fired"); break;
												case 5: echo __("Strobe return light not detected"); break;
												case 7: echo __("Strobe return light detected"); break;
												case 8: echo __("Flash did not fire, compulsory flash mode"); break;
												case 9: echo __("Flash fired, compulsory flash mode"); break;
												case 13: echo __("Flash fired, compulsory flash mode, return light not detected"); break;
												case 15: echo __("Flash fired, compulsory flash mode, return light detected"); break;
												case 16: echo __("Flash did not fire, compulsory flash mode"); break;
												case 20: echo __("Flash did not fire, compulsory flash mode, return light not detected"); break;
												case 24: echo __("Flash did not fire, auto mode"); break;
												case 25: echo __("Flash fired, auto mode"); break;
												case 29: echo __("Flash fired, auto mode, return light not detected"); break;
												case 31: echo __("Flash fired, auto mode, return light detected"); break;
												case 32: echo __("No flash function"); break;
												case 48: echo __("Flash did not fire, no flash function"); break;
												case 65: echo __("Flash fired, red-eye reduction mode"); break;
												case 69: echo __("Flash fired, red-eye reduction mode, return light not detected"); break;
												case 71: echo __("Flash fired, red-eye reduction mode, return light detected"); break;
												case 73: echo __("Flash fired, compulsory flash mode, red-eye reduction mode"); break;
												case 77: echo __("Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected"); break;
												case 79: echo __("Flash fired, compulsory flash mode, red-eye reduction mode, return light detected"); break;
												case 88: echo __("Flash did not fire, auto mode, red-eye reduction mode"); break;
												case 89: echo __("Flash fired, auto mode, red-eye reduction mode"); break;
												case 93: echo __("Flash fired, auto mode, return light not detected, red-eye reduction mode"); break;
												case 95: echo __("Flash fired, auto mode, return light detected, red-eye reduction mode"); break;
											endswitch;
										?>
									</div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("FlashEnergy", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Flash energy")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>

							<?php if($exif = ExifPeer::getTag("FocalLengthIn35mmFilm", $file->getId())) : ?>
								<div class="rub">
									<div class="label-right"><?php echo __("Focal lenght 35mm")?> : </div> <div class="value-right"><?php echo $exif->getValue(); ?></div>
								</div>

								<br clear="all">
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
					<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_META_EXIF)): ?>
						<div class="cat-right">
							<a href="javascript: void(0);" class="deploy-cat">
								<i class="icon-bar-chart"></i> <?php echo __("EXIF Data"); ?><i class="icon-chevron-down right"></i>
							</a>
							<div class="content" style="display: none;">
								<?php
									$exif = ExifPeer::getAllTags($file->getId());

									if($exif)
									{
										foreach($exif as $metaData)
										{
											if(!strstr($metaData->getValue(), "??") && !strstr($metaData->getTitle(), "UndefinedTag"))
											{
												echo '<div class="rub">';
												echo '		<div class="label-right">'.$metaData->getTitle().' : </div>';
												echo '		<div class="value-right">'.wordwrap($metaData->getValue(), 30, "<br />\n", true).'</div>';
												echo '</div>';

												echo '<br clear="all">';
											}
										}
									}
									else
									{
										echo '<div class="rub">';
										echo '		<div class="value-right">'.__("No exif metadata collected for this file.").'</div>';
										echo '</div>';

										echo '<br clear="all">';
									}
								?>
							</div>
						</div>
					<?php endif; ?>

					<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_META_IPTC)): ?>
						<div class="cat-right">
							<a href="javascript: void(0);" class="deploy-cat">
								<i class="icon-bar-chart"></i> <?php echo __("IPTC Data"); ?><i class="icon-chevron-down right"></i>
							</a>
							<div class="content" style="display: none;">
								<?php
									$iptc = IptcPeer::getAllTags($file->getId());

									if($iptc)
									{
										foreach($iptc as $metaData)
										{
											if(!strstr($metaData->getValue(), "??") && !strstr($metaData->getTitle(), "UndefinedTag"))
											{
												echo '<div class="rub">';
												echo '		<div class="label-right">'.$metaData->getTitle().' : </div>';
												echo '		<div class="value-right">'.wordwrap($metaData->getValue(), 30, "<br />\n", true).'</div>';
												echo '</div>';

												echo '<br clear="all">';
											}
										}
									}
									else
									{
										echo '<div class="rub">';
										echo '		<div class="value-right">'.__("No iptc metadata collected for this file.").'</div>';
										echo '</div>';

										echo '<br clear="all">';
									}
								?>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if($role) : ?>
					<div class="cat-right">
						<a href="javascript: void(0);" class="deploy-cat <?php echo $file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH ? "inactive tooltipLeft" : ""; ?>" <?php echo $file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH ? "name='".__("Unauthorized disclosure")."'" : ""; ?>>
							<i class="icon-picture"></i> <?php echo __("File thumbnail"); ?><i class="icon-chevron-down right"></i>
						</a>
						<?php if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH) : ?>
							<div class="content" style="display: none;">
								<div class="rub">
									<table>
										<tr>
											<td>
												<div class="label-right no-margin">
													<input type="checkbox" name="folder-cover" id="folder-cover" style="margin: 0px; padding: 0px;" <?php echo $file->getFolderCover() ? "checked" : ""; ?> />
												</div>
											</td>
											<td>
												<div class="label-right no-margin">
													<label for="folder-cover"><?php echo __("Folder cover of"); ?> "<a href='<?php echo url_for("folder/show?id=".$file->getFolderId()); ?>' class='link_right_col'><?php echo $file->getFolder(); ?></a>"</label>
													<span class="flag_save left" id="ok_folder_cover" style="margin-left: 0px;"><img src="<?php echo image_path("icons/accept.png"); ?>" /></span>
												</div>
											</td>
										</tr>
									</table>
								</div>

								<br clear="all" />

								<div class="rub">
									<table>
										<tr>
											<td>
												<div class="label-right no-margin">
													<input type="checkbox" name="groupe-cover" id="groupe-cover" style="margin: 0px; padding: 0px;" <?php echo $file->getGroupeCover() ? "checked" : ""; ?> />
												</div>
											</td>
											<td>
												<div class="label-right no-margin">
													<label for="groupe-cover"><?php echo __("Groupe cover of"); ?> "<a href='<?php echo url_for("groupe/show?id=".$file->getGroupeId()); ?>' class='link_right_col'><?php echo $file->getGroupe(); ?></a>"</label>
													<span class="flag_save left" id="ok_groupe_cover" style="margin-left: 0px;"><img src="<?php echo image_path("icons/accept.png"); ?>" /></span>
												</div>
											</td>
										</tr>
									</table>
								</div>

								<br clear="all" />
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>
<div id="print_<?php echo $file->getId();?>" style="display:none;">
	<?php include_partial("file/print", array("file"=>$file))?>
</div>
<div id="custom_download" class="dialog"></div>
<div id="full-screen">
	<div class="container">
		<div class="row">
			
		</div>
	</div>
</div>
<script>
	var jCrop = "";
	var creation = false;

	function rotatePicture(angle, file_id)
	{
		jQuery("div#" + file_id + ".file-div").html("<i class='icon-refresh icon-spin icon-3x'></i>");

		jQuery.post(
			"<?php echo url_for("file/rotate"); ?>",
			{ id: file_id, angle: angle },
			function(data) {
				window.location.reload();
			}
		);
	}

	function displayFullScreen()
	{
		var temp = jQuery("<div class='span12'></div>");
		jQuery("body").append(temp);
		var width = temp.width();
		temp.remove();
		var height = jQuery(window).height();

		jQuery.post(
			"<?php echo url_for("file/fullscreen"); ?>",
			{ id: "<?php echo $file->getId(); ?>", height: height, width: width },
			function(data) {
				jQuery("body").addClass("no-scroll");
				jQuery("body").append("<div class='overlay-full'></div>");
				jQuery(".overlay-full").fadeIn(400, function() {
					jQuery("body").append("<div id='full_screen'>" + data + "</div>");
					jQuery("#full_screen").fadeIn(400);
				});
			}
		);
	}

	function displayWaintingFiles(state, type)
	{
		jQuery(".validate-file").fadeOut(400, function() {
			var $this = jQuery(this);
			var message = "";

			if (state == <?php echo FileWaitingPeer::__STATE_VALIDATE; ?> && type == "validation") {
				message = "<?php echo __("File has been accepted."); ?>";
			}

			if ((state == <?php echo FileWaitingPeer::__STATE_DELETE; ?> && type == "validation") ||
					(state == <?php echo FileWaitingPeer::__STATE_DELETE; ?> && type == "deletion")) {
				message = "<?php echo __("File has been deleted."); ?>";
			}

			if (state == <?php echo FileWaitingPeer::__STATE_VALIDATE; ?> && type == "deletion") {
				message = "<?php echo __("File has been restored."); ?>";
			}

			$this.html("<div class='text-center'>" + message + "</div>");

			$this.fadeIn(400, function() {
				setTimeout(function() {
					switch (state) {
						case <?php echo FileWaitingPeer::__STATE_VALIDATE; ?>:
							window.location.reload();
						break;

						case <?php echo FileWaitingPeer::__STATE_DELETE; ?>:
							window.location.href = "<?php echo url_for("folder/show?id=".$file->getFolderId()); ?>";
						break;
					}
				}, 1000);
			});
		});
	}

	jQuery(document).ready(function(){
		jQuery(".accept-waiting").on("click", function() {
			var state = jQuery(this).data("state");
			var type = jQuery(this).data("type");

			jQuery.post(
				"<?php echo url_for("file/accept"); ?>",
				{ id: "<?php echo $file->getId(); ?>", type: state },
				function(data) {
					if (data.errorCode <= 0) {
						displayWaintingFiles(state, type);
					}
				},
				"json"
			);
		});

		jQuery(".deny-waiting").on("click", function() {
			var state = jQuery(this).data("state");
			var type = jQuery(this).data("type");

			jQuery.post(
				"<?php echo url_for("file/deny"); ?>",
				{ id: "<?php echo $file->getId(); ?>", type: state },
				function(data) {
					if (data.errorCode <= 0) {
						displayWaintingFiles(state, type);
					}
				},
				"json"
			);
		});

		tooltip();
		tooltipLeft();

		var offset = ((jQuery("img.main").closest(".file-div").width() - jQuery("img.main").width()) / 2) + jQuery("img.main").width() - 40;
		jQuery(".resize").css("margin-left", offset + "px");

		jQuery("img.main").parent().bind("click", function() {
			if(!jQuery("#info_identify").is(":visible"))
				displayFullScreen();
		});

		jQuery(".resize").bind("click", function() {
			displayFullScreen();
		});

		jQuery(".button-custom-download").bind("click", function() {
			jQuery("#custom_download").dialog({
				title: "<span class='first-title'><?php echo __("Custom download"); ?></span>",
				resizable: false,
				draggable: false,
				modal: true,
				width: 450,
				height: 350,
				show: 'fade',
				hide: 'fade',
				open: function(event, ui) { 
					jQuery(this).html('<div style="text-align: center; margin-top: 15px;"><img src="<?php echo image_path("loader-rotate.gif"); ?>" /></div>');

					jQuery.post(
						"<?php echo url_for("file/customDownload"); ?>",
						{ id: <?php echo $file->getId(); ?> },
						function(data) {
							jQuery("#custom_download").fadeOut(200, function() {
								jQuery("#custom_download").html(data);
								jQuery("#custom_download").fadeIn()
							});
						}
					);
				},
				close: function(event, ui) { 
					jQuery(this).dialog("destroy");
				},
				buttons: {
					"<?php echo __("Download"); ?>": function() {
						var error = false;

						if(!creation)
						{
							jQuery("#error").fadeOut(200, function() {
								jQuery("#error").html("");

								if(jQuery.trim(jQuery('#format').val()).length <= 0)
								{
									error = true;
									jQuery("#error").append("<?php echo __("Please select format."); ?><br />")
								}

								if(jQuery.trim(jQuery('#width').val()).length <= 0 || jQuery('#width').val() == 0)
								{
									error = true;
									jQuery("#error").append("<?php echo __("Please enter correct width."); ?><br />")
								}

								if(jQuery.trim(jQuery('#height').val()).length <= 0 || jQuery('#height').val() == 0)
								{
									error = true;
									jQuery("#error").append("<?php echo __("Please enter correct height."); ?><br />")
								}

								if(error)
									jQuery("#error").fadeIn();
								else
								{
									creation = true;
									jQuery("#error").removeClass("require_field").addClass("text").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' style='vertical-align: middle; margin-right: 5px;' /><?php echo $file->getType() == FilePeer::__TYPE_PHOTO ? __("Creation of picture in progress...") : __("Creation of video in progress..."); ?></div>").fadeIn(200, function() {
										jQuery("#form_download").attr("action", "<?php echo url_for("download/customDownload"); ?>?id=<?php echo $file->getId(); ?>&format=" + jQuery("#format").val() + "&width=" + jQuery('#width').val() + "&height=" + jQuery('#height').val());

										jQuery('<iframe name="iframe_download" id="iframe_download" style="display: none;" src="javascript: return false;"></iframe>').load(function() {
											if(!jQuery.browser.webkit)
											{
												var html = jQuery(this).contents().find("body").html();

												if(html == "")
												{
													creation = false;
													jQuery("#error").fadeOut(200, function() {
														jQuery(this).removeClass("text").addClass("require_field").html("");
														// jQuery("#iframe_download").remove();
														jQuery("#custom_download").dialog("close");
													});
												}
											}
										})
										.appendTo('body');

										if(jQuery.browser.msie || jQuery.browser.webkit)
										{
											jQuery("#iframe_download").attr("src", "about:blank");

											jQuery("#iframe_download").ready(function() {
												var html = jQuery(this).contents().find("body").html();

												if(html != "")
												{
													creation = false;
													jQuery("#error").delay(500).fadeOut(200, function() {
														jQuery(this).removeClass("text").addClass("require_field").html("");
														// jQuery("#iframe_download").remove();
														jQuery("#custom_download").dialog("close");
													});
												}
											});
										}
										else
											jQuery("#iframe_download").attr("src", "javascript: return false;");

										jQuery("#form_download").submit();
									});
								}
							});
						}
					},
					"<?php echo ucfirst(__("Close")); ?>": function() {
						jQuery(this).dialog("close");
					}
				}
			});
		});

		jQuery(".bread.favorites").live("click", function() {
			var object = jQuery(this);

			jQuery.post(
				"<?php echo url_for("favorite/add"); ?>",
				{ type: "<?php echo FavoritesPeer::__TYPE_FILE; ?>", id: "<?php echo $file->getId(); ?>" },
				function(data) {
					services.notification.loading();
					
					if(data.errorCode == 0)
					{
						services.notification.success(__("The file has been added to my favorites."));
						
						object.html("<i class='icon-star'></i> <?php echo __("Remove from favorites"); ?>");
						object.addClass("unfavorites").removeClass("favorites");
					}
				},
				"json"
			);
		});

		jQuery(".bread.unfavorites").live("click", function() {
			var object = jQuery(this);

			jQuery.post(
				"<?php echo url_for("favorite/delete"); ?>",
				{ type: "<?php echo FavoritesPeer::__TYPE_FILE; ?>", id: "<?php echo $file->getId(); ?>" },
				function(data) {
					services.notification.loading();
					
					if(data.errorCode == 0)
					{
						services.notification.success(__("The file has been removed from my favorites."));
						object.html("<i class='icon-star-empty'></i> <?php echo __("Add to favorites"); ?>");
						object.addClass("favorites").removeClass("unfavorites");
					}
				},
				"json"
			);
		});

		jQuery("#nav-bottom-file a:not(.inactive)").bind("click", function() {
			var div = jQuery(this).attr("rel");

			jQuery("#nav-bottom-file a").removeClass("active");
			jQuery(this).addClass("active");

			jQuery("#content-bottom-file .content-bottom").each(function(index) {
				if(jQuery(this).is(":visible"))
				{
					if(jQuery(this).attr("data-position") > jQuery("#" + div).attr("data-position"))
					{
						jQuery(this).hide("slide", { direction: "right" }, 600, function() {
							jQuery("#" + div).show("slide", { direction: "left" }, 600);
						});
					}
					else
					{
						jQuery(this).hide("slide", { direction: "left" }, 600, function() {
							jQuery("#" + div).show("slide", { direction: "right" }, 600);
						});
					}
				}
			});
		});

		jQuery(".deploy-cat:not(.inactive)").bind("click", function() {
			var div = jQuery(this).parent().find(".content");

			if(div.is(":visible"))
			{
				jQuery(this).removeClass("active");
				jQuery(this).find(".right").removeClass("icon-chevron-up").addClass("icon-chevron-down");
				div.slideUp("slow");
			}
			else
			{
				jQuery(this).addClass("active");
				jQuery(this).find(".right").removeClass("icon-chevron-down").addClass("icon-chevron-up");
				div.slideDown("slow");
			}
		});

		jQuery("#folder-cover").bind("click", function() {
			jQuery.post(
				"<?php echo url_for("file/updateFolderCover"); ?>",
				{ file_id: "<?php echo $file->getId(); ?>", check: jQuery(this).is(":checked") },
				function(data) {
					jQuery("#ok_folder_cover").fadeIn('slow').delay(1000).fadeOut('slow');
				}
			);
		});

		jQuery("#groupe-cover").bind("click", function() {
			jQuery.post(
				"<?php echo url_for("file/saveGroupeCover"); ?>",
				{ file_id: "<?php echo $file->getId(); ?>", check: jQuery(this).is(":checked") },
				function(data) {
					jQuery("#ok_groupe_cover").fadeIn('slow').delay(1000).fadeOut('slow');
				}
			);
		});

		<?php if($role) : ?>
			function bindBorder(settings, object)
			{
				jQuery(object).css('border-color', '#FFFFFF'); 
				jQuery(object).css('background-color', '#FFFFFF'); 
				jQuery('.eotf').bind('mouseover', overTd);
				jQuery('.eotf').bind('mouseout', outTd);
				jQuery(object).css('padding-left', '4px'); 
				jQuery(object).css('padding-right', '4px'); 

				return true;
			}

			function unbindBorder(settings, object)
			{
				jQuery('.eotf').unbind('mouseover');
				jQuery('.eotf').unbind('mouseout');  

				jQuery(object).css('padding', '0px');
				jQuery(object).css('padding-left', '4px');

				return true;
			}

			function bindBorderTitle(settings, object)
			{
				jQuery(object).css('border-color', '#FFFFFF'); 
				jQuery(object).css('background-color', '#FFFFFF'); 
				jQuery('.eotf-title').bind('mouseover', overTd);
				jQuery('.eotf-title').bind('mouseout', outTd);
				jQuery(object).css('padding-left', '4px'); 
				jQuery(object).css('padding-right', '4px'); 

				return true;
			}

			function unbindBorderTitle(settings, object)
			{
				jQuery('.eotf-title').unbind('mouseover');
				jQuery('.eotf-title').unbind('mouseout');  

				jQuery(object).css('padding', '0px');
				jQuery(object).css('padding-left', '4px');

				return true;
			}

			function bindBorderArea(settings, object)
			{
				jQuery(object).css('border-color', '#FFFFFF'); 
				jQuery(object).css('background-color', '#FFFFFF'); 
				jQuery('.eotfarea').bind('mouseover', overTd);
				jQuery('.eotfarea').bind('mouseout', outTd);
				jQuery(object).css('padding', '2px'); 

				return true;
			}

			function unbindBorderArea(settings, object)
			{
				jQuery('.eotfarea').unbind('mouseover');
				jQuery('.eotfarea').unbind('mouseout');  

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

			function hideCreativeCommons()
			{
				jQuery("#creative_commons").fadeOut(200, function() {
					jQuery("#creative_commons").html("");
				});

				if(!jQuery("#copyrights").is(":visible"))
				{
					jQuery("#copyrights").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");
					jQuery("#copyrights").fadeIn(200, function() {
						jQuery.post(
							"<?php echo url_for("file/showCopyrights"); ?>",
							{ file_id: <?php echo $file->getId(); ?>, role: <?php echo $role; ?> },
							function(data) {
								jQuery("#copyrights").fadeOut(200, function() {
									jQuery("#copyrights").html(data);
									jQuery("#copyrights").fadeIn();
								});
							}
						);
					});
				}
			}

			function showCreativeCommons()
			{
				// jQuery("#copyrights").fadeOut(200, function() {
					//jQuery("#copyrights").html("");

					jQuery("#creative_commons").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");
					jQuery("#creative_commons").fadeIn(200, function() {
						jQuery.post(
							"<?php echo url_for("file/showCreativeCommonsShow"); ?>",
							{ file_id: <?php echo $file->getId(); ?>, role: <?php echo $role; ?> },
							function(data) {
								jQuery("#creative_commons").fadeOut(200, function() {
									jQuery("#creative_commons").html(data);
									jQuery("#creative_commons").fadeIn();
								});
							}
						);
					});
				// });
			}

			jQuery(document).ready(function() {
				jQuery(".eotf").editable(
					"<?php echo url_for("file/field"); ?>",
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
						},
						data: function(value, settings) {
							var regexp = new RegExp("(<?php echo strtolower(__("To inform")); ?>)","g");
							var regexp2 = new RegExp("(<?php echo strtolower(__("not specified")); ?>)","g");

							if(regexp.test(value.toLowerCase()) || regexp2.test(value.toLowerCase()))
									return "";

							return value;
						}
					}
				);

				jQuery(".eotf-title").editable(
					"<?php echo url_for("file/field"); ?>",
					{
						indicator: '<?php echo __("Saving");?>...',
						placeholder: '',
						cssclass: 'editable-details-file',
						onedit: unbindBorderTitle,
						onreset: bindBorderTitle,
						onblur: "submit",
						width: "100%",
						callback : function(value, settings) {
							jQuery(this).html(value);
							bindBorderTitle(settings, this);
						},
						data: function(value, settings) {
							var regexp = new RegExp("(<?php echo strtolower(__("To inform")); ?>)","g");
							var regexp2 = new RegExp("(<?php echo strtolower(__("not specified")); ?>)","g");

							if(regexp.test(value.toLowerCase()) || regexp2.test(value.toLowerCase()))
									return "";

							return value;
						}
					}
				);

				jQuery('input[name="licences"]').bind("click", function() {
					var object = jQuery(this);

					jQuery.post(
						"<?php echo url_for("file/field"); ?>",
						{ id: <?php echo $file->getId(); ?>, field: "licence", value: object.val() },
						function(data) {
							object.parent().find(".icon-ok-sign").fadeIn('slow').delay(1000).fadeOut('slow');

							if(object.val() == "<?php echo LicencePeer::__CREATIVE_COMMONS; ?>")
								showCreativeCommons();
							else
								hideCreativeCommons();
						}
					);
				});

				jQuery(".eotfarea").editable(
					"<?php echo url_for("file/field"); ?>",
					{
						type: 'textarea',
						indicator: '<?php echo __("Saving");?>...',
						placeholder: '',
						cssclass: 'editable-details-file',
						onedit: unbindBorderArea,
						onreset: bindBorderArea,
						onblur: "submit",
						width: "100%",
						callback : function(value, settings) {
							jQuery(this).html(value);
							bindBorderArea(settings, this);
						},
						data: function(value, settings) {
							var regexp = new RegExp("(<?php echo strtolower(__("Add a description.")); ?>)","g");

							if(regexp.test(value.toLowerCase()))
									return "";

							var retval = value.replace(/<br\s*\/?>/mg,"");
							return retval;
						}
					}
				);

				jQuery(".eotf").bind("mouseover", overTd);
				jQuery(".eotf-title").bind("mouseover", overTd);
				jQuery(".eotfarea").bind("mouseover", overTd);
				jQuery(".eotf").bind("mouseout", outTd);
				jQuery(".eotf-title").bind("mouseout", outTd);
				jQuery(".eotfarea").bind("mouseout", outTd);
			});
		<?php endif; ?>
	});
</script>
