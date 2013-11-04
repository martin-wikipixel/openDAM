<?php 
if($sf_params->get("navigation") == "create"){
  include_partial("folder/navigationCreate", array("selected"=>"edit", "folder"=>$folder));
}elseif($sf_params->get("navigation") == "upload"){
  include_partial("upload/navigation", array("selected"=>"edit", "folder_id"=>$folder->getId(), "group_id"=>$folder->getGroupeId()));
}?>

<?php echo form_tag('file/editAll', array('name'=>'editAll_form', 'id'=>'editAll_form', "class"=>"form", 'multipart'=>true))?>

<div id="searchResults-popup">
  <div class="inner">
  
    <?php include_partial("file/edit", array("file"=>$file, "folder"=>$folder));?>
<br clear="all">
<div class="right">
	<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
	<a href="<?php echo url_for("upload/option?folder_id=".$folder->getId()."&navigation=".$sf_params->get("navigation"));?>" class="button btnBSG"><span> < <?php echo __("PREVIOUS STEP")?></span></a>
	<!--have to redirect to folder/show after submit-->
	<a href="javascript:jQuery('#editAll_form').submit();" class="button btnBS"><span><?php echo __("CONFIRM and VIEW FILES")?></span></a>
	<a href="#" onclick="window.parent.location.href='<?php echo url_for("folder/show?id=".$folder->getId()); ?>'" class="button btnBSG"><span><?php echo __("SKIP STEP")?> ></span></a>
</div>
  </div><!--inner-->
</div><!--searchResults-->


    
</form>

<script type="text/javascript">
//<![CDATA[

function onFocus(obj){
  if(obj.value.toLowerCase() == "<?php echo __("address, city, region")?>"){
    obj.value = "";
  }
}

function onBlur(obj){
  if(obj.value == ""){
    obj.value = "<?php echo __("Address, City, Region")?>";
  }
}

function searchLocation(){
  initialize(jQuery("#address").val(), '0', '0');
}

//]]>
</script>