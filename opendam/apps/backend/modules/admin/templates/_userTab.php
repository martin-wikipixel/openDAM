<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "user")?>>
		<a href="<?php echo path("@admin_user_edit", array("id" => $user->getId())); ?>"><?php echo __("User info"); ?></a> 
	</li>
	
	<li <?php echo isActive($selected, "password")?>>
		<a href="<?php echo path("@admin_user_password", array("id" => $user->getId())); ?>"><?php echo __("Password"); ?></a>
	</li>
	

	<li <?php echo isActive($selected, "album_right")?>>
		<a href="<?php echo path("@admin_user_album_right_list", array("user" => $user->getId())); ?>"><?php echo __("Albums's rights"); ?></a>
	</li>

	<!--  
	<li <?php echo isActive($selected, "folder_right")?>>
		<a href="<?php echo path("@admin_user_folder_right_list", array("user" => $user->getId())); ?>"><?php echo __("Folders's rights"); ?></a>
	</li>
	-->

	<li <?php echo isActive($selected, "group")?>>
		<a href="<?php echo path("@admin_user_group_list", array("user" => $user->getId())); ?>"><?php echo __("group.title"); ?></a>
	</li>

	<li <?php echo isActive($selected, "module")?>>
		<a href="<?php echo path("@admin_user_module_list", array("user" => $user->getId())); ?>"><?php echo __("Modules"); ?></a>
	</li>
</ul>
