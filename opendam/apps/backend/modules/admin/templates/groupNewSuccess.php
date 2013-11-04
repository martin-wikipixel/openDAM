<div id="admin-group-new-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_group_list"), "text" => __("group.title")),
			array("link" => path("@admin_group_new"), "text" => __("Add group")),
		));
	?>
	
	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>

		<div class="control-group">
			<label class="control-label required" for="data_name"><?php echo __("Name")?></label>
			<div class="controls">
				<?php echo $form["name"]->render(); ?>
				<?php echo $form["name"]->renderError(); ?>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label required" for="data_description"><?php echo __("Description")?></label>
			<div class="controls">
				<?php echo $form["description"]->render(); ?>
				<?php echo $form["description"]->renderError(); ?>
			</div>
		</div>
		
		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Add")?></button>
		</div>
	</form>
</div>