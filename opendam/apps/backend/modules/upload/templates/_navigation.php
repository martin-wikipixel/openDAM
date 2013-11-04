<div class="navigation steps">
  <?php $style = "text-decoration: none; font-weight: bold;";?>
  <a href="#<?php //echo "upload/uploadify?folder_id=".$folder_id."&code=".$sf_user->getId()?>" style="<?php echo $selected == "uploadify" ? $style : ""?>"><strong <?php echo $selected == "uploadify" ? "class='current'" : ""?>><em>1</em></strong> <?php echo __("Upload files")?></a> &nbsp;
  <?php //if(sizeof($sf_user->getAttribute("files_array")) > 1):?> 
    <a href="#<?php //echo "upload/option?folder_id=".$folder_id?>" style="<?php echo $selected == "edit" ? $style : ""?>"><strong <?php echo $selected == "edit" ? "class='current'" : ""?>><em>2</em></strong> <?php echo __("Edit files common informations")?></a> &nbsp;
  <?php //endif;?>  
</div>
