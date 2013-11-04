<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}

if (isActive($selected, "list")) {
	$albumCount = $albums->count();
}
else {
	$albumCount = GroupePeer::countAlbumsHaveAccessForUser(albumActions::getListParams());
}

if (isActive($selected, "shared")) {
	$albumSharedCount = $albums->count();
}
else {
	$albumSharedCount = GroupePeer::countAlbumsHaveAccessForUser(albumActions::getSharedListParams());
}

$albumNotSharedCount = -1;
?>
<ul class="breadcrumb breadcrumb-tab">
	<li <?php echo isActive($selected, "list")?>>
		<a href="<?php echo path("album_list")?>"><?php echo __("Shared with me")?> (<?php echo $albumCount?>)</a>
	</li>

	<li <?php echo isActive($selected, "shared")?>>
		<a href="<?php echo path("album_shared_list")?>"><?php echo __("Not shared with me")?>  (<?php echo $albumNotSharedCount?>)</a>
	</li>
	
	<li <?php echo isActive($selected, "shared")?>>
		<a href="<?php echo path("album_shared_list")?>"><?php echo __("External shared")?> -  (<?php echo $albumSharedCount?>)</a>
	</li>

	<li>
		<a href="<?php echo url_for("file/recent")?>"><?php echo __("Recents")?></a>
	</li>
</ul>