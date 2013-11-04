<?php $files = $files->getRawValue(); ?>
<?php $count = 0; ?>

<?php foreach($files as $file) : ?>
	<?php if($file->getType() == FilePeer::__TYPE_PHOTO && $file->existsThumb100() && $file->exists()) : ?>
		<?php $dimension = getimagesize($file->getThumb100Pathname()); ?>
		<?php $dimension_o = getimagesize($file->getPathname()); ?>
		<?php if($dimension_o[0] >= 220 && $dimension_o[1] >= 220) : ?>
			<?php $count++; ?>
			<?php $merge = "margin-top: ".floor((100 - $dimension[1]) / 2)."px;"; ?>
			<div>
				<a href="javascript: void(0);" class="thumb_files" rel="<?php echo $file->getId(); ?>">
					<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100")); ?>" style="z-index:0; <?php echo $merge; ?>" />
				</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php endforeach; ?>

<?php if(sizeof($files) == 0 || $count == 0) : ?>
	<p style="width: 100%; text-align: center;" class="require_field"><?php echo __("No photo available."); ?></p>
<?php endif; ?>