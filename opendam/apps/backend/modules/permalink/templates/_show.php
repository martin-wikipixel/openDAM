<?php
	$roleGroup = $sf_user->getRole($file->getGroupeId());
	
	if ($roleGroup) {
		if ($roleGroup < RolePeer::__ADMIN) {
			$role = true;
		}
		else {
			if ($roleGroup == RolePeer::__ADMIN) {
				if ($sf_user->hasCredential("admin")) {
					$role = true;
				}
				elseif ($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$role = true;
				}
				elseif ($file->getUserId() == $sf_user->getId()) {
					$role = true;
				}
				else {
					$role = false;
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB)
			{
				if ($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$role = true;
				}
				elseif ($file->getUserId() == $sf_user->getId()) {
					$role = true;
				}
				else {
					$role = false;
				}
			}
			else {
				$role = false;
			}
		}
	}
	else {
		$role = false;
	}

	if(($file->getType() == FilePeer::__TYPE_PHOTO && (!$permalink_original || !$permalink_web)) || ($file->getType() != FilePeer::__TYPE_PHOTO && !$permalink_original))
		$choice = "desactivate";
	else
		$choice = "activate";
?>

<div style="position:relative;">
	<?php if($role) : ?>
		<div class="right">
			<select name="permalink_choice" id="permalink_choice" style="width: 150px;">
				<option value="activate" <?php echo $choice == "activate" ? "selected" : ""; ?>><?php echo __("Activate"); ?></option>
				<option value="desactivate" <?php echo $choice == "desactivate" ? "selected" : ""; ?>><?php echo __("Desactivate"); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<div class="left" style="width: 49%;">
		<?php if($choice == "activate") : ?>
			<div class="rub">
				<div class="label-right no-margin">
					<?php echo __("Original file link"); ?> <a href="javascript: void(0);" class="tooltip" name="<?php echo __("Direct access link to the file to be used for direct download files or the inclusion on a website or a emailing."); ?>"><i class="icon-question-sign"></i></a>
				</div>
			</div>

			<br clear="all" />

			<div class="rub" style="width: 100%;">
				<div class="value-right" style="width: 100%;">
					<div class="add-comment" style="float: left; margin-top: 0px; width: 100%;">
						<div class="textarea-wrapper" style="padding-right: 3px;">
							<input type="text" style="border: 0; padding: 0; margin: 0; height: 30px; width: 100%; font-size: 14px!important; padding-top: 5px; padding-bottom: 5px;" class="input_permalink" name="original_without_authentication" id="original_without_authentication" value="<?php echo url("permalink_show", array("link" => $permalink_original->getLink()))?>" />
						</div>
					</div>

					<?php if($role) : ?>
						<div style="clear: both; width: 100%;">

								<a href="javascript: void(0);" id="regenerate_permalink" class="forgot-password" style="float: right; font-size: 12px;"><?php echo __("Regenerate"); ?></a>

						</div>
					<?php endif; ?>
				</div>
			</div>

			<br clear="all" />

			<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
				<?php if($file->existsThumbWeb()) : ?>
					<?php $size = getimagesize($file->getThumbWebPathname()); ?>
					<div class="rub">
						<div class="label-right no-margin">
							<?php echo __("Low definition file link"); ?> (<?php echo $size[0]." x ".$size[1]; ?>) <a href="javascript: void(0);" class="tooltip" name="<?php echo __("Direct access link to the file to be used for direct download files or the inclusion on a website or a emailing."); ?>"><i class="icon-question-sign"></i></a>
						</div>
					</div>

					<div class="rub" style="width: 100%;">
						<div class="value-right" style="width: 100%;">
							<div class="add-comment" style="float: left; margin-top: 0px; width: 100%;">
								<div class="textarea-wrapper" style="padding-right: 3px;">
									<input type="text" style="border: 0; padding: 0; margin: 0; height: 30px; width: 100%; font-size: 14px!important; padding-top: 5px; padding-bottom: 5px;" class="input_permalink" name="web_without_authentication" id="web_without_authentication" value="<?php echo url("permalink_show", array("link" => $permalink_web->getLink()))?>" />
								</div>
							</div>
						</div>
					</div>

					<br clear="all" />
				<?php endif; ?>
			<?php endif; ?>

			<?php if($file->getType() == FilePeer::__TYPE_VIDEO) : ?>
				<br clear="all" />

				<div class="rub">
					<div class="value-right">
						<a href="javascript: void(0);" id="show-video"><i class="icon-cloud"></i> <?php echo __("Integrate")?></a>
					</div>
				</div>

				<br clear="all" />

				<div class="rub" style="width: 100%;">
					<div class="value-right" style="width: 100%;">
						<div id="integrate_container">
							<?php include_component('permalink', 'video', array('file' => $file));?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<br clear="all" />

			<div class="rub">
				<div class="value-right">
					<a href="javascript: void(0);" id="show-qr-code"><i class="icon-barcode"></i> <?php echo __("QR code")?></a>
				</div>
			</div>

			<br clear="all" />

			<div class="rub" style="width: 100%;">
				<div class="value-right" style="width: 100%;">
					<div id="qr_container">
						<div>
							<div class="left" style="text-align: left; width: 50%;">
								<div style="padding-left: 7px;" class="label-qr"><?php echo __("Originale"); ?></div>

								<?php if(!file_exists($file->getPath()."/".$permalink_original->getQrcode().".png")) : ?>
									<?php $qrcode = PermalinkPeer::buildQrCode($file->getId(), $permalink_original->getLink(), PermalinkPeer::__OBJECT_FILE); ?>
									<?php $permalink_original->setQrcode($qrcode); $permalink_original->save(); ?>
								<?php endif; ?>
								<img src='<?php echo "/".$file->getPath(false)."/".$permalink_original->getQrcode().".png"; ?>' />
							</div>

							<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
								<?php if($file->existsThumbWeb()) : ?>
									<div class="right" style="text-align: left; width: 50%;">
										<div style="padding-left: 7px;" class="label-qr"><?php echo __("Web version"); ?></div>

										<?php if(!file_exists($file->getPath()."/".$permalink_web->getQrcode().".png")) : ?>
											<?php $qrcode = PermalinkPeer::buildQrCode($file->getId(), $permalink_web->getLink(), PermalinkPeer::__OBJECT_FILE); ?>
											<?php $permalink_web->setQrcode($qrcode); $permalink_web->save(); ?>
										<?php endif; ?>
										<img src='<?php echo "/".$file->getPath(false)."/".$permalink_web->getQrcode().".png"; ?>' />
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<br clear="all" />
						</div>
					</div>
				</div>
			</div>

			<script>
			jQuery(document).ready(function() {
				jQuery("#show-qr-code").bind("click", function() {
					if(jQuery("#qr_container").is(":visible"))
						jQuery("#qr_container").slideUp("slow");
					else
						jQuery("#qr_container").slideDown("slow");
				});

				jQuery("#show-video").bind("click", function() {
					if(jQuery("#integrate_container").is(":visible"))
						jQuery("#integrate_container").slideUp("slow");
					else
						jQuery("#integrate_container").slideDown("slow");
				});

				jQuery(".input_permalink").bind("click", function() {
					jQuery(this).focus();
					jQuery(this).select();
				});

				jQuery("#regenerate_permalink").bind("click", function() {
					if(confirm("<?php echo __("Are you sure you want to regenerate the permalinks?"); ?>"))
					{
						jQuery("#permalink_container").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");

						jQuery.post(
							"<?php echo url_for("permalink/regenerate"); ?>",
							{ file_id: <?php echo $file->getId(); ?> },
							function(data) {
								jQuery("#permalink_container").html(data);
							}
						);
					}
				});
			});
			</script>
		<?php endif; ?>
	</div>
</div>

	<script>
		jQuery(document).ready(function() {
			jQuery("#permalink_choice").bind("change", function() {
				if(jQuery(this).val() == "desactivate")
				{
					if(confirm("<?php echo __("Are you sure you want to desactivate the permalinks?"); ?>"))
					{
						jQuery("#permalink_container").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");

						jQuery.post(
							"<?php echo url_for("permalink/desactivate"); ?>",
							{ file_id: <?php echo $file->getId(); ?> },
							function(data) {
								jQuery("#permalink_container").html(data);
							}
						);
					}
				}
				else
				{
					jQuery("#permalink_container").fadeOut(200, function() {
						jQuery("#permalink_container").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");
						jQuery("#permalink_container").fadeIn(200, function() {
							jQuery.post(
								"<?php echo url_for("permalink/create"); ?>",
								{type: "file", id: "<?php echo $file->getId(); ?>" },
								function(data) {
									jQuery("#permalink_container").fadeOut(200, function() {
										jQuery("#permalink_container").html(data);
										jQuery("#permalink_container").fadeIn();
									});
								}
							);
						});
					});
				}
			});
		});
	</script>
