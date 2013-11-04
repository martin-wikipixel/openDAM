<div id="admin-tag-album-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_tag_list"), "text" => __("Keyword management")),
			array("link" => path("@admin_tag_edit", array("id" => $tag->getId())), "text" => $tag->getName()),
			array("link" => path("@admin_tag_album_list", array("tag" => $tag->getId())), "text" => __("Albums")),
		));
	?>
	
	<?php include_partial("admin/tagTab", array("tag" => $tag, "selected" => "albums"));?>
	
	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($albums->getResults())):?>
				<tr>
					<td colspan="2"><?php echo __("No album found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($albums as $album):?>
					<tr>
						<td><?php echo $album->getName()?></td>
						<td>
							<a class="btn"  href="<?php echo path("@album_show", array("id" => $album->getId()));?>">
								<?php echo __("Open")?>
							</a>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("admin_tag_album_delete", array(
								"tag" => $tag->getId(), "album" => $album->getId(), "csrfToken" => $csrfToken
							))?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($albums, "@admin_tag_album_list");?>
</div>