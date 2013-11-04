<div id="admin-user-module-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_edit", array("id" => $user->getId())), "text" => $user->__toString()),
			array("link" => path("@admin_user_module_list", array("user" => $user->getId())), "text" => __("Modules")),
		));
	?>

	<?php include_partial("admin/userTab", array("user" => $user, "selected" => "module")); ?>

	<div class="row-fluid">
		<?php if (count($newModules) > 0): ?>
			<form class="form-inline span6" method="post" action="<?php echo path("@admin_user_module_add"); ?>">
				<input type="hidden" name="csrfToken" value="<?php echo $csrfToken;?>">
				<input type="hidden" name="user" value="<?php echo $user->getId(); ?>">
					
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
			<p class="alert alert-info span4">
				<?php echo __("All modules have been added to this user."); ?>
			</p>
		<?php endif; ?>
	</div>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Active")?></th>
				<th><?php echo __("Value")?></th>
				<th><?php echo __("Action")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($userModules)):?>
				<tr>
					<td colspan="4"><?php echo __("No modules found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($userModules as $userModule):?>
					<?php 
						$module = $userModule->getModule();
						$values = ModuleValuePeer::retrieveByModuleId($userModule->getModuleId());
					?>
					<tr data-user-module-id="<?php echo $userModule->getId()?>" data-module-id="<?php echo $module->getId()?>" data-user-id="<?php echo $user->getId()?>">
						<td><?php echo $module->getTitle(); ?></td>
						<td>
							<?php if(!$module->getDeactivated()) : ?>
								-
							<?php else: ?>
								<select class="active">
									<?php $tab = array(0 => __("No"), 1 => __("Yes")); ?>
									<?php foreach($tab as $key => $value) : ?>
										<option value="<?php echo $key; ?>" <?php echo $key == $userModule->getActive() ? "selected" : ""; ?>><?php echo $value; ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
						</td>
						
						<td>
							<?php if (count($values) > 0): ?>
								<select>
									<option value=""></option>
									<?php foreach($values as $value) : ?>
										<option value="<?php echo $value->getId(); ?>" <?php echo $value->getId() == $userModule->getModuleValueId() ? "selected" : ""; ?>>
											<?php echo $value->getDescription(); ?>
										</option>
									<?php endforeach; ?>
								</select>
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
						
						<td>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_user_module_delete", array("user" => $user->getId(), 
									"_module" => $module->getId(), "csrfToken" => $csrfToken)); ?>" >
								<i class="icon-trash"></i> <?php echo __("Remove"); ?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
</div>

