<div id="searchResults-popup">
	<div class="inner">
		<form name='add_folder_form' id='add_folder_form' class='form' action='<?php echo url_for("folder/addFolderUpload"); ?>' method='post'>
			<?php echo $form['_csrf_token']->render(); ?>
			<?php echo $form['group_id']->render(); ?>

			<label for="data_company"><?php echo __("Folder name")?> :</label>
			<?php echo $form['name']->render(); ?>
			<span class="description" style="width: 15px;"><span class='require_field'>*</span></span>

			<br clear="all">

			<span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span>
			<br clear="all">
			<div class="right">
				<a href="#" onclick="jQuery('#add_folder_form').submit();" class="button btnBS"><span><?php echo __("NEXT STEP")?> ></span></a>
			</div>
		</form>
	</div>
</div>