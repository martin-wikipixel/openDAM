<?php 
function isActiveTab($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActiveTab($selected, "album")?>>
		<a href="<?php echo path("@group_right_user_list", array("album" => $album->getId())); ?>"><?php echo __("Album"); ?></a> 
	</li>
	
	<li <?php echo isActiveTab($selected, "user")?>>
		<a href="<?php echo path("@group_right_user_search", array("album" => $album->getId())); ?>"><?php echo __("Users"); ?></a> 
	</li>
	
	<li <?php echo isActiveTab($selected, "group")?>>
		<a href="<?php echo path("@group_right_group_search", array("album" => $album->getId())); ?>"><?php echo __("group.title"); ?></a> 
	</li>
</ul>
