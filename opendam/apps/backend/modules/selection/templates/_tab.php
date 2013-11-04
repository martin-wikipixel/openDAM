<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "edit")?>>
		<a href="<?php echo path("@selection_edit", array("id" => $selection->getId())); ?>">
			<?php echo __("Edit"); ?>
		</a> 
	</li>
	
	<li <?php echo isActive($selected, "file")?>>
		<a href="<?php echo path("@selection_file_list", array("selection" => $selection->getId())); ?>">
			<?php echo __("Files"); ?> (<?php echo BasketHasContentPeer::countFiles($selection->getId())?>)
		</a>
	</li>
	
	<li <?php echo isActive($selected, "send")?>>
		<a href="<?php echo path("@selection_email_send", array("selection" => $selection->getId())); ?>">
			<?php echo __("Send by email"); ?>
		</a>
	</li>

	<li <?php echo isActive($selected, "comment")?>>
		<a href="<?php echo path("selection_comment_list", array("selection" => $selection->getId()));?>">
			<?php echo __("Comments"); ?> (<?php echo $selection->countBasketHasComments()?>)
		</a>
	</li>
</ul>