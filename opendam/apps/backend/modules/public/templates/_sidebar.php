<?php echo include_partial("public/info", array());?>

<?php echo form_tag("search/search", array("id"=>"search_form", "name"=>"search_form", "method"=>"GET"));?>

  <?php echo input_hidden_tag("first_call", 1); ?>
  
  <?php include_partial("tagSearch/filter", array('file_ids'=>array(), 'folder_ids'=>array(), 'group_ids'=>array(), "title"=>__("Search by tags")));?>  

</form>

<script type="text/javascript">
  jQuery(document).ready( function() {
  	jQuery(".ui-slider-handle").click(function(){return doSubmitSearchForm()});
  });
  
  function doSubmitSearchForm(){
    jQuery('#search_form').submit();
  }
</script>