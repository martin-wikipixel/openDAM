<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag("request/sendRequestFolder", array("name"=>"sendRequestFolderForm", "id"=>"sendRequestFolderForm", "class"=>"form"))?>
			<?php echo $form['_csrf_token']->render(); ?>

			<label for="data_folder_id" style="width:180px;"><?php echo __("Folder")?> :</label>
			<?php echo $form['folder_id']->render(); ?>
			<div style="float: left;" class="text">
				<?php echo $folderPath; ?>
			</div>

			<br clear="all">
			<br clear="all">

			<label for="message" style="width:180px;"><?php echo __("Message")?> :</label>
			<?php echo $form['message']->render(); ?>
			<span class="description" style="width:100px;"><span class='require_field'>*</span></span>
		</form>

		<br clear="all"><span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span><br clear="all">
		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
			<a href="#" onclick="jQuery('#sendRequestFolderForm').submit();" class="button btnBS"><span><?php echo __("SEND")?></span></a>
		</div>
	</div>
</div>