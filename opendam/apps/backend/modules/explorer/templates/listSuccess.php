<?php foreach ($folders->getRawValue() as $folder) : ?>
<li>
	<a href="javascript: void(0);" class="folder-tree"
			data-href="<?php echo path("@folder_show", array("id" => $folder->getId())); ?>"
			data-id="<?php echo $folder->getId(); ?>" data-album-id="<?php echo $folder->getGroupeId(); ?>">
		<i class="icon-folder-close"></i> <?php echo $folder->getName(); ?>
	</a>
</li>
<?php endforeach; ?>