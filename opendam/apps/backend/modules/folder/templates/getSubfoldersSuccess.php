<select name="add_folder_folder_id" id="add_folder_folder_id" style="float: left; width: 100%; margin-top: 0px;">
	<?php foreach($subfolders as $key => $value) : ?>
		<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>