<div class="filterBox">
	<div class="right">
		<select name="state_permalink_<?php echo $file->getId(); ?>" id="state_permalink_<?php echo $file->getId(); ?>" style="width: 150px;">
			<option value="activate" <?php echo $permalink_original ? "selected" : ""; ?>><?php echo __("Activate"); ?></option>
			<option value="desactivate" <?php echo !$permalink_original ? "selected" : ""; ?>><?php echo __("Desactivate"); ?></option>
			<option value="regenerate"><?php echo __("Regenerate"); ?></option>
		</select>
	</div>

	<?php if($permalink_original) : ?>
		<span id="enabled_permalink_<?php echo $file->getId(); ?>" style="<?php echo $permalink_original->getState() == PermalinkPeer::__STATE_DISABLED ? "display: none;" : ""; ?>">
			<label style="width: auto;"><?php echo __("Original file link"); ?> <a href="javascript: void(0);" class="tooltip" name="<?php echo __("Direct access link to the file to be used for direct download files or the inclusion on a website or a emailing."); ?>"><i class="icon-question-sign"></i></a></label>

			<br clear="all" />

			<input type="text" class="input_permalink" value="<?php echo 'https://'.$_SERVER['SERVER_NAME'].'/p/'.$permalink_original->getLink(); ?>" style="float: left; width: 210px; margin-top: 3px;" />

			<?php if($file->getType() == FilePeer::__TYPE_PHOTO && $permalink_web) : ?>
				<?php if($file->existsThumbWeb()) : ?>
					<?php $size = getimagesize($file->getThumbWebPathname()); ?>
					<br clear="all" />

					<label style="width: auto;"><?php echo __("Low definition file link"); ?> (<?php echo $size[0]." x ".$size[1]; ?>) <a href="javascript: void(0);" class="tooltip" name="<?php echo __("Direct access link to the file to be used for direct download files or the inclusion on a website or a emailing."); ?>"><i class="icon-question-sign"></i></a></label>

					<br clear="all" />

					<input type="text" class="input_permalink" value="<?php echo 'https://'.$_SERVER['SERVER_NAME'].'/p/'.$permalink_web->getLink(); ?>" style="float: left; width: 210px; margin-top: 3px;" />
				<?php endif; ?>
			<?php endif; ?>

			<?php if($file->getType() == FilePeer::__TYPE_VIDEO) : ?>
				<br clear="all" />

				<?php include_component("permalink", "showVideo", array("file" => $file));?>
			<?php endif; ?>

			<br clear="all" />
			<br clear="all" />

			<div class="filterBox">
				<div class="title" style="cursor:pointer;" onclick="toggleContainer('qr_container_<?php echo $file->getId(); ?>', 'qr_img_<?php echo $file->getId(); ?>');">
					<?php echo image_tag("right-arr.gif", array("id"=>"qr_img_".$file->getId(), "style"=>"float:left; margin-top:4px; cursor:pointer;", "align"=>"absmiddle"))?>
					<h4><?php echo __("QR code")?></h4>
				</div>

				<div class="qr_container" id="qr_container_<?php echo $file->getId(); ?>">
					<div id="filterByInformation" class="text">
						<div class="filterRow">
							<div class="left" style="text-align: left; width: 50%;">
								<div style="padding-left: 7px;" class="label-qr"><?php echo __("Originale"); ?></div>

								<?php if(!file_exists($file->getPath()."/".$permalink_original->getQrcode().".png")) : ?>
									<?php $qrcode = PermalinkPeer::buildQrCode($file->getId(), $permalink_original->getLink(), PermalinkPeer::__OBJECT_FILE); ?>
									<?php $permalink_original->setQrcode($qrcode); $permalink_original->save(); ?>
								<?php endif; ?>
								<img src='<?php echo "/".$file->getPath(false)."/".$permalink_original->getQrcode().".png"; ?>' />
							</div>

							<?php if($file->getType() == FilePeer::__TYPE_PHOTO && $permalink_web) : ?>
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
						</div>
					</div>
				</div>
			</div>
		</span>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function() {
	tooltip();

	jQuery(".input_permalink").click(function() {
		jQuery(this).focus();
		jQuery(this).select();
	});


	jQuery("#state_permalink_<?php echo $file->getId(); ?>").bind("change", function() {
		switch(jQuery("#state_permalink_<?php echo $file->getId(); ?>").val())
		{
			case "regenerate":
			{
				if(confirm("<?php echo __("Are you sure you want to regenerate the permalinks?"); ?>"))
				{
					jQuery("#permalink_container_<?php echo $file->getId(); ?>").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");

					jQuery.post(
						"<?php echo url_for("permalink/regenerate"); ?>",
						{ file_id: <?php echo $file->getId(); ?>, template: "showFile" },
						function(data) {
							jQuery("#permalink_container_<?php echo $file->getId(); ?>").html(data);
							jQuery("#state_permalink_<?php echo $file->getId(); ?>").val("activate");
						}
					);
				}
			}
			break;

			case "desactivate":
			{
				if(confirm("<?php echo __("Are you sure you want to desactivate the permalink?"); ?>"))
				{
					jQuery("#enabled_permalink_<?php echo $file->getId(); ?>").slideUp();
					jQuery("#div_disabled_<?php echo $file->getId(); ?>").fadeOut(200, function() {
						jQuery("#div_enabled_<?php echo $file->getId(); ?>").fadeIn();
					});

					if(jQuery.browser.msie)
						jQuery("#enabled_permalink_<?php echo $file->getId(); ?>").hide();

					jQuery.post(
						"<?php echo url_for("permalink/desactivate"); ?>",
						{ file_id: "<?php echo $file->getId(); ?>" }
					);
				}
			}
			break;

			case "activate":
			{
				jQuery("#enabled_permalink_<?php echo $file->getId(); ?>").slideDown();
				jQuery("#div_enabled_<?php echo $file->getId(); ?>").fadeOut(200, function() {
					jQuery("#div_disabled_<?php echo $file->getId(); ?>").fadeIn();
				});

				if(jQuery.browser.msie)
					jQuery("#enabled_permalink_<?php echo $file->getId(); ?>").show();

				jQuery.post(
					"<?php echo url_for("permalink/create"); ?>",
					{ id: "<?php echo $file->getId(); ?>", type: "file", template: "showFile" }
				);
			}
			break;
		}
	});
});
</script>