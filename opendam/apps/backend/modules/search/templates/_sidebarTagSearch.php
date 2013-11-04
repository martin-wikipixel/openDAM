<?php echo form_tag("search/search", array("id"=>"search_form", "name"=>"search_form", "method"=>"GET"))?>

	<?php include_partial("tagSearch/filter", array('file_ids'=>$file_ids, 'group_ids'=>$group_ids, 'folder_ids'=>$folder_ids, "title"=>__("Filter by tags")));?>
	<?php include_partial("search/information", array("submit_function"=>"doSubmitSearchForm();", 'file_ids'=>$file_ids, "group_options"=>$group_options, "types"=>$types, "usage_rights"=>$usage_rights, "years"=>$years, "sizes"=>$sizes, "dates"=>$dates));?>
	<?php include_partial("search/usage", array('file_ids'=>$file_ids, 'folder_ids'=>$folder_ids, "submit_function"=>"doSubmitSearchForm();"));?>

</form>

<script type="text/javascript">
  jQuery(document).ready( function() {
    jQuery(".ui-slider-handle").bind("click", function(){ return doSubmitSearchForm(); });
    jQuery("#handle_year-to").bind("mouseup", function(){ return doSubmitSearchForm(); });
    jQuery("#handle_date-to").bind("mouseup", function(){ return doSubmitSearchForm(); });
    jQuery("#handle_size-to").bind("mouseup", function(){ return doSubmitSearchForm(); });
    jQuery("#handle_year-from").bind("mouseup", function(){ return doSubmitSearchForm(); });
    jQuery("#handle_date-from").bind("mouseup", function(){ return doSubmitSearchForm(); });
    jQuery("#handle_size-from").bind("mouseup", function(){ return doSubmitSearchForm(); });
    jQuery(".ui-slider-range").bind("click", function(){ return doSubmitSearchForm(); });
    jQuery(".ui-widget-header").bind("click", function(){ return doSubmitSearchForm(); });
    jQuery(".ui-slider-scale").bind("click", function(){ return doSubmitSearchForm(); });
    jQuery(".ui-helper-reset").bind("click", function(){ return doSubmitSearchForm(); });
  });
  
  function doSubmitSearchForm(){
	jQuery("#search_form").submit();
  }
</script>