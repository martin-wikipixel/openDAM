<?php
	$errors = $form->hasErrors() ? $form->getErrors() : Array();
?>
<div class="container">
	<div class="row">
		<div class="span12 title-folder">
			<h2><?php echo __("Please log-in to access the main folder")?></h2>
		</div>
	</div>
	<div class="row">
		<div class="span12">
			<form class="form-horizontal" name="login-group" id="login-group" action="" method="post">
				<?php echo $form['_csrf_token']->render(); ?>
				<?php echo $form['id']->render(); ?>

				<div class="control-group <?php echo !empty($errors) ? "error" : ""; ?>">
					<label class="control-label" for="data_password"><?php echo __("Password"); ?></label>
					<div class="controls">
						<?php echo $form['password']->render(); ?>
						<?php if(!empty($errors)) : ?>
							<span class="help-inline">
								<?php foreach($errors as $error) : ?>
									<?php echo $error; ?>
								<?php endforeach; ?>
							</span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group">
					<div class="controls">
						<a href="javascript: void(0);" class="btn-header submit"><?php echo __("Login"); ?></a>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">   
jQuery(document).ready(function() {
	jQuery("#data_password").bind("keydown", function(event) {
		var code = (event.keyCode ? event.keyCode : event.which);

		if(code == 13)
			jQuery('#login-group').submit();
	});

	jQuery(".submit").bind("click", function() {
		jQuery('#login-group').submit();
	});
});
</script>
