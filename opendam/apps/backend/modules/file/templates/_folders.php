<?php $folders = FolderPeer::getAllPathFolder($group_id);?>

<label for="folder_id1" style="width: 150px;"><?php echo __("Select the folder")?> :</label>
<?php if(sizeof($folders)):?>
  <?php echo select_tag('folder_id1', options_for_select($folders), array("style"=>"width:279px; float:left")); ?>
<?php else:?>
  <span class="text" style="margin-top:8px; float:left; width:400px;"><?php echo __("No folder. Please select an another group.")?></span>
<?php endif;?>