<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "edit")?>>
		<a href="<?php echo path("@admin_group_edit", array("id" => $group->getId())); ?>"><?php echo __("group.label.name"); ?></a> 
	</li>
	
	<li <?php echo isActive($selected, "users")?>>
		<a href="<?php echo path("@admin_group_user_list", array("id" => $group->getId())); ?>">
			<?php echo __("Users"); ?> (<?php echo $group->countUsers();?>)
		</a>
	</li>
	
	<li <?php echo isActive($selected, "permissions")?>>
		<a href="<?php echo path("@admin_group_permission_list", array("id" => $group->getId())); ?>">
			<?php echo __("Manage permissions"); ?> (<?php echo UnitGroupPeer::countByUnitId($group->getId());?>)
		</a>
	</li>
</ul>
