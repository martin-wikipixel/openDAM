<div id="searchResults-popup">
	<div class="inner">
		<div class="text">
			<img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" style="vertical-align: -3px;" /> <?php echo __("Please wait during thumbnails' regeneration..."); ?>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		setTimeout("regenerateThumbnails()", 2000);
	});

	function regenerateThumbnails()
	{
		jQuery.post(
			"<?php echo url_for("file/regenerateThumbnails"); ?>",
			{ id: <?php echo $file->getId(); ?> },
			function(data) {
				window.parent.location = "<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>";
			}
		);
	}
</script>