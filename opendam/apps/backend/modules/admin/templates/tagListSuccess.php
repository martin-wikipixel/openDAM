<div id="admin-tag-list-page" class="span12">
	<?php
		$orderBy = $orderBy->getRawValue();
		
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_tag_list"), "text" => __("Keyword management")),
		));
	?>
	
	<div class="commands-top">
		<a class="btn btn-primary" role="button" href="<?php echo path("@admin_tag_new"); ?>">
			<i class="icon-plus-sign"></i> <?php echo __("Add tag"); ?>
		</a>
		
		<button class="btn btn-danger hide" data-action="delete-all">
			<i class="icon-trash"></i> <?php echo __("Delete selected tags");?>
		</button>
	</div>

	<div class="search-block clearfix">
		<div class="pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy", "page")));?>
				
				<label><?php echo __('Sort modules by')?></label>
				<select name="orderBy[]">
					<option <?php if (in_array("name_asc", $orderBy)) echo "selected";?> value="lastname_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("name_desc", $orderBy)) echo "selected";?> value="lastname_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("created_at_asc", $orderBy)) echo "selected";?> value="created_at_asc"><?php echo __("Date ascending")?></option>
					<option <?php if (in_array("created_atl_desc", $orderBy)) echo "selected";?> value="created_at_desc"><?php echo __("Date descending")?></option>
				</select>
				
				<button class="btn"><i class="icon-search"></i></button>
			</form>
			
			<ul class="filter">
				<li>
					<a class="<?php if ($currentLetter === "") echo "selected"?>" href="<?php echo path("@admin_tag_list", 
							merge_request_params(null, array("letter", "page")));?>"><?php echo __('ALL')?>
					</a> 
				</li>
				
				<?php foreach ($letters as $letter):?>
					<li>
						<a class="<?php if ($letter == $currentLetter) echo "selected"?>" href="<?php echo path("@admin_tag_list", 
								merge_request_params(array("letter" => $letter), array("page")));?>"><?php echo $letter?>
						</a>
					</li>
				<?php endforeach;?>
			</ul>
	
		</div>

		<form class="form-search pull-right">
			<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>

			<div class="input-append">
				<input name="keyword" type="text" class="input-medium search-query" placeholder="<?php echo __("Search")?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>
	</div>
	
	<table id="tag-list" class="table">
		<thead>
			<tr>
				<th class="span1"></th>
				<th class="span7"><?php echo __("Name")?></th>
				<th class="span4 text-centered"><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($tags->getResults())):?>
				<tr>
					<td colspan="3"><?php echo __("No tag found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($tags as $tag):?>
					<tr>
						<td class="text-centered">
							<input type="checkbox" name="tags[]" value="<?php echo $tag->getId(); ?>" />
						</td>
						<td><?php echo $tag->getName();?></td>
						<td class="text-centered">
							<button data-tag-id="<?php echo $tag->getId()?>" 
								data-csrf-token="<?php echo $csrfToken;?>" 
								data-action="replace"
								data-name="<?php echo $tag->getName();?>"
								class="btn btn-primary hide" >
								<?php echo __("Replace");?>
							</button>

							<a class="btn" href="<?php echo path("admin_tag_edit", array("id" => $tag->getId()));?>">
								<?php echo __("Manage");?>
							</a>

							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_tag_delete", array("id" => $tag->getId(), 
									"csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>

	<?php echo pagination($tags, "@admin_tag_list");?>
</div>
