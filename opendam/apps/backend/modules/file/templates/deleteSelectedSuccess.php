<?php include_partial("file/navigationManage", array("selected"=>"delete", "folder"=>$folder, "file_ids"=>$file_ids));?>

<div id="searchResults-popup">
	<div class="inner">
		<?php if(!$deleteOnDemand) : ?>
			<?php echo form_tag('file/deleteSelected', array('name'=>'file_remove_form', 'id'=>'file_remove_form'))?>
				<?php echo input_hidden_tag("folder_id", $folder->getId(), array())?>
				<?php foreach ($file_ids as $file_id) :
						echo input_hidden_tag("file_ids[]", $file_id, array());
				endforeach; ?>

				<div class="text"><?php echo __("Are you sure want to remove the selected files?")?></div>
				<br clear="all" />
				<div class="red"><?php echo __("ATTENTION: ALL REMOVAL IS IRREVERSIBLE.")?></div>

				<br clear="all"/>

				<div class="right">
					<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
					<a href="#" onClick="jQuery('#file_remove_form').submit();"  class="button btnBS"><span><?php echo __("REMOVE")?></span></a>
				</div>
			</form>
		<?php else : ?>
			<form name='form-delete-selected' id='form-delete-selected' action='<?php echo url_for("file/deleteSelected"); ?>' method='post'>
				<?php echo $form['_csrf_token']->render(); ?>

				<input type='hidden' name='folder_id' id='folder_id' value='<?php echo $folder->getId(); ?>' />
				<?php foreach ($file_ids as $file_id) : ?>
					<input type='hidden' name='file_ids[]' id='file_ids_<?php echo $file_id; ?>' value='<?php echo $file_id; ?>' />
				<?php endforeach; ?>

				<label><?php echo __("Reason for removal"); ?></label>
				<?php echo $form['reason']->render(); ?>
				<span class="description"><span class='require_field'>*</span></span>

				<br clear="all"><span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span><br clear="all">

				<div class="right">
					<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
					<a href="#" onClick="jQuery('#form-delete-selected').submit();"  class="button btnBS"><span><?php echo __("SEND INQUIRY")?></span></a>
				</div>
			</form>
		<?php endif; ?>
	</div><!--inner-->
</div><!--searchResults-->