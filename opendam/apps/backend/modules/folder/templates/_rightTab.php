<?php 
function isActive($selected, $name) {
	return $selected == $name ? "selected" : "";
}
?>

<h4>
	<?php echo __("Access list"); ?>
	<select id="access-type" data-folder-id="<?php echo $folder->getId(); ?>">
		<option value="user" <?php echo isActive($selected, "user"); ?>><?php echo __("Users"); ?></option>
		<option value="group" <?php echo isActive($selected, "group"); ?>><?php echo __("group.title"); ?></option>
	</select>
</h4>