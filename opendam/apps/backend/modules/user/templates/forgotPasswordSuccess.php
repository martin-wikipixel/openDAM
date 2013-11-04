<div class="container">
	<div id="user-password" class="row">
		<form class="span6 offset3" method="post">
			<?php echo $form['_csrf_token']->render(); ?>
			<?php echo $form['referer']->render(); ?>

			<div class="header clearfix">
				<h3><?php echo __("Please enter your email")?></h3>
			</div>

			<div class="body">
				<?php echo $form['email']->render(); ?>
				
				<button class="button btnBS"><?php echo __("Send my password")?></button>
			</div>
		</form>
	</div>
</div>