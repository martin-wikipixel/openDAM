
<input type="hidden" name="add_folder_group_id" id="add_folder_group_id" value="<?php echo $group_selected?>">

<?php if($folder_selected) : ?>
	<input type="hidden" name="add_folder_folder_id" id="add_folder_folder_id" value="<?php echo $folder_selected?>">
<?php else: ?>
	<input type="hidden" name="add_folder_folder_id" id="add_folder_folder_id" value="0" />
<?php endif; ?>

<label for="add_folder_folder_name"><?php echo __("Folder name"); ?></label>
<input type="text" name="add_folder_folder_name" id="add_folder_folder_name" style="width: 100%;" />

<br clear="all" />


<div id="error">
	<span class="require_field"></span>
</div>

<br clear="all" />
<br clear="all" />

<div class="navigation_inner">
	<a class="button btnBS" id="create_folder" href="javascript: void(0);"><span><?php echo __("Create a folder"); ?></span></a>
</div>