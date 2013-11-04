<div class="container">
	<div id="user-login" class="row">
		<form class="span6 offset3" method="post">
			<?php echo $form['_csrf_token']->render(); ?>

			<div class="header clearfix">
				<h3><?php echo __("LOGIN")?></h3>
			</div>

			<div class="body">
				<?php echo $form["username"]->render()?>
				<?php echo $form["password"]->render()?>

				<span class="help-inline"><a href="<?php echo url_for("@forgot-password"); ?>" class="forgot-password"><?php echo __("Forgot your password?"); ?></a></span>
				
				<button class="button btnBS"><?php echo __("LOGIN")?></button>
			</div>
		</form>
	</div>
</div>
