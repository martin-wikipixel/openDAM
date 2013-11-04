<div class="row-fluid">
	<?php if (count($newModules) > 0): ?>
		<form class="form-inline span6" method="post" action="<?php echo path("@account_module_add"); ?>">
			<input type="hidden" name="csrfToken" value="<?php echo $csrfToken;?>">

			<label><?php echo __("Add user's module");?></label>
			<select name="_module" required>
				<option value=""><?php echo __("Select")?></option>
				<?php foreach($newModules as $module) : ?>
					<option value="<?php echo $module->getId(); ?>"><?php echo $module->getTitle(); ?></option>
				<?php endforeach; ?>
			</select>
					
			<button class="btn btn-primary"><i class="icon-plus-sign"></i> <?php echo __("Add")?></button>
		</form>
	<?php else: ?>
		<p class="alert alert-info span3">
			<?php echo __("All modules have been added to this user."); ?>
		</p>
	<?php endif; ?>
</div>