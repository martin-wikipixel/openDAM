<form class="form-inline" name="add-permission" method="post" action="<?php echo path("admin_group_permission_add", 
		array("group" => $group->getId()));?>">
	<label><?php echo __("Select group you want to add right")?></label>

	<input type="hidden" name="csrfToken" value="<?php echo $csrfToken;?>">

	<select name="album">
		<option value=""><?php echo __("Select"); ?></option>
		<?php foreach ($albums as $album) :?>
			<option value="<?php echo $album->getId(); ?>">
				<?php echo $album->getName(); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<select class="hide" name="role">
		<option value=""><?php echo __("Select")?></option>
		<?php foreach ($roles as $role):?>
			<option value="<?php echo $role->getId()?>"><?php echo $role->getName()?></option>
		<?php endforeach;?>
	</select>

	<button class="btn btn-primary hide" data-action="submit"><?php echo __("Add");?></button>
</form>