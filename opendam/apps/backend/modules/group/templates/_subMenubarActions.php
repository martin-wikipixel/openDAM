<div class="subMenuBarTitle">
	<span><?php echo __("Actions"); ?></span>
</div>
<div class="subMenuBar">
	<div>
		<a href='<?php echo url_for("group/merge?group_from=".$group->getId()); ?>' <?php echo $selected == "merge" ? "class='active'" : ""; ?>><?php echo __("Merge"); ?></a> &nbsp; - &nbsp; 
		<a href='<?php echo url_for("group/remove?id=".$group->getId()."&iframe=1"); ?>' <?php echo $selected == "delete" ? "class='active'" : ""; ?>><?php echo __("Remove"); ?></a>
	</div>
</div>
<br clear="all" />