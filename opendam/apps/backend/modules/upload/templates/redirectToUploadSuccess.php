<div id="searchResults">
	<div class="inner">
  
		<div style="width: 100%; text-align: center;">
			<img src='<?php echo image_path("icons/loader/big-circle.gif"); ?>' />
		</div>
		<div class="clear"></div>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		setTimeout(function() { window.location='<?php echo url_for("upload/uploadify?folder_id=".$folder_id."&navigation=upload&mode=normal"); ?>'; }, 100);
	});
</script>