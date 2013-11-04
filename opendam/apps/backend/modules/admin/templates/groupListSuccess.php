<div id="admin-group-list-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_group_list"), "text" => __("group.title")),
		));
	?>

	<div class="commands-top">
		<a class="btn btn-primary" href="<?php echo path("@admin_group_new"); ?>">
			<i class="icon-plus-sign"></i> <?php echo __("Add group"); ?>
		</a>
	</div>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Description")?></th>
				<th><?php echo __("QTY. User(s)")?></th>
				<th><?php echo __("Created at")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($groups->getResults())):?>
				<tr>
					<td colspan="5"><?php echo __("No user group found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($groups as $group):?>
					<tr>
						<td><?php echo $group->getTitle();?></td>
						<td><?php echo $group->getDescription();?></td>
						<td><?php echo $group->countUsers();?></td>
						<td><?php echo my_format_date_time($group->getCreatedAt())?></td>
						<td>
							<a class="btn" href="<?php echo path("admin_group_edit", array("id" => $group->getId()));?>"><?php echo __("Edit"); ?></a>
							
							<a class="btn" href="<?php echo path("@admin_group_permission_list", array("id" => $group->getId())); ?>">
								<?php echo __("Manage"); ?> 
							</a>
		
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_group_delete", array("id" => $group->getId(), 
									"csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>

	<?php echo pagination($groups, "@admin_group_list");?>
</div>