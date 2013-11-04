<div id="admin-group-user-list-page" class="span12" data-group-id="<?php echo $group->getId()?>">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_group_list"), "text" => __("group.title")),
			array("link" => path("@admin_group_edit", array("id" => $group->getId())), "text" => $group->getTitle()),
			array("link" => path("@admin_group_user_list", array("id" => $group->getId())), "text" => __("Users")),
		));
	?>
	
	<?php include_partial("admin/groupTab", array("selected" => "users", "group" => $group)); ?>

	<form id="user-add-form" class="form-inline" method="post" action="<?php echo path("admin_group_user_add", 
			array("group" => $group->getId()))?>">
		<label><?php echo __("Add existing user")?></label>
		<input type="text" name="user">
	</form>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Lastname")?></th>
				<th><?php echo __("Firstname")?></th>
				<th><?php echo __("Email")?></th>
				<th><?php echo __("Comment")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($users)):?>
				<tr>
					<td colspan="4"><?php echo __("No user found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($users as $user):?>
					<tr>
						<td><?php echo $user->getLastname();?></td>
						<td><?php echo $user->getFirstname();?></td>
						<td><?php echo $user->getEmail();?></td>
						<td>
							<?php if ($user->getComment()) : ?>
								<?php echo "<em>(".$user->getComment().")</em>"; ?>
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
						<td>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_group_user_delete", 
								array("group" => $group->getId(), "user" => $user->getId(), "csrfToken" => $csrfToken))?>">
								<i class="icon-trash"></i> <?php echo __("Remove"); ?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
</div>