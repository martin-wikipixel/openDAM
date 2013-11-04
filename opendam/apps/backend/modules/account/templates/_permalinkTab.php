<?php 
/*function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}*/
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "albums")?>>
		<a href="<?php echo path("@account_permalink_album_list"); ?>"><?php echo __("Permalinks of albums"); ?></a> 
	</li>
	
	<li <?php echo isActive($selected, "folders")?>>
		<a href="<?php echo path("@account_permalink_folder_list"); ?>"><?php echo __("Permalinks of folders"); ?></a>
	</li>
	
	<li <?php echo isActive($selected, "files")?>>
		<a href="<?php echo path("@account_permalink_file_list"); ?>"><?php echo __("Permalinks of files"); ?></a>
	</li>
</ul>
