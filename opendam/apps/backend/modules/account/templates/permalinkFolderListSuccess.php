<div id="account-permalink-folder-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account_permalink"), "text" => __("My permalinks")),
			array("link" => path("@account_permalink_folder_list"), "text" => __("Folders")),
		));
	?>

	<?php include_partial("account/tab", array("selected" => "permalink"));?>
	<?php include_partial("account/permalinkTab", array("selected" => "folders"));?>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Folder"); ?></th>
				<th><?php echo __("Format"); ?></th>
				<th><?php echo __("Links"); ?></th>
				<th><?php echo __("Created at"); ?></th>
				<th><?php echo __("Actions"); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if (!count($permalinks)):?>
				<tr>
					<td colspan="5">
						<?php echo __("No permalink found.")?>
					</td>
				</tr>
			<?php else:?>
				<?php foreach ($permalinks as $permalink): ?>
					<?php $folder = FolderPeer::retrieveByPK($permalink->getObjectId())?>
					<tr>
						<td><?php echo $folder->getPathname()?></td>
						<td><?php echo $permalink->getType() == 1?__('Original'):__('Web') ?></td>
						<td>
							<?php include_partial("account/permalinkLinksColumn", 
							array("permalink" => $permalink, "routeName" => "permalink_folder"))?>
						</td>
						<td>
							<?php echo my_format_date_time($permalink->getCreatedAt())?>
						</td>
						<td>
							<a class="btn"  href="<?php echo path("@folder_show", array("id" => $folder->getId()));?>">
								<?php echo __("Open")?>
							</a>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@account_permalink_delete", 
									array("id" => $permalink->getId(), "csrfToken" => $csrfToken));?>">
								<i class="icon-trash"></i> <?php echo __("Delete")?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($permalinks, "@account_permalink_folder_list");?>
</div>