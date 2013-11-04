<div id="admin-user-folder-right-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_edit", array("id" => $user->getId())), "text" => $user->__toString()),
			array("link" => path("@admin_user_folder_right_list", array("user" => $user->getId())), "text" => __("Folders's rights")),
		));
	?>

	<?php include_partial("admin/userTab", array("user" => $user, "selected" => "folder_right")); ?>

</div>