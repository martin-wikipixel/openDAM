<select name='data[group_to]' id='data_group_to' style='float: left; width: 200px;'>
	<option value=''><?php echo __("Select group"); ?></option>
	<?php foreach($groups as $key => $value) : ?>
		<option value='<?php echo $key; ?>'><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>