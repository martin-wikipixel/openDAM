<?php echo $form['_csrf_token']->render(); ?>
<?php echo $form['id']->render(); ?>
<?php echo $form['inside']->render(); ?>
<?php echo $form['redirect']->render(); ?>

<label for="name"><?php echo __("Folder name")?> :</label>
<?php echo $form['name']->render(); ?>
<span class="description" style="width: 15px;"><span class='require_field'>*</span></span>

<br clear="all">

<label for="description"><?php echo __("Description")?> :</label>
<?php echo $form['description']->render(); ?>
<span class="description" style="width: 15px;"><span class='require_field'>*</span></span>

<br clear="all">
<br clear="all">
  
<div class="right">
	<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("Close")?></span></a>

	<?php if($folder->isNew() || $sf_params->get("navigation") == "create"):?>

			<a href="#" onClick="jQuery('#edit_form').submit();" class="button btnBS"><span><?php echo __("Confirm folder creation")?></span></a>
			<div id="jsbutton"><a href="#" onClick="jQuery('#data_redirect').val(1); jQuery('#edit_form').submit();" class="button btnBS"><span><?php echo __("Add pictures to the folder")?></span></a></div>

	<?php else:?>

			<a href="#" onClick="jQuery('#edit_form').submit();" class="button btnBS"><span><?php echo __("Confirm")?></span></a>

	<?php endif;?>
</div>

<br clear="all">