<div id="selection-file-list-page" class="span12" data-file-count="<?php echo $selectionFiles->count()?>">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@selection_list"), "text" => __("Collections")),
			array("link" => path("@selection_edit", array("id" => $selection->getId())), "text" => $selection->getTitle()),
			array("link" => path("@selection_file_list", array("selection" => $selection->getId())), "text" => __("Files")),
		));
	?>
	
	<?php include_partial("selection/tab", array("selection" => $selection, "selected" => "file"))?>
	
	<?php if (!count($selectionFiles)):?>
		<?php echo __("Your selection is empty.")?>
	<?php else:?>
		<a class="btn btn-primary" href="<?php echo path("selection_download", array("selection" => $selection->getId()))?>">
			<?php echo __("Download all files")?>
		</a>
		
		<table class="table">
			<thead>
				<tr>
					<th><?php echo __("Files")?></th>
					<th><?php echo __("Actions")?></th>
				</tr>
			</thead>

			<tbody>
					<?php foreach ($selectionFiles as $selectionFile):?>
						<?php 
							$file = $selectionFile->getFile();
						?>
						<tr>
							<td>
								<div class="pull-left">
									<?php if ($file->exists()):?>
										<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100"));?>" />
									<?php endif;?>
								</div>
								<div>
									<strong><?php echo __("Group"); ?> :</strong> <?php echo $file->getGroupe(); ?><br />
									<strong><?php echo __("Folder"); ?> :</strong> <?php echo $file->getFolder(); ?><br />
									<strong><?php echo __("Filename"); ?> :</strong> <?php echo $file; ?><br />
									<strong><?php echo __("Size"); ?> :</strong> <?php echo MyTools::getSize($file->getSize()); ?>
								</div>
							</td>
							<td>
								<a class="btn btn-danger" data-action="delete" href="<?php echo path("selection_file_delete", 
									array(
										"selection" => $selectionFile->getBasketId(),
										"file"   => $selectionFile->getFileId() 
								))?>">
									<i class="icon-trash"></i> <?php echo __("Remove");?>
								</a>
								
								<a class="btn" href="<?php echo path("file_show", 
										array("id" => $file->getId()));?>">
									<?php echo __("Open");?>
								</a>
							</td>
						</tr>
					<?php endforeach;?>
			</tbody>
		</table>

		<?php echo pagination($selectionFiles, "@selection_file_list");?>
	<?php endif;?>
</div>