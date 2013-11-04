<div id="admin-tag-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_tag_list"), "text" => __("Keyword management")),
			array("link" => path("@admin_tag_edit", array("id" => $tag->getId())), "text" => __("Edit")),
		));
	?>
	
	<?php include_partial("admin/tagTab", array("tag" => $tag, "selected" => "edit"));?>
	
	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>

		<div class="control-group">
			<label class="control-label required" for="data_name"><?php echo __("Name")?></label>
			<div class="controls">
				<?php echo $form["name"]->render(); ?>
				<?php echo $form["name"]->renderError(); ?>
			</div>
		</div>
		
		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Save")?></button>
		</div>
	</form>
</div>