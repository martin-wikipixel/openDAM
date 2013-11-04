<div id="admin-module-list-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_module_list"), "text" => __("Modules")),
		));
	?>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Active")?></th>
				<th><?php echo __("Value")?></th>
			</tr>
		</thead>
	
		<tbody>
			<?php foreach($customerModules as $customerModule) : ?>
				<?php 
					$module = $customerModule->getModule();
					$values = ModuleValuePeer::retrieveByModuleId($customerModule->getModuleId());
				?>
				<tr data-customer-id="<?php echo $customer->getId()?>" data-module-id="<?php echo $module->getId()?>">
					<td>
						<?php echo $module->getTitle();?>
					</td>
					
					<td>
						<?php if ($module->getDeactivated()) : ?>
							<select class="active">
								<?php $tab = array(0 => __("No"), 1 => __("Yes")); ?>
								<?php foreach ($tab as $key => $value) : ?>
									<option value="<?php echo $key; ?>" <?php echo $key == $customerModule->getActive() ? "selected" : ""; ?>>
										<?php echo $value; ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
					
					<td>
						<?php if (count($values) > 0) : ?>
							<select class="values">
								<option value=""><?php echo __("Select")?></option>
								<?php foreach($values as $value) : ?>
									<option value="<?php echo $value->getId(); ?>" <?php echo $value->getId() == $customerModule->getModuleValueId() ? "selected" : ""; ?>>
										<?php echo $value->getDescription(); ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>