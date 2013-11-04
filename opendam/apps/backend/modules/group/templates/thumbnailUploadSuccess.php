<div style="margin-top:-18px;">
<?php if (!empty($form) && $form->hasErrors()): ?>
	<?php foreach($form->getErrors() as $name => $error): ?>
		<span class="description" style="width: auto;"><span class='require_field'><?php echo __($error); ?></span></span><br />
	<?php endforeach; ?>
<?php endif; ?>
</div>

<?php if(isset($thumbnail) && file_exists(sfConfig::get("app_path_upload_dir")."/".$sf_user->getDisk()->getPath()."/cust-".$sf_user->getCustomerId()."/groups/".$thumbnail)): ?>
	<img src="/<?php echo sfConfig::get('app_path_upload_dir_name'); ?>/<?php echo $sf_user->getDisk()->getPath(); ?>/cust-<?php echo $sf_user->getCustomerId(); ?>/groups/<?php echo $thumbnail?>" id="uploaded_thumbnail" style="width: <?php echo $new_width; ?>px; height: <?php echo $new_height; ?>px;" />

	<?php echo $form['uploaded_thumbnail_name']->render(); ?>
	<?php echo $form['is_upload']->render(); ?>
	<?php echo $form['width']->render(); ?>
	<?php echo $form['height']->render(); ?>


	<br clear="all">
	<br clear="all">
<?php endif ?>