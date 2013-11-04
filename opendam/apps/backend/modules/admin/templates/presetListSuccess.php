<div id="admin-preset-list-page" class="span12">
	<?php
		$orderBy = $orderBy->getRawValue();

		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_preset_list"), "text" => __("Presets")),
		));
	?>

	<div class="commands-top">
		<a class="btn btn-primary" role="button" href="<?php echo path("@admin_preset_new"); ?>">
			<i class="icon-plus-sign"></i> <?php echo __("Add preset"); ?>
		</a>
	</div>

	<div class="search-block clearfix">
		<div class="pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy", "page")));?>
				
				<label><?php echo __('Sort modules by')?></label>
				<select name="orderBy[]">
					<option <?php if (in_array("name_asc", $orderBy)) echo "selected";?> value="name_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("name_desc", $orderBy)) echo "selected";?> value="name_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("created_at_asc", $orderBy)) echo "selected";?> value="created_at_asc"><?php echo __("Creation date ascending")?></option>
					<option <?php if (in_array("created_at_desc", $orderBy)) echo "selected";?> value="created_at_desc"><?php echo __("Creation date descending")?></option>
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
				<th><?php echo __("Created at")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($presets)):?>
				<tr>
					<td colspan="3"><?php echo __("No preset found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($presets as $preset):?>
					<tr>
						<td><?php echo $preset->getName();?></td>
						<td><?php echo my_format_date_time($preset->getCreatedAt())?></td>
						<td>
							<a class="btn" href="<?php echo path("@admin_preset_edit", array("id" => $preset->getId()));?>">
								<?php echo __("Details");?>
							</a>
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_preset_delete", 
									array("id" => $preset->getId(), "csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($presets, "@admin_preset_list");?>
</div>
