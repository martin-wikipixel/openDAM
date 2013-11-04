<div id="support-body">
	<div class="support-list">
		<?php include_component("right", "supportList", array("selectedSupportsId" => $selectedSupportsId))?>
	</div>
	
	<div class="support-new">
		<input type="text" name="support" placeholder="<?php echo __("New support"); ?>" />
		<button data-action="add" type="button" class="btn btn-primary"><?php echo __("Add")?></button>
	</div>
</div>
