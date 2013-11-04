<?php include_partial("folder/navigationManage", array("selected"=>"move", "folder"=>$folder));?>
<?php include_partial("folder/subMenubarActions", array("selected"=>"move", "folder"=>$folder));?>

<div id="searchResults-popup">
	<div class="inner">
		<form name='move_form' id='move_form' action='<?php echo url_for('folder/move'); ?>' method='post'>
			<input type='hidden' name='id' id='id' value='<?php echo $folder->getId(); ?>' />
  
			<label for="group_id" style="width: 250px;"><?php echo __("Move folder to another group")?> : </label>
			<?php $options = GroupePeer::getGroupsInArray($sf_user->getId(), $folder->getGroupeId()); ?>
			<?php if(empty($options))
						$options = GroupePeer::getGroupsInArray($sf_user->getId()); ?>
			<select name='group_id' id='group_id' style='float: left; width: 279px;' onchange='loadFolderGroup();'>
				<?php foreach($options as $key => $value) : ?>
					<option value='<?php echo $key; ?>'><?php echo $value; ?></option>
				<?php endforeach; ?>
			</select>

			<br clear="all">

			<label for="folder_id" style="width: 250px;"><?php echo __("Move folder to another subfolder")?> : </label>
			<span id='folder_id_container'></span>

			<br clear="all">
			<br clear="all">

			<div class="right">
				<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>  


					<a href="#" onClick="jQuery('#move_form').submit();"  class="button btnBS"><span><?php echo __("SAVE")?></span></a>

			</div>
		</form>
	</div>
</div>

<script>
	function loadFolderGroup() {
		jQuery.post(
			"<?php echo url_for("folder/folderFromGroup"); ?>",
			{ "group_id": jQuery("#group_id").val() },
			function(data) {
				jQuery("#folder_id_container").html(data);
			}
		);
	}

	loadFolderGroup();
</script>