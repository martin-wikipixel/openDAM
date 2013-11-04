<div id="account-module-list-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account_password"), "text" => __("Change my password")),
		));
	?>
	
	<?php include_partial("account/tab", array("selected" => "module"));?>

	<?php include_partial("account/addModule", array("newModules" => $newModules, "csrfToken" => $csrfToken))?>

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
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@account_module_delete", array("_module" => $module->getId(), 
									"csrfToken" => $csrfToken)); ?>" >
								<i class="icon-trash"></i> <?php echo __("Remove"); ?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
</div>