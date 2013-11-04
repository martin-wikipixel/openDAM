<div id="account-permalink-file-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account_permalink"), "text" => __("My permalinks")),
			array("link" => path("@account_permalink_file_list"), "text" => __("Files")),
		));
	?>

	<?php include_partial("account/tab", array("selected" => "permalink"));?>
	<?php include_partial("account/permalinkTab", array("selected" => "files"));?>

	<table class="table">
		<thead>
			<tr>
				<th class="span2"><?php echo __("File"); ?></th>
				<th class="span2"><?php echo __("Format"); ?></th>
				<th class="span4"><?php echo __("Links"); ?></th>
				<th class="span2"><?php echo __("Created at"); ?></th>
				<th class="span2"><?php echo __("Actions"); ?></th>
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
					<?php $file = FilePeer::retrieveByPK($permalink->getObjectId())?>
					<tr>
						<td><?php echo $file->getVirtualPathname()?></td>
						<td><?php echo $permalink->getType() == 1?__('Original'):__('Web') ?></td>
						<td>
							<?php include_partial("account/permalinkLinksColumn", 
							array("permalink" => $permalink, "routeName" => "permalink_show"))?>
						</td>
						<td>
							<?php echo my_format_date_time($permalink->getCreatedAt())?>
						</td>
						<td>
							<a class="btn"  href="<?php echo path("@file_show", array("id" => $file->getId()));?>">
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
	
	<?php echo pagination($permalinks, "@account_permalink_file_list");?>
</div>