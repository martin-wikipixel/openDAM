<div id="selection-email-send-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@selection_list"), "text" => __("Collections")),
			array("link" => path("@selection_email_send", array("selection" => $selection->getId())), "text" => $selection->getTitle()),
		));
	?>
	
	<?php include_partial("selection/tab", array("selection" => $selection, "selected" => "send"))?>
	
	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>

		<!--  
		<div class="control-group">
			<label class="control-label required"><?php echo __("User Sender")?></label>
			<div class="controls">
				<span class="text-node"><?php echo $sender; ?></span>
			</div>
		</div>
		-->

		<div class="control-group">
			<label class="control-label required" for="data_receivers" >
				<?php echo __("Receivers")?> <i>(<?php echo __("separated by commas"); ?>)</i>
			</label>
			<div class="controls">
				<?php echo $form["receivers"]->render(); ?>
				<?php echo $form["receivers"]->renderError(); ?>
			</div>
		</div>

		<?php if ($sf_user->isAdmin()) : ?>
			<div class="control-group">
				<label class="control-label" for="data_groups" ><?php echo __("Or select group")?></label>
				<div class="controls">
					<?php echo $form["groups"]->render(); ?>
					<?php echo $form["groups"]->renderError(); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="control-group">
			<label class="control-label" for="data_subject" ><?php echo __("Subject")?></label>
			<div class="controls">
				<?php echo $form["subject"]->render(); ?>
				<?php echo $form["subject"]->renderError(); ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="data_message" ><?php echo __("Message")?></label>
			<div class="controls">
				<?php echo $form["message"]->render(); ?>
				<?php echo $form["message"]->renderError(); ?>
			</div>
		</div>

		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Send");?></button>
		</div>
	</form>
</div>