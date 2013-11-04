<?php echo input_tag("keyword", $keyword  ? $keyword : __("Search"), array("style"=>"width:200px; color:gray;", "onFocus"=>"onFocus(this)", "onBlur"=>"onBlur(this)"))?>
<?php echo submit_tag("&nbsp;", array( "class"=>"search_btn"))?>
<?php echo input_hidden_tag("is_search", 1)?>


<script type="text/javascript">
function onFocus(obj){
  if(obj.value.toLowerCase() == "<?php echo __("search")?>"){
    obj.value = "";
  }
}

function onBlur(obj){
  if(obj.value == ""){
    obj.value = "<?php echo __("Search")?>";
  }
}
</script>