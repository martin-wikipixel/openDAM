<div id="admin-user-password-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_edit", array("id" => $user->getId())), "text" => $user->__toString()),
			array("link" => path("@admin_user_password", array("id" => $user->getId())), "text" => __("Password")),
		));
	?>

	<?php include_partial("admin/userTab", array("user" => $user, "selected" => "password")); ?>

	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>
		<?php echo $form["id"]->render(); ?>

		<div class="control-group">
			<label class="control-label required" for="data_password"><?php echo __("Password")?></label>
			<div class="controls">
				<?php echo $form["password"]->render(); ?>
				<?php echo $form["password"]->renderError(); ?>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label required" for="data_confirm_password"><?php echo __("Confirm password")?></label>
			<div class="controls">
				<?php echo $form["confirm_password"]->render(); ?>
			</div>
		</div>
		
		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Save");?></button>
		</div>
	</form>
</div>