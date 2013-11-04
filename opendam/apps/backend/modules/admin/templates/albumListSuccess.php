<div id="admin-album-list-page" class="span12">
	<?php	
		$orderBy = $orderBy->getRawValue();

		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_album_list"), "text" => __("Albums")),
		));
	?>

	<div class="commands-top">
		<a class="btn btn-primary" href="<?php echo path("@admin_album_new"); ?>">
			<i class="icon-plus-sign"></i> <?php echo __("Add album"); ?>
		</a>
	</div>

	<div class="search-block clearfix">
		<div class="pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy", "page")));?>
				
				<label><?php echo __("Sort albums by")?></label>
				<select name="orderBy[]">
					<option <?php if (in_array("name_asc", $orderBy)) echo "selected";?> value="name_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("name_desc", $orderBy)) echo "selected";?> value="name_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("created_at_asc", $orderBy)) echo "selected";?> value="created_at_asc"><?php echo __("Date of creation ascending")?></option>
					<option <?php if (in_array("created_at_desc", $orderBy)) echo "selected";?> value="created_at_desc"><?php echo __("Date of creation descending")?></option>
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
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Description")?></th>
				<th><?php echo __("Created at")?></th>
				<th><?php echo __("Created by")?></th>
				<th><?php echo __("Number of files");?></th>
				<th><?php echo __("Size")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($albums->getResults())):?>
				<tr>
					<td colspan="7"><?php echo __("No album found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($albums as $album):?>
					<tr>
						<td><?php echo $album->getName();?></td>
						<td><?php echo $album->getDescription();?></td>
						<td><?php echo my_format_date_time($album->getCreatedAt())?></td>
						<td>
							<a href="<?php echo path("admin_user_edit", array("id" => $album->getUserId()))?>">
								<?php echo $album->getUser();?>
							</a>
						</td>
						<td><?php echo $album->getNumberOfFiles()?></td>
						<td><?php echo MyTools::getSize($album->getSize())?></td>
						<td>
							<a class="btn" href="<?php echo path("@album_show", array("id" => $album->getId()));?>">
								<?php echo __("Open")?>
							</a>

							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_album_delete", array("id" => $album->getId(), "csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($albums, "@admin_album_list");?>
</div>
