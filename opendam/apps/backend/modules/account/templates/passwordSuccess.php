<div id="account-password-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account_password"), "text" => __("Change my password")),
		));
	?>
	
	<?php include_partial("account/tab", array("selected" => "password"));?>
	
	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>

		<div class="control-group">
			<label class="control-label required" for="data_password"><?php echo __("Current password")?></label>
			<div class="controls">
				<?php echo $form["password"]->render(); ?>
				<?php echo $form["password"]->renderError(); ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label required" for="data_new_password"><?php echo __("New password")?></label>
			<div class="controls">
				<?php echo $form["new_password"]->render(); ?>
				<?php echo $form["new_password"]->renderError(); ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label required" for="data_verify_password"><?php echo __("Verify new password")?></label>
			<div class="controls">
				<?php echo $form["verify_password"]->render(); ?>
				<?php echo $form["verify_password"]->renderError(); ?>
			</div>
		</div>

		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Save")?></button>
		</div>
	</form>
</div>