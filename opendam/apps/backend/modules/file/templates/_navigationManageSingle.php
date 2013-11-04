<div class="navigation steps">
  <?php $style = "text-decoration: none; font-weight: bold;";?>
  <a href="<?php echo url_for("file/move?folder_id=".$folder->getId()."&id=".$file->getId());?>" style="<?php echo $selected == "move" ? $style : ""?>" rel="facebox"> <?php echo __("Move")?></a> &nbsp; - &nbsp;
  <a href="<?php echo url_for("file/copy?folder_id=".$folder->getId()."&id=".$file->getId());?>" style="<?php echo $selected == "copy" ? $style : ""?>" rel="facebox"> <?php echo __("Copy")?></a> &nbsp; - &nbsp;
  <a href="<?php echo url_for("file/deleteSingle?folder_id=".$folder->getId()."&id=".$file->getId());?>" style="<?php echo $selected == "delete" ? $style : ""?>" rel="facebox"> <?php echo __("Remove")?></a> &nbsp; - &nbsp;
  <a href="<?php echo url_for("file/replace?id=".$file->getId());?>" style="<?php echo $selected == "replace" ? $style : ""?>" rel="facebox"> <?php echo __("Replace")?></a>
</div>