<div id="admin-tag-folder-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_tag_list"), "text" => __("Keyword management")),
			array("link" => path("@admin_tag_edit", array("id" => $tag->getId())), "text" => $tag->getName()),
			array("link" => path("@admin_tag_folder_list", array("tag" => $tag->getId())), "text" => __("Folders")),
		));
	?>
	
	<?php include_partial("admin/tagTab", array("tag" => $tag, "selected" => "folders"));?>
	
	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($folders->getResults())):?>
				<tr>
					<td colspan="2"><?php echo __("No folder found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($folders as $folder):?>
					<tr>
						<td><?php echo $folder->getPathname()?></td>
						<td>
							<a class="btn"  href="<?php echo path("@folder_show", array("id" => $folder->getId()));?>">
								<?php echo __("Open")?>
							</a>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("admin_tag_folder_delete", array(
								"tag" => $tag->getId(), "folder" => $folder->getId(), "csrfToken" => $csrfToken
							))?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($folders, "@admin_tag_folder_list");?>
</div>