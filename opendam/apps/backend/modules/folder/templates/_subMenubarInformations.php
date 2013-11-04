<div class="subMenuBarTitle">
	<span><?php echo __("Informations"); ?></span>
</div>
<div class="subMenuBar">
	<div>
		
			<a href='<?php echo url_for("folder/edit?group_id=".$folder->getGroupeId()."&id=".$folder->getId());?>' rel='facebox' <?php echo $selected == "edit" ? "class='active'" : ""; ?>><?php echo __("Edit information"); ?></a> &nbsp; - &nbsp;

		<a href='<?php echo url_for("folder/thumbnail?id=".$folder->getId());?>' rel='facebox' <?php echo $selected == "thumb" ? "class='active'" : ""; ?>><?php echo __("Thumbnail")?></a> &nbsp; - &nbsp;
		<a href='<?php echo url_for("folder/default?id=".$folder->getId());?>' rel='facebox' <?php echo $selected == "default" ? "class='active'" : ""; ?>><?php echo __("Default values common to all files")?></a> &nbsp; - &nbsp;
		<a href='<?php echo url_for("folder/recursive?id=".$folder->getId());?>' rel='facebox' <?php echo $selected == "recursive" ? "class='active'" : ""; ?>><?php echo __("Recursive modification")?></a>
	</div>
</div>
<br clear="all" />