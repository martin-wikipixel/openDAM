<?php $files_related = $file->getFileRelatedsRelatedByFileIdTo(); ?>

<div class="left" style="width: 100%;">
	<?php if($role) : ?>
		<div id="add_related_medias">
			<div class="rub">
				<div class="label-right">
					<input type="radio" style="margin-top: 0px; margin-bottom: 0px;" name="related_choice[]" id="related_choice_reference" value="reference" />
				</div>

				<div class="label-right no-margin">
					<label for="related_choice_reference">
						<?php echo __("Write one or more media's reference separated by commas"); ?>

						<a class="tooltip" name="<span class='text'><b><?php echo __("Where to find media's reference?"); ?></b></span><br /><br /><img src='<?php echo image_path("how_to/media_reference_".$sf_user->getCulture().".jpg"); ?>' />">
							<i class="icon-question-sign"></i>
						</a>
					</label>
				</div>
			</div>

			<br clear="all" />

			<div class="rub" style="width: 100%;">
				<div class="value-right" style="width: 100%;">
					<div class="add-comment" style="float: left; margin-top: 0px; width: 90%;">
						<div class="textarea-wrapper" style="padding-right: 3px;">
							<input type="text" name="reference" id="reference" style="border: 0; padding: 0; margin: 0; height: 30px; width: 100%; font-size: 14px!important; padding-top: 5px; padding-bottom: 5px;" />
						</div>
					</div>


						<a id="add_reference" href="javascript: void(0);" class="left" style="margin-left: 5px; margin-top: 8px;"><img src="<?php echo image_path("icons/add4Bis.gif"); ?>" /></a>

				</div>
			</div>

			<br clear="all" />

			<div class="rub">
				<div class="label-right">
					<div class="require_field left" id="error_related"></div>
				</div>
			</div>

			<br clear="all" />

			<div class="rub">
				<div class="label-right">
					<input type="radio" style="margin-top: 0px; margin-bottom: 0px;" name="related_choice[]" id="related_choice_media" value="media" class="left" />
				</div>

				<div class="label-right no-margin">
					<label for="related_choice_media"><?php echo __("Browse imported media"); ?></label>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

<br clear="all" />

<?php foreach($files_related as $file_related) : ?>
	<?php $file_from = $file_related->getFileRelatedByFileIdFrom(); ?>
	<?php $role = $sf_user->getRole($file_from->getGroupeId(), $file_from->getFolderId()); ?>

	<?php if($file_from->getState() == FilePeer::__STATE_VALIDATE) : ?>
		<?php switch($file_from->getType()) :
			case FilePeer::__TYPE_PHOTO: $addClass = "grid-photo"; $path_picto = image_path("file/picto_photo.gif"); break;
			case FilePeer::__TYPE_AUDIO: $addClass = "grid-audio"; $path_picto = image_path("file/picto_audio.gif"); break;
			case FilePeer::__TYPE_VIDEO: $addClass = "grid-video"; $path_picto = image_path("file/picto_video.gif"); break;
		endswitch; ?>
		<div class="grid-file file-div <?php echo $addClass; ?>" id="<?php echo $file_from->getId()?>" style="margin-bottom: 0px; margin-right: 0px;">
			<div style="width: 100%; text-align: center;">
				<?php if($role) : ?>
					<div style="position: absolute; margin-left: 10px; margin-top: -14px;">
						<a href="javascript: void(0);" onclick="unlinkMedia(<?php echo $file_from->getId(); ?>);">
							<img src='<?php echo image_path("icons/recycle.gif"); ?>' />
						</a>
					</div>
					<a href="<?php echo url_for("file/show?folder_id=".$file_from->getFolderId()."&id=".$file_from->getId()); ?>" name="<?php echo __('Group name :').' '.GroupePeer::retrieveByPk($file_from->getGroupeId()).'<br />'.__('Folder name :').' '.$file_from->getFolder().'<br />'.__('File name :').' '.$file_from.'<br />'.__('Creation date :').' '.$file_from->getCreatedAt('d/m/Y H:i:s').'<br />'.__('Uploaded by :').' '.$file_from->getUser(); ?>" class="tooltip">
						<?php $dimension = getimagesize($file->getThumb100Pathname()); ?>
						<?php $merge = "margin-top: ".floor((100 - $dimension[1]) / 2)."px;"; ?>
						<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100")); ?>" style="z-index:0; <?php echo $merge; ?>" />
					</a>

						<script>
							jQuery(document).ready(function(){
								jQuery("#<?php echo $file_from->getId()?>.file-div").draggable({
									helper: "clone",
									start: function(event, ui) {
										jQuery(ui.helper).css("z-index", 1100);
										jQuery(this).attr('rel', 'addToBasketLite("<?php echo $file_from->getId()?>","file");');
									}
								});
							});
						</script>

				<?php else: ?>
					<img src="<?php echo image_path("no-access-file-100x100"); ?>" style="z-index:0;" />
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
<script>
	var mediaSelected = "";

	function unlinkMedia(media_id)
	{
		if(confirm("<?php echo __("Are you sure you want unbind these files?"); ?>"))
		{
			jQuery("#related_container").fadeOut(200, function() {
				jQuery.post(
					"<?php echo url_for("file/unbindReference"); ?>",
					{ file_id: <?php echo $file->getId(); ?>, media_id: media_id },
					function(data) {
						jQuery("#related_container").html(data.html);
						tooltip();
						jQuery("#related_container").fadeIn();
					},
					"json"
				);
			});
		}
	}

	jQuery(document).ready(function() {

			jQuery("#related_choice_media").bind("click", function() {
				if(jQuery(this).is(":checked") == true)
				{
					var object = jQuery("#choice_media");
					mediaSelected = "";

					jQuery(object).fadeIn(200, function() {
						jQuery.post(
							"<?php echo url_for("file/loadThumb"); ?>",
							function(data) {
								jQuery(object).fadeOut(200, function() {
									jQuery(object).html(data);
									jQuery(object).fadeIn(200, function() {
										jQuery("#buttons_choice_media").fadeIn();
									});
								});
							}
						);
					});

					jQuery("#validate_choice").bind("click", function() {
						jQuery.post(
							"<?php echo url_for("file/addReference"); ?>",
							{ from: mediaSelected, to: "<?php echo $file->getId(); ?>" },
							function(data) {
								if(data.code > 0)
								{
									jQuery("#error_related").fadeOut(200, function() {
										jQuery(this).html(data.html);
										jQuery(this).fadeIn();
									});
								}
								else
								{
									jQuery("#buttons_choice_media").fadeOut();
									jQuery("#choice_media").fadeOut(200, function() {
										jQuery(this).html('<div style="width: 100%; text-align: center;"><img src="<?php echo image_path("loader-rotate.gif"); ?>" /></div>');
									});

									jQuery("#related_container").fadeOut(200, function() {
										jQuery(this).html(data.html);
										tooltip();
										jQuery(this).fadeIn();
									});
								}
							},
							"json"
						);
					});

					jQuery("#cancel_choice").bind("click", function() {
						jQuery("#buttons_choice_media").fadeOut();
						jQuery("#choice_media").fadeOut(200, function() {
							jQuery(this).html('<div style="width: 100%; text-align: center;"><img src="<?php echo image_path("loader-rotate.gif"); ?>" /></div>');
						});
					});
				}
			});


		jQuery("#reference").bind("focus", function() {
			jQuery("#related_choice_reference").attr("checked", true);
		});

		jQuery("#add_reference").bind("click", function() {
			var error = false;

			if(jQuery("#related_choice_reference").is(":checked") == false && jQuery("#related_choice_media").is(":checked") == false)
			{
				error = true;

				jQuery("#error_related").fadeOut(200, function() {
					jQuery(this).html("<?php echo __("Media's reference is required."); ?>");
					jQuery(this).fadeIn();
				});
			}
			else if(jQuery("#related_choice_reference").is(":checked") == true && jQuery("#reference").val() == "")
			{
				error = true;

				jQuery("#error_related").fadeOut(200, function() {
					jQuery(this).html("<?php echo __("Media's reference is required."); ?>");
					jQuery(this).fadeIn();
				});
			}
			else
				jQuery("#error_related").fadeOut();


			if(!error)
			{
				if(jQuery("#related_choice_reference").is(":checked") == true)
					var references = jQuery("#reference").val();

				jQuery.post(
					"<?php echo url_for("file/addReference"); ?>",
					{ from: references, to: "<?php echo $file->getId(); ?>" },
					function(data) {
						if(data.code > 0)
						{
							jQuery("#error_related").fadeOut(200, function() {
								jQuery(this).html(data.html);
								jQuery(this).fadeIn();
							});
						}
						else
						{
							jQuery("#related_container").fadeOut(200, function() {
								jQuery(this).html(data.html);
								tooltip();
								jQuery(this).fadeIn();
							});
						}
					},
					"json"
				);
			}
		});
	});
</script>