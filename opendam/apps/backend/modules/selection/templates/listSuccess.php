<div id="selection-list-page" class="span12">
	<?php	
		$orderBy = $orderBy->getRawValue();
	?>

	<?php include_partial("global/breadCrumb", array("bread_crumbs"=> $breadCrumbs));?>

	<div class="search-block clearfix">
		<div class="pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy", "page")));?>
				
				<label><?php echo __("Sort selections by")?></label>
				<select name="orderBy[]">
					<option <?php if (in_array("title_asc", $orderBy)) echo "selected";?> value="title_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("title_desc", $orderBy)) echo "selected";?> value="title_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("created_at_asc", $orderBy)) echo "selected";?> value="created_at_asc"><?php echo __("Date of creation ascending")?></option>
					<option <?php if (in_array("created_at_desc", $orderBy)) echo "selected";?> value="created_at_desc"><?php echo __("Date of creation descending")?></option>
					<option <?php if (in_array("code_asc", $orderBy)) echo "selected";?> value="code_asc"><?php echo __("Code ascending")?></option>
					<option <?php if (in_array("code_desc", $orderBy)) echo "selected";?> value="code_desc"><?php echo __("Code descending")?></option>
					<option <?php if (in_array("type_asc", $orderBy)) echo "selected";?> value="type_asc"><?php echo __("Type ascending")?></option>
					<option <?php if (in_array("type_desc", $orderBy)) echo "selected";?> value="type_desc"><?php echo __("Type descending")?></option>
				</select>
				
				<button class="btn"><i class="icon-search"></i></button>
			</form>
		</div>
	</div>
	
	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Name")?></th>
				<th><?php echo __("Created at")?></th>
				<th><?php echo __("Shared")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($selections->getResults())):?>
				<tr>
					<td colspan="5"><?php echo __("No selection found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($selections as $selection):?>
					<tr>
						<td><?php echo $selection->getTitle();?></td>
						<td><?php echo my_format_date_time($selection->getCreatedAt());?></td>
						<td><?php echo $selection->getIsShared() ? __("Yes") : __("No"); ?></td>
						<td>
							<a class="btn" href="<?php echo path("@selection_edit", array("id" => $selection->getId()))?>">
								<?php echo __("Details"); ?>
							</a>

							<a class="btn" href="<?php echo path("@selection_set_current", array("id" => $selection->getId(), 
									"csrfToken" => $csrfToken));?>">
								<?php echo __("Set as current selection");?>
							</a>

							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@selection_delete", 
									array("id" => $selection->getId(), "csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($selections, "@selection_list", query_params());?>
</div>