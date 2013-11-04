
<?php $group_options = $sf_data->getRaw("group_options"); ?>
<?php $types = $sf_data->getRaw("types"); ?>
<?php $years = $sf_data->getRaw("years"); ?>
<?php $dates = $sf_data->getRaw("dates"); ?>
<?php $usage_rights = $sf_data->getRaw("usage_rights"); ?>
<?php $sizes = $sf_data->getRaw("sizes"); ?>
<?php $file_ids = $sf_data->getRaw("file_ids"); ?>

<div class="filterBox">
	<div class="title" style="cursor:pointer;" onclick="toggleContainer('filterbyinformation_container', 'filterbyinformation_container_img')">
		<?php echo image_tag("down-arr.gif", array("align"=>"absmiddle", "id"=>"filterbyinformation_container_img"))?>
		<h4><?php echo __("Filter by information")?></h4>
	</div>

	<br clear="all">

	<div id="filterbyinformation_container">

		<div id="filterByInformation">
			<div class="filterRow">
				<?php if($sf_context->getModuleName() == "search"):?>
					<label><?php echo checkbox_tag("view_groups", 1, ($sf_params->get("first_call")) ? true : $sf_params->get("view_groups"), array("onclick"=>$submit_function)); ?> <?php echo __("Show groups")?></label>
					<label><?php echo checkbox_tag("view_folders", 1, ($sf_params->get("first_call")) ? true : (($sf_context->getModuleName() == "group") ? true : $sf_params->get("view_folders")), array("onclick"=>$submit_function)); ?> <?php echo __("Show folders")?></label>
					<label><?php echo checkbox_tag("view_files", 1, ($sf_params->get("first_call")) ? true : (($sf_context->getModuleName() == "folder") ? true : $sf_params->get("view_files")), array("onclick"=>$submit_function)); ?> <?php echo __("Show files")?></label>
				<?php endif;?>
				<label style="float: none;"><?php echo checkbox_tag("added_by_me", 1, $sf_params->get("added_by_me_input") == "true" ? true : false, array("onclick"=>"jQuery('#added_by_me_input').val(jQuery(this).attr('checked'));".$submit_function)); ?> <?php echo __("Added by me")?></label>
			</div>

			<?php if(sizeof($group_options) && $sf_context->getModuleName() == "search"):?>
				<div class="filterRow">
					<label for="group_id" style="float: none;"><?php echo __("Group membership")?> :</label>
					<?php echo select_tag("group_id", options_for_select($group_options, $sf_params->get("group_id") ? $sf_params->get("group_id") : " ", array("include_custom"=>__("ALL"))), array("onchange"=>$submit_function))?>
				</div>
			<?php endif;?>

			<?php if(sizeof($types)):?>
				<div class="filterRow">
					<label for="file_type" style="float: none;"><?php echo __("File type")?> :</label>
					<?php echo select_tag("file_type", options_for_select($types, $sf_params->get("file_type_input"), array("include_custom"=>__("ALL"))), array("onchange"=>"jQuery('#file_type_input').val(jQuery(this).val());".$submit_function))?>
				</div>
			<?php endif;?>

			<?php if(sizeof($file_ids)):?>
				<?php $orientations = FilePeer::getOrientationOfFiles($file_ids); ?>
				<div class="filterRow">
					<label for="file_orientation" style="float: none;"><?php echo __("File orientation")?> :</label>
					<select name="file_orientation" id="file_orientation" onchange="jQuery('#file_orientation_input').val(jQuery(this).val());<?php echo $submit_function; ?>">
						<option value=""><?php echo __("ALL"); ?></option>
						<?php foreach($orientations as $key => $value) : ?>
							<option value="<?php echo $key; ?>" <?php echo $sf_params->get("file_orientation_input") == $key || $sf_params->get("file_orientation") == $key ? "selected" : ""; ?>><?php echo $value; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<?php $ratings = FilePeer::getRatingOfFiles($file_ids); ?>

				<?php if(!empty($ratings)) : ?>
					<div class="filterRow">
						<label for="file_rating" style="float: none; width: auto;"><?php echo __("Display only files with")?> :</label>
						<select name="file_rating" id="file_rating" onchange="jQuery('#file_rating_input').val(jQuery(this).val());<?php echo $submit_function; ?>">
							<option value=""><?php echo __("ALL"); ?></option>
							<?php foreach($ratings as $key => $value) : ?>
								<option value="<?php echo $key; ?>" <?php echo $sf_params->get("file_rating_input") == $key || $sf_params->get("file_rating") == $key ? "selected" : ""; ?>><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif;?>
			<?php endif;?>

			<?php if(sizeof($years)):
				$year_from = $sf_params->get("year-from_input") ? $sf_params->get("year-from_input") : min($years);
				$year_to = $sf_params->get("year-to_input") ? $sf_params->get("year-to_input") : max($years); ?>
    
				<div class="filterRow sliderContainer">
					<label style="margin:10px 0; width: auto;"><?php echo __("Date of publication")?> : 
						<span id="yearsLabel"><?php echo $year_from?> - <?php echo $year_to?></span>
					</label>

					<br clear="all">

					<div id="years"></div>

					<input type="hidden" id="year-from" name="year-from" value="<?php echo $year_from;?>"/>
					<input type="hidden" id="year-to" name="year-to" value="<?php echo $year_to;?>"/>

					<div class="text" style="margin-top:5px;">
						<span class="left"><?php echo min($years);?></span>
						<span class="right"><?php echo max($years);?></span>
					</div>

					<br clear="all">
				</div>
			<?php endif;?>

			<?php if(sizeof($dates)):
				$date_from = $sf_params->get("date-from_input") ? $sf_params->get("date-from_input") : min($dates);
				$date_to = $sf_params->get("date-to_input") ? $sf_params->get("date-to_input") : max($dates); ?>
    
				<div class="filterRow sliderContainer">
					<label style="margin:10px 0; width: auto;"><?php echo __("Shooting date")?> : 
						<span id="datesLabel"><?php echo $date_from?> - <?php echo $date_to?></span>
					</label>

					<br clear="all">

					<div id="dates"></div>

					<input type="hidden" id="date-from" name="date-from" value="<?php echo $date_from;?>"/>
					<input type="hidden" id="date-to" name="date-to" value="<?php echo $date_to;?>"/>

					<div class="text" style="margin-top:5px;">
						<span class="left"><?php echo min($dates);?></span>
						<span class="right"><?php echo max($dates);?></span>
					</div>

					<br clear="all">
				</div>
			<?php endif;?>

			<?php if(sizeof($sizes)):
				$size_from = $sf_params->get("size-from_input") ? $sf_params->get("size-from_input") : min($sizes);
				$size_to = $sf_params->get("size-to_input") ? $sf_params->get("size-to_input") : max($sizes); ?>

				<div class="filterRow sliderContainer">
					<label style="margin:10px 0;">
						<?php echo __("Size range")?> : 
						<span id="sizesLabel"><?php echo  MyTools::getSize($size_from)." - ". MyTools::getSize($size_to)?></span>
					</label>

					<br clear="all">

					<div id="sizes"></div>

					<input type="hidden" id="size-from" name="size-from" value="<?php echo $size_from;?>" />
					<input type="hidden" id="size-to" name="size-to" value="<?php echo $size_to;?>" />

					<div class="text" style="margin-top:5px;">
						<span class="left"><?php echo  MyTools::getSize(min($sizes));?></span>
						<span class="right"><?php echo  MyTools::getSize(max($sizes));?></span>
					</div>

					<br clear="all">
				</div>
			<?php endif;?>
		</div>
	</div>
</div>

<script type="text/javascript">
function getSize(size)
{
	return (size > 1048576) ? ((size/1048576).toFixed(1) + "<?php echo __("Mb")?>") : ((size/1024).toFixed(1) + "<?php echo __("Kb")?>");
}

jQuery(document).ready(function() {
	<?php if(!empty($years)):?>
		jQuery('#years').slider({
			range: true,
			min: <?php echo min($years)?>,
			max: <?php echo max($years)?>,
			values: [<?php echo $year_from?>, <?php echo $year_to?>],
			change: function(event, ui) {
				jQuery("#year-from").val(ui.values[0]); 
				jQuery("#year-to").val(ui.values[1]);
				jQuery("#yearsLabel").html(ui.values[0] + " - " + ui.values[1]);
				<?php echo $submit_function;?>
			}
		});
	<?php endif;?>

	<?php if(!empty($dates)):?>
		jQuery('#dates').slider({
			range: true,
			min: <?php echo min($dates)?>,
			max: <?php echo max($dates)?>,
			values: [<?php echo $date_from?>, <?php echo $date_to?>],
			change: function(event, ui) {
				jQuery("#date-from").val(ui.values[0]); 
				jQuery("#date-to").val(ui.values[1]);
				jQuery("#datesLabel").html(ui.values[0] + " - " + ui.values[1]);
				<?php echo $submit_function;?>
			}
		});
	<?php endif;?>

	<?php if(!empty($sizes)):?>
		jQuery('#sizes').slider({
			range: true,
			min: <?php echo min($sizes); ?>,
			max: <?php echo max($sizes); ?>,
			values: [<?php echo $size_from; ?>, <?php echo $size_to; ?>],
			change: function(event, ui) {
				jQuery("#size-from").val(ui.values[0]); 
				jQuery("#size-to").val(ui.values[1]);
				jQuery("#sizesLabel").html(getSize(ui.values[0]) + " - " +  getSize(ui.values[1]));
				<?php echo $submit_function;?>
			}
		});
	<?php endif;?>
});
</script>