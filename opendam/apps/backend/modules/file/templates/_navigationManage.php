<?php $file_ids = $file_ids->getRawValue(); ?>

<div class="navigation steps">
  <?php $style = "text-decoration: none; font-weight: bold;";?>
  <a href="<?php echo url_for("file/editSelected")."?folder_id=".$folder->getId()."&file_ids[]=".implode("&file_ids[]=",$file_ids)."&first_call1=1&index=1"; ?>" style="<?php echo $selected == "edit" ? $style : ""?>"><?php echo __("Edit information")?></a> &nbsp; - &nbsp;  
  <a href="<?php echo url_for("file/moveSelected")."?folder_id=".$folder->getId()."&file_ids[]=".implode("&file_ids[]=",$file_ids);?>" style="<?php echo $selected == "move" ? $style : ""?>"> <?php echo __("Move")?></a> &nbsp; - &nbsp;
  <a href="<?php echo url_for("file/copySelected")."?folder_id=".$folder->getId()."&file_ids[]=".implode("&file_ids[]=",$file_ids);?>" style="<?php echo $selected == "copy" ? $style : ""?>"> <?php echo __("Copy")?></a> &nbsp; - &nbsp;
  <a href="<?php echo url_for("file/deleteSelected")."?folder_id=".$folder->getId()."&file_ids[]=".implode("&file_ids[]=",$file_ids);?>" style="<?php echo $selected == "delete" ? $style : ""?>"> <?php echo __("Remove")?></a>
</div>