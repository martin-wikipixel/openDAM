<div class="subMenuBarTitle">
	<span><?php echo __("Actions"); ?></span>
</div>
<div class="subMenuBar">
	<div>
		<a href='<?php echo url_for("folder/move?id=".$folder->getId());?>' rel='facebox' <?php echo $selected == "move" ? "class='active'" : ""; ?>><?php echo __("Move")?></a> &nbsp; - &nbsp;
		<a href='<?php echo url_for("folder/delete?id=".$folder->getId());?>' rel='facebox' <?php echo $selected == "delete" ? "class='active'" : ""; ?>><?php echo __("Remove")?></a>
	</div>
</div>
<br clear="all" />