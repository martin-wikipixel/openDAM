<?php include_partial("folder/navigationManage", array("selected"=>"edit", "folder"=>$folder)); ?>

<?php include_partial("folder/subMenubarInformations", array("selected" => "thumb", "folder" => $folder)); ?>

<div id="searchResults-popup">
	<div class="inner">
		<form name='thumb_form' id='thumb_form' class='form' action='<?php echo url_for("folder/thumbnail"); ?>' enctype='multipart/form-data' method='post'>
			<?php echo $form['_csrf_token']->render(); ?>
			<?php echo $form['id']->render(); ?>

			<div id="thumbnail_container" style="float: left; position: relative; <?php echo $folder->getThumbnail() ? "display: block;" : ""; ?>">
				<?php if($folder->getThumbnail() && $folder->exists()): ?>
					<img src='<?php echo image_path("icons/recycle.gif"); ?>' style='cursor: pointer; position: absolute; top: 25; left: 5; z-index: 100; border: 0' onclick='removeThumbnail();' />

					<img src="<?php echo path("@folder_thumbnail", array("id" => $folder->getId())); ?>" id="uploaded_thumbnail" />

					<?php echo $form['uploaded_thumbnail_name']->render(); ?>
					<?php echo $form['is_upload']->render(); ?>

					<?php echo $form['width']->render(); ?>
					<?php echo $form['height']->render(); ?>
				<?php endif ?>
			</div>

			<div id="rest_form">
				<br clear="all">
				<br clear="all">

				<input type="radio" name="from_thumb" id="from_computer" class="left" style="margin-right: 10px;" />
				<label for="from_computer" style="width: 150px;"><?php echo __("From my computer")?> :</label>

				<div id="container_from_computer" style="display: none;">
					<?php echo $form['thumbnail']->render(); ?>
					<input type="button" id="uploadButton" value="<?php echo __("Upload")?>" style="float: left; margin-left: 5px;"/>
					<span id="thumbnail_indicator" style="display:none; margin-top: 10px; margin-left: 5px; float:left;"><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></span>
					<span class="description"><?php echo __("Optimal size: 720x720 pixels")?><br /><?php echo __("Minimum size : 220x220 pixels")?><br/><?php echo __("Formats : JPG, GIF, PNG")?></span>
				</div>

				<?php echo $form['x1']->render(); ?>
				<?php echo $form['x2']->render(); ?>
				<?php echo $form['y1']->render(); ?>
				<?php echo $form['y2']->render(); ?>
				<?php echo $form['w']->render(); ?>
				<?php echo $form['h']->render(); ?>

				<br clear="all">
				<br clear="all">

				<input type="radio" name="from_thumb" id="from_wikipixel" class="left" style="margin-right: 10px;" />
				<label for="from_wikipixel" style="width: 150px;"><?php echo __("From WikiPixel"); ?> :</label>

				<div id="container_from_wikipixel" style="display: none; width: 100%; margin: 0;">
					<select name="folder_wikipixel" id="folder_wikipixel" style="float: left; width: 200px;">
						<option value="all"><?php echo __("The entire main folder"); ?></option>
						<?php foreach($folders as $folder_id => $label) : ?>
							<option value="<?php echo $folder_id; ?>"><?php echo $label; ?></option>
						<?php endforeach; ?>
					</select>

					<br clear="all">

					<div class="files-thumb">
						<?php include_partial("folder/filesThumb", array("files" => $files)); ?>
					</div>
				</div>
			</div>
		</form>

		<br clear="all">
		<br clear="all">

		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>

				<a href="#" onClick="jQuery('#thumb_form').submit();" class="button btnBS"><span><?php echo __("SAVE")?></span></a>

		</div>
	</div>
</div>
<script>
jQuery(document).ready(function() {
	function bindThumbFiles()
	{
		jQuery(".thumb_files").bind("click", function() {
			if(confirm("<?php echo __("Are you sure you want to use this photo?"); ?>"))
			{
				var object = jQuery(this);
				jQuery(".files-thumb").fadeOut('slow', function() {
					jQuery("#thumbnail_container").fadeOut();
					jQuery("#thumbnail_container").html("<p style='width: 300px; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></p>");
					jQuery("#thumbnail_container").fadeIn('slow', function() {
						jQuery.post(
							"<?php echo url_for("folder/thumbnailUploadFromWikipixel"); ?>",
							{ file_id: jQuery(object).attr("rel") },
							function(data) {
								jQuery("#thumbnail_container").fadeOut('slow', function() {
									jQuery(this).html(data);
									jQuery(this).fadeIn('slow', function() {
										jQuery("#rest_form").fadeOut('slow');
									});
								});
							}
						);
					});
				});
			}
		});
	}

	jQuery("#from_wikipixel").bind("click", function() {
		if(!jQuery("#container_from_wikipixel").is(":visible"))
			jQuery("#container_from_wikipixel").fadeIn();

		jQuery("#container_from_computer").fadeOut();
	});

	jQuery("#from_computer").bind("click", function() {
		if(!jQuery("#container_from_computer").is(":visible"))
			jQuery("#container_from_computer").fadeIn();

		jQuery("#container_from_wikipixel").fadeOut();
	});

	bindThumbFiles();

	jQuery("#folder_wikipixel").bind("change", function() {
		jQuery(".files-thumb").fadeOut('slow', function() {
			jQuery(this).html("<p style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></p>");
			jQuery(this).fadeIn('slow', function() {
				jQuery.post(
					"<?php echo url_for("folder/getFilesThumb"); ?>",
					{ folder_id: jQuery("#folder_wikipixel").val(), group_id: <?php echo $folder->getGroupeId(); ?> },
					function(data) {
						jQuery(".files-thumb").fadeOut('slow', function() {
							jQuery(this).html(data);
							bindThumbFiles();
							jQuery(this).fadeIn();
						});
					}
				);
			});
		});
	});

	jQuery("#uploadButton").bind("click", function() {
		jQuery("#thumbnail_indicator").show();

		var action = jQuery("#thumb_form").attr("action");

		jQuery("<iframe name='iframe_upload_thumb' id='iframe_upload_thumb' src='javascript: return false;'></iframe>")
			.load(function() {
				var html = jQuery(this).contents().find("body").html();

				if(html)
				{
					jQuery("#thumb_form").attr("action", action);
					jQuery("#thumb_form").removeAttr("target");

					removeThumbnail();
					jQuery('#thumbnail_container').html(html);
					jQuery("#thumbnail_container").fadeIn();
					jQuery('#data_thumbnail').val('');

					if(jQuery('#data_is_upload'))
						jQuery('#data_is_upload').val(1);

					jQuery('#thumbnail_indicator').hide();
					jQuery("#rest_form").fadeOut('slow');
					jQuery(this).remove();
				}
			})
			.appendTo('body');

		jQuery("#thumb_form").attr("target", "iframe_upload_thumb");
		jQuery("#thumb_form").attr("action", "<?php echo url_for('folder/thumbnailUpload')?>");

		jQuery("#thumb_form").submit();
	});
});

function removeThumbnail()
{
	jQuery('#thumbnail_container').html("");
}
</script>