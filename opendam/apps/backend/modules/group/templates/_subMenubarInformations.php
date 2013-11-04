<div class="subMenuBarTitle">
	<span><?php echo __("Informations"); ?></span>
</div>
<div class="subMenuBar">
	<div>

			<a href='<?php echo url_for("group/step1?id=".$group->getId()); ?>' rel='facebox' <?php echo $selected == "step1" ? "class='active'" : ""; ?>><?php echo __("Edit information"); ?></a>


		
			&nbsp; - &nbsp; 


		<a href='<?php echo url_for("group/thumbnail?id=".$group->getId()); ?>' rel='facebox' <?php echo $selected == "thumb" ? "class='active'" : ""; ?>><?php echo __("Thumbnail"); ?></a> &nbsp; - &nbsp; 
		<a href='<?php echo url_for("group/tags?id=".$group->getId()); ?>' rel='facebox' <?php echo $selected == "tags" ? "class='active'" : ""; ?>><?php echo __("Default tags"); ?></a> &nbsp; - &nbsp; 	
		<a href='<?php echo url_for("group/fields?id=".$group->getId()); ?>' rel='facebox' <?php echo $selected == "fields" ? "class='active'" : ""; ?>><?php echo __("Specific field"); ?></a>
	</div>
</div>
<br clear="all" />