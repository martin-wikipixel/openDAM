<div class="navigation steps">
  <?php $style = "text-decoration: none; font-weight: bold;";?>
  <a href="<?php echo url_for("file/waiting?group_id=".$group_id."&type=".FileWaitingPeer::__STATE_WAITING_VALIDATE); ?>" style="<?php echo $selected == FileWaitingPeer::__STATE_WAITING_VALIDATE ? $style : ""?>"><?php echo __("Pending validation")?></a> &nbsp; - &nbsp;  
  <a href="<?php echo url_for("file/waiting?group_id=".$group_id."&type=".FileWaitingPeer::__STATE_WAITING_DELETE); ?>" style="<?php echo $selected == FileWaitingPeer::__STATE_WAITING_DELETE ? $style : ""?>"><?php echo __("Pending deletion")?></a>
</div>