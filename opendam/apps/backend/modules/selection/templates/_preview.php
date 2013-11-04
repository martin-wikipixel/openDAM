<?php $contents = $contents->getRawValue(); ?>

<?php foreach($contents as $content) :
	$file = $content->getFile(); ?>
	<div class='item-show-cart' style="height: 100px; width: 420px;">
		<div class='left'>
			<img src='<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100")); ?>' />
		</div>
		<div class='description-item-cart'>
			<strong><?php echo __("Group"); ?> :</strong> <?php echo $file->getGroupe(); ?><br />
			<strong><?php echo __("Folder"); ?> :</strong> <?php echo $file->getFolder(); ?><br />
			<strong><?php echo __("Filename"); ?> :</strong> <?php echo myTools::utf8_substr($file, 0, 25); ?><br />
			<strong><?php echo __("Size"); ?> :</strong> <?php echo MyTools::getSize($file->getSize()); ?>
		</div>
	</div>
<?php endforeach; ?>
