	<div class="row-fluid">
		<?php if (count($newGroups)):?>
			<form class="form-inline span8" method="post" action="<?php echo path("@admin_user_group_add", array("user" => $user->getId()));?>">
				<input type="hidden" name="csrfToken" value="<?php echo $csrfToken;?>">

				<label><?php echo __("Add user into these group(s)"); ?></label>
		
				<select name="group" required>
					<option value=""><?php echo __("Select")?></option>
					<?php foreach ($newGroups as $group):?>
						<option value="<?php echo $group->getId()?>"><?php echo $group->getTitle()?></option>
					<?php endforeach;?>
				</select>
				
				<button class="btn btn-primary"><i class="icon-plus-sign"></i> <?php echo __("Add")?></button>
			</form>
		<?php else:?>
			<p class="alert alert-info span3">
				<?php echo __("All units have been added to this user."); ?>
			</p>
		<?php endif;?>
	</div>