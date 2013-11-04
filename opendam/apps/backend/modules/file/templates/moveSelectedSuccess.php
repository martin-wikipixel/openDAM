<?php include_partial("file/navigationManage", array("selected"=>"move", "folder"=>$folder, "file_ids"=>$file_ids));?>

<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag('file/moveSelected', array('name'=>'file_move_form', 'id'=>'file_move_form'))?>
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
		<div class="text" style="height: 20px;" id="waiting">
			<div style="display: none;">
				<br clear="all"/>
				<img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" style="vertical-align: -3px;" /> <?php echo __("Please wait during moving folders..."); ?>
			</div>
		</div>
<div class="right">
	<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
	<a href="#" onClick="submitMove();" id="submit_button" class="button btnBS"><span><?php echo __("MOVE")?></span></a> 
</div>
	</div>
</div>



<script type="text/javascript">
	function submitMove()
	{
		jQuery("#waiting > div").fadeIn('slow', function() {
			jQuery('#file_move_form').submit();
		});
	}

	jQuery(document).ready(function() {
		if(!jQuery("#folder_id1").val())
			jQuery("#submit_button").fadeOut();

		jQuery("#group_id").bind("change", function() {
			jQuery.post(
				"<?php echo url_for("file/observeGroupId?folder_id=".$folder->getId()); ?>",
				{ "group_id": jQuery("#group_id").val() },
				function(data) {
					jQuery("#folders_container").html(data);

					if(!jQuery("#folder_id1").val())
						jQuery("#submit_button").fadeOut();
					else
						jQuery("#submit_button").fadeIn();
				}
			);
		});
	});
</script>