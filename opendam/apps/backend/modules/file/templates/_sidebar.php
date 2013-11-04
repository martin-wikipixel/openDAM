<div style="padding: 10px; padding-left: 18px; padding-right: 18px;">
	<div class="title-file" id="title_container" style="font-weight: normal; font-size: 16px;">
		<?php if($role) : ?>
			<div class='eotf-title' id='<?php echo $file->getId(); ?>' rel='name' style="line-height: 18px;min-height: 22px;"><?php echo myTools::longword_break_old($file, 22); ?></div>
		<?php else: ?>
			<div><?php echo myTools::longword_break_old($file, 22); ?></div>
		<?php endif; ?>
	</div>
</div>

<br clear="all">

<div style="padding: 10px; padding-left: 18px; padding-right: 18px;">
	<div id="description_container">
		<?php if($role) : ?>
			<div class='eotfarea' style="min-height: 35px;" id='<?php echo $file->getId(); ?>' rel='description'><?php echo $file->getDescription() ? nl2br($file->getDescription()) : "<span style=\"cursor: pointer;\" class=\"text\">".__("Add a description.")."</span>"; ?></div>
		<?php else: ?>
			<div><?php echo nl2br($file->getDescription()); ?></div>
		<?php endif; ?>
	</div>
</div>

<?php include_partial("file/detail", array("file" => $file, "role" => $role));?>

<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
	<?php if($role) : ?>
		<?php include_partial('file/history', array("file"=>$file)); ?>
	<?php endif; ?>
<?php endif; ?>