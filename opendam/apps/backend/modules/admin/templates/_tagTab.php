<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "edit")?>>
		<a href="<?php echo path("@admin_tag_edit", array("id" => $tag->getId())); ?>">
			<?php echo __("Edit"); ?>
		</a> 
	</li>
	
	<li <?php echo isActive($selected, "albums")?>>
		<a href="<?php echo path("@admin_tag_album_list", array("tag" => $tag->getId())); ?>">
			<?php echo __("Albums"); ?> (<?php echo FileTagPeer::countAlbumsOfTag($tag->getId());?>)
		</a>
	</li>
	

	<li <?php echo isActive($selected, "folders")?>>
		<a href="<?php echo path("@admin_tag_folder_list", array("tag" => $tag->getId())); ?>">
			<?php echo __("Folders"); ?> (<?php echo FileTagPeer::countFoldersOfTag($tag->getId());?>)
		</a>
	</li>

	<li <?php echo isActive($selected, "files")?>>
		<a href="<?php echo path("@admin_tag_file_list", array("tag" => $tag->getId())); ?>">
			<?php echo __("Files"); ?> (<?php echo FileTagPeer::countFilesOfTag($tag->getId());?>)
		</a>
	</li>
</ul>
