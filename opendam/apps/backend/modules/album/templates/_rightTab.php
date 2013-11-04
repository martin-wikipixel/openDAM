<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "user")?>>
		<a href="<?php echo path("album_right_user_list", array("album" => $album->getId()))?>"><?php echo __("Users")?></a>
	</li>

	<li <?php echo isActive($selected, "group")?>>
		<a href="<?php echo path("album_right_group_list", array("album" => $album->getId()))?>"><?php echo __("group.title")?></a>
	</li>
</ul>