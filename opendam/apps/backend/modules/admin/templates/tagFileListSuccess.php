<div id="admin-tag-file-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_tag_list"), "text" => __("Keyword management")),
			array("link" => path("@admin_tag_edit", array("id" => $tag->getId())), "text" => $tag->getName()),
			array("link" => path("@admin_tag_file_list", array("tag" => $tag->getId())), "text" => __("Files")),
		));
	?>
	
	<?php include_partial("admin/tagTab", array("tag" => $tag, "selected" => "files"));?>
	
	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($files->getResults())):?>
				<tr>
					<td colspan="2"><?php echo __("No file found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($files as $file):?>
					<tr>
						<td><?php echo $file->getVirtualPathname()?></td>
						<td>
							<a class="btn"  href="<?php echo path("@file_show", array("id" => $file->getId()));?>">
								<?php echo __("Open")?>
							</a>
							
							<a class="btn btn-danger" data-action="delete" href="<?php //echo path("admin_tag_file_delete", array(
								//"tag" => $tag->getId(), "file" => $file->getId(), "csrfToken" => $csrfToken)?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($files, "@admin_tag_file_list");?>
</div>