<div id="admin-file-duplicate-list-page" class="span12">
	<?php 
		$orderBy = $orderBy->getRawValue();

		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_file_duplicate_list"), "text" => __("Manage duplicates")),
		));
	?>
	<div class="search-block clearfix">
		<div class="pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy", "page")));?>
				
				<label><?php echo __("Sort files by")?></label>

				<select name="orderBy[]"> 
					<option <?php if (in_array("name_asc", $orderBy)) echo "selected";?> value="name_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("name_desc", $orderBy)) echo "selected";?> value="name_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("size_asc", $orderBy)) echo "selected";?> value="size_asc"><?php echo __("Size ascending")?></option>
					<option <?php if (in_array("size_desc", $orderBy)) echo "selected";?> value="size_desc"><?php echo __("Size descending")?></option>
					<option <?php if (in_array("checksum_asc", $orderBy)) echo "selected";?> value="checksum_asc"><?php echo __("Hash ascending")?></option>
					<option <?php if (in_array("checksum_desc", $orderBy)) echo "selected";?> value="checksum_desc"><?php echo __("Hash descending")?></option>
				</select>
				
				<button class="btn"><i class="icon-search"></i></button>
			</form>
		</div>

		<form class="form-search pull-right">
			<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>

			<div class="input-append">
				<input name="keyword" type="text" class="input-medium search-query" placeholder="<?php echo __("Search")?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th class="span2"><?php echo __("Thumbnail")?></th>
				<th class="span2"><?php echo __("Filename")?></th>
				<th class="span1"><?php echo __("Size")?></th>
				<th class="span1"><?php echo __("Folder")?></th>
				<th class="span1"><?php echo __("Group")?></th>
				<th class="span2"><?php echo __("Hash")?></th>
				<th class="span1"><?php echo __("Created at")?></th>
				<th class="span2"><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($files->getResults())):?>
				<tr>
					<td colspan="8"><?php echo __("No file found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($files as $file):?>
					<tr>
						<td>
							<?php if (file_exists($file->getPath()."/".$file->getThumb100())) : ?>
								<img src="<?php echo "/".$file->getPath(false)."/".$file->getThumb100(); ?>" />
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
						
						<td>
							<?php echo $file->getName(); ?>
						</td>
						<td><?php echo myTools::getSize($file->getSize()); ?></td>
						<td><?php echo $file->getFolder(); ?></td>
						<td><?php echo $file->getGroupe(); ?></td>
						<td><?php echo $file->getChecksum(); ?></td>
						<td><?php echo my_format_date_time($file->getCreatedAt()); ?></td>
						<td>
							<a class="btn" target="_blank" href="<?php echo path("@admin_file_download", array("id" => $file->getId()))?>">
								<?php echo __("Download")?>
							</a>
							<a class="btn btn-danger" data-action=delete href="<?php echo path("@admin_file_delete", array("id" => $file->getId(), 
									"csrfToken" => $csrfToken))?>">
								<i class="icon-trash"></i> <?php echo __("Remove"); ?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($files, "@admin_file_duplicate_list", query_params());?>
</div>