<div id="admin-group-permission-list-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_group_list"), "text" => __("group.title")),
			array("link" => path("@admin_group_edit", array("id" => $group->getId())), "text" => $group->getName()),
			array("link" => path("@admin_group_permission_list", array("id" => $group->getId())), "text" => __("Manage permissions")),
		));
	?>
	
	<?php include_partial("admin/groupTab", array("selected" => "permissions", "group" => $group)); ?>
	
	<?php include_partial("admin/groupRightAdd", array("group" => $group, "albums" => $albums, "csrfToken" => $csrfToken, 
		"roles" => $roles));?>
	
	<?php if (!count($rights)):?>
		<?php echo __("No right found.");?>
	<?php else:?>
		<table id="permissions-table" class="table table-bordered">
			<thead class="text-center">
				<tr>
					<th class="album" rowspan="2"><?php echo __("Albums")?></th>
					<th colspan="3"><?php //echo __("Roles")?><?php echo __("Permissions"); ?></th>
					<th class="album" rowspan="2"><?php echo __("Actions");?></th>
				</tr>
				<tr>
					<?php foreach ($roles as $role) :?>
						<th><?php echo $role->getName(); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
	
			<tbody>
				<?php foreach ($rights as $right) :?>
					<?php
						$album = $right->getGroupe(); 
					?>
					<tr>
						<td><i class="icon-book"></i> <?php echo $album->getName(); ?></td>
						<?php foreach ($roles as $role) :?>
							<?php 
								$roleId = $role->getId();
								$radioName = "radio-".$album->getId();
							?>
							<td class="text-center">
								<?php if (!$album->getFree() || ($album->getFree() && ($roleId == RolePeer::__ADMIN 
										|| $roleId == $album->getFreeCredential()))): ?>
									<input
										type="radio" <?php echo $roleId == $right->getRole() ? "checked" : ""; ?> 
										name="<?php echo $radioName;?>" value="<?php echo $roleId; ?>" 
										data-album-id="<?php echo $album->getId()?>"
										data-group-id="<?php echo $group->getId()?>"
										>
								<?php else: ?>
									-
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
						<td class="text-center">
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_group_permission_delete", 
									array("album" => $album->getId(), "group"=> $group->getId(), "csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;?>
</div>