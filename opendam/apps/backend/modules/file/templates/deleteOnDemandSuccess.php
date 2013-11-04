<div id="searchResults-popup">
	<div class="inner">
		<form name='form-delete-file' id='form-delete-file' action='<?php echo url_for("file/deleteOnDemand"); ?>' method='post'>
			<?php echo $form['_csrf_token']->render(); ?>
			<?php echo $form['id']->render(); ?>

			<label><?php echo __("Reason for removal"); ?></label>
			<?php echo $form['reason']->render(); ?>
			<span class="description"><span class='require_field'>*</span></span>

			<br clear="all"><span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span><br clear="all">

			<div class="right">
				<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
				<a href="#" onClick="jQuery('#form-delete-file').submit();" class="button btnBS"><span><?php echo __("SEND INQUIRY")?></span></a>
			</div>
		</form>
	</div>
</div>