<?php include_partial("file/navigationManage", array("selected"=>"copy", "folder"=>$folder, "file_ids"=>$file_ids));?>

<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag('file/copySelected', array('name'=>'file_copy_form', 'id'=>'file_copy_form'))?>
			<?php echo input_hidden_tag("folder_id", $folder->getId(), array())?>

			<?php foreach ($file_ids as $file_id) :
					echo input_hidden_tag("file_ids[]", $file_id, array());
			endforeach; ?>
  
			<label for="group_id" style="width: 150px;"><?php echo __("Select group")?> : </label>
			<?php $options = GroupePeer::getGroupsInArray($sf_user->getId())?>
			<?php echo select_tag('group_id', options_for_select($options, $folder->getGroupeId()), array("style"=>"float:left; width:279px;"))?>

			<br clear="all">
			<br clear="all">

			<div id="folders_container">
				<?php include_partial("file/folders", array("group_id"=>$folder->getGroupeId(), "folder_id"=>$folder->getId()));?>
			</div>
		</form>
		<br clear="all"/>
<div class="right">
	<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
	<a href="#" onClick="jQuery('#file_copy_form').submit();"  class="button btnBS"><span><?php echo __("MOVE")?></span></a> 
</div>
	</div>
</div>



<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#group_id").bind("change", function() {
			jQuery.post(
				"<?php echo url_for("file/observeGroupId?folder_id=".$folder->getId()); ?>",
				{ "group_id": jQuery("#group_id").val() },
				function(data) {
					jQuery("#folders_container").html(data);
				}
			);
		});
	});
</script>