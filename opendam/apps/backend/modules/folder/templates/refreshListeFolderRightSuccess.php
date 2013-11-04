<?php $folders = FolderPeer::getFoldersRightsInArray($sf_params->get("user_id"), $sf_params->get("group_id"), "not_free"); ?>
<select name='folder_right_id' id='folder_right_id' style='width: 250px;'>
	<option value=''><?php echo __("Select folder"); ?></option>
	<?php foreach($folders as $key => $value) : ?>
		<option value='<?php echo $key; ?>'><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>