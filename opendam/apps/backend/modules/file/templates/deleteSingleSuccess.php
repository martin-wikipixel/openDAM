<?php include_partial("file/navigationManageSingle", array("selected"=>"delete", "folder"=>$folder, "file"=>FilePeer::retrieveByPk($id))); ?>

<div id="searchResults-popup">
	<div class="inner">
		<?php if(!$deleteOnDemand) : ?>
			<?php echo form_tag('file/deleteSingle', array('name'=>'file_remove_form', 'id'=>'file_remove_form'))?>
				<?php echo input_hidden_tag("folder_id", $folder->getId(), array())?>
				<?php echo input_hidden_tag("id", $id, array()); ?>
		  
				<div class="text"><?php echo __("Are you sure want to remove file?")?></div>
				<br clear="all">
				<div class="red"><?php echo __("ATTENTION: ALL REMOVAL IS IRREVERSIBLE.")?></div>

				<br clear="all"/>

				<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
				<a href="#" onClick="jQuery('#file_remove_form').submit();"  class="button btnBS"><span><?php echo __("REMOVE")?></span></a>
			</form>
		<?php else: ?>
			<form name='form-delete-single' id='form-delete-single' action='<?php echo url_for("file/deleteSingle"); ?>' method='post'>
				<?php echo $form['_csrf_token']->render(); ?>
				<?php echo $form['id']->render(); ?>
				<?php echo $form['folder_id']->render(); ?>

				<label><?php echo __("Reason for removal"); ?></label>
				<?php echo $form['reason']->render(); ?>
				<span class="description"><span class='require_field'>*</span></span>

				<br clear="all"><span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span><br clear="all">

				<div class="right">
					<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
					<a href="#" onClick="jQuery('#form-delete-single').submit();"  class="button btnBS"><span><?php echo __("SEND INQUIRY")?></span></a>
				</div>
			</form>
		<?php endif; ?>
	</div><!--inner-->
</div><!--searchResults-->