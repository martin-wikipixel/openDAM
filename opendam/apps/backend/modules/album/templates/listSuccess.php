<div id="album-list-page" class="span12">
	<?php 
		$orderBy = $orderBy->getRawValue();
	?>
	<?php include_partial("album/listTab", array("selected" => "list", "albums" => $albums));?>

	<div class="top-bar-actions clearfix">
		<div class="pull-right">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy")));?>
					
				<label><?php echo __("Sort by")?></label>
				<select class="order-by-select custom-select" name="orderBy[]">
					<option <?php if (in_array("name_asc", $orderBy)) echo "selected";?> value="name_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("name_desc", $orderBy)) echo "selected";?> value="name_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("created_at_asc", $orderBy)) echo "selected";?> value="created_at_asc"><?php echo __("Date of creation ascending")?></option>
					<option <?php if (in_array("created_at_desc", $orderBy)) echo "selected";?> value="created_at_desc"><?php echo __("Date of creation descending")?></option>
					<!--  
					<option <?php if (in_array("lastActivity_asc", $orderBy)) echo "selected";?> value="lastActivity_asc"><?php echo __("Last activity date ascending")?></option>
					<option <?php if (in_array("lastActivity_desc", $orderBy)) echo "selected";?> value="lastActivity_desc"><?php echo __("Last activity date descending")?></option>
					-->
				</select>
				
				<label><?php echo __("Per page")?></label>
				<select class="per-page" name="perPage">
					<option <?php if ($perPage == 8) echo "selected";?> value="8">8</option>
					<option <?php if ($perPage == 16) echo "selected";?> value="16">16</option>
					<option <?php if ($perPage == 24) echo "selected";?> value="24">24</option>
				</select>
			</form>
		</div>
	</div>
	
	<div id="album-thumbnails">
		<?php include_partial("album/listThumbnail", array("albums" => $albums))?>
	</div>
	
	<?php if ($albums->getLastPage() > $albums->getPage()):?>
		<div id="show-more-container">
			<button type="button" class="btn btn-primary" data-loading-text="<i class='icon-spinner icon-spin'></i> <?php echo __("Loading groups..."); ?>">
				<?php echo __("Show more");?>
			</button>
		</div>
	<?php endif;?>

	<!-- modal container -->
	<div id="share-modal" class="dialog"></div>
</div>
