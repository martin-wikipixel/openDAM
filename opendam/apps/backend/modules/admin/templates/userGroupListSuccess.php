<div id="admin-user-group-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_edit", array("id" => $user->getId())), "text" => $user->__toString()),
			array("link" => path("@admin_user_group_list", array("user" => $user->getId())), "text" => __("group.title")),
		));
	?>
	
	<?php include_partial("admin/userTab", array("user" => $user, "selected" => "group")); ?>

	<?php include_partial("admin/userAddGroup", array("user" => $user, "newGroups" => $newGroups, "csrfToken" => $csrfToken)); ?>
	
	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($groups)):?>
				<tr>
					<td colspan="2"><?php echo __("No units found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($groups as $group):?>
					<tr>
						<td>
							<?php echo $group->getTitle();?>
						</td>
						<td>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_user_group_delete", array("user" => $user->getId(), 
									"group" => $group->getId(), "csrfToken" => $csrfToken));?>">
								<i class="icon-trash"></i> <?php echo __("Remove"); ?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
</div>