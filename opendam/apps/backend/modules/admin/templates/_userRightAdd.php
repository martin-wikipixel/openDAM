<div class="row">
	<?php if (!count($albums)):?>
		<p class="alert alert-info span3">
			<?php echo __("No album to add."); ?>
		</p>
	<?php else:?>
		<form class="form-inline span12" name="add-permission" method="post" 
			action="<?php echo path("admin_user_album_right_add", array("user" => $user->getId()));?>">
				
			<input type="hidden" name="csrfToken" value="<?php echo $csrfToken;?>">
		
			<label><?php echo __("Select group you want to add right")?></label>
		
			<select name="album" required>
				<option value=""><?php echo __("Select"); ?></option>
				<?php foreach ($albums as $album) :?>
					<option value="<?php echo $album->getId(); ?>">
						<?php echo $album->getName(); ?>
					</option>
				<?php endforeach; ?>
			</select>
			
			<select class="hide" name="role" required>
				<option><?php echo __("Select")?></option>
				<?php foreach ($roles as $role):?>
					<option value="<?php echo $role->getId()?>"><?php echo $role->getName()?></option>
				<?php endforeach;?>
			</select>

			<button class="btn btn-primary hide" data-action="submit"><?php echo __("Add");?></button>
		</form>
	<?php endif;?>
</div>