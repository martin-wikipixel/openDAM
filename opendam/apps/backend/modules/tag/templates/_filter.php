<?php $folder_ids = $sf_data->getRaw("folder_ids"); ?>
<?php $file_ids = $sf_data->getRaw("file_ids"); ?>

<div class="filterBox">
	<div class="title" style="cursor:pointer;" onclick="toggleContainer('filterbytags_container', 'filterbytags_container_img')">
		<?php echo image_tag("down-arr.gif", array("align"=>"absmiddle", "id"=>"filterbytags_container_img"))?>
		<h4><?php echo $title?> (<span id='count_tag'></span>)</h4>
	</div>

	<div id="filterbytags_container">
		<div class="optionsButton" id="selectedTags">
			<?php include_partial("tag/selectedTags", array());?>
		</div>
		<br clear="all">
		<div class="filterKeywords">
			<?php echo input_tag('tag_title', __("Filter tags ..."), array("style"=>"width: 190px; font-size:11px;", "id"=>"tag_title", "onfocus"=>"onTagFocus(this)", "onblur"=>"onTagBlur(this)", "onkeyup"=>"onKeyUpTag(this);", "class" => "nc"));?>
		</div>
		<div class="filterTags" style="margin-top:-20px;" id="filteredTags">
			<?php include_partial("tag/filteredTags", array("folder_ids"=>$folder_ids, "file_ids"=>$file_ids));?>
		</div>
	</div>
</div>
<br clear="all">
<script type="text/javascript">
function appendToSelectedTags(id, name)
{
	if(id && name)
	{
		jQuery('#selectedTags').append('<a href="javascript:doSubmitFoldersFilesForm();" id="selected_tag_id_'+id+'" class="r"><input type="hidden" value="'+id+'" name="selected_tag_ids[]" /> <span>'+urldecode(name)+'</span><em onclick="removeFromSelectedTags('+id+');"></em></a>');
		jQuery('#<?php echo $form_name?>').append('<input type="hidden" value="'+id+'" name="selected_tag_ids[]" id="selected_tag_id_inshow_'+id+'" />');

		if(jQuery('#cloud_tag_id_'+id))
			jQuery('#cloud_tag_id_'+id).hide();

		var count = jQuery('#count_tag').html() - 1;
		jQuery('#count_tag').html(count);
	}

	window.setTimeout(doSubmitFoldersFilesForm, 200);
}

function removeFromSelectedTags(id)
{
	if(jQuery('#selected_tag_id_'+id))
		jQuery('#selected_tag_id_'+id).remove();

	if(jQuery('#selected_tag_id_inshow_'+id))
		jQuery('#selected_tag_id_inshow_'+id).remove();

	if(jQuery('#bread_tag_id_'+id))
		jQuery('#bread_tag_id_'+id).remove();

	if(jQuery('#cloud_tag_id_'+id))
		jQuery('#cloud_tag_id_'+id).show();

	var count = parseInt(parseInt(jQuery('#count_tag').html()) + 1);
	jQuery('#count_tag').html(count);

	doSubmitFoldersFilesForm();
}


function onKeyUpTag(obj)
{
	var uri = "";

	jQuery("[name='selected_tag_ids[]']").each(function (index, object) {
		uri += "&selected_tag_ids[]="+jQuery(object).val();
	});

	jQuery.post(
		"<?php echo url_for("tag/filterTags"); ?>?tag_title=" + obj.value + uri + "<?php echo sizeof($folder_ids) ? "&folder_ids[]=".join("&folder_ids[]=", $folder_ids) : ""?><?php echo sizeof($file_ids) ? "&file_ids[]=".join("&file_ids[]=", $file_ids) : ""?>",
		function(data) {
			jQuery("#filteredTags").html(data);
		}
	);
}

function onTagFocus(obj)
{
	if((jQuery(obj).val() == "<?php echo __("filter tags ...")?>") || (jQuery(obj).val() == "<?php echo __("Filter tags ...")?>"))
	{
		jQuery(obj).val("");
		jQuery(obj).removeClass("nc");
	}
}

function onTagBlur(obj)
{
	if(jQuery(obj).val() == "")
	{
		jQuery(obj).val("<?php echo __("Filter tags ...")?>");
		jQuery(obj).addClass("nc");
	}
}

jQuery(document).ready(function() {
	var max = jQuery('#filteredTags').find('a').length;
	var min = jQuery('#selectedTags').find('a').length;
	jQuery('#count_tag').html(max - min);
 });
</script>