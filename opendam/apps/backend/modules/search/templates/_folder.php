<?php
	if (!$folder->getThumbnail()) {
		$relative = image_path("no-access-file-200x200.png");
		$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
	}
	else {
		$absolute = $folder->getRealPathname();
		$relative = path("@folder_thumbnail", array("id" => $folder->getId()));
	}

	$size = getimagesize($absolute);
?>

<div class="group-folder item-search folder item-folder item open" data-id="folder_<?php echo $folder->getId(); ?>" data-value="<?php echo $folder->getId(); ?>">
	<div class="contain">
		<a href="<?php echo url_for("folder/show?id=".$folder->getId()); ?>">
			<div class="thumbnail">
				<img src="<?php echo $relative; ?>" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" />
			</div>
			<div class="info-group-folder">
				<div class="title">
					<i class="icon-folder-close"></i>
					<?php echo $folder->getName(); ?>
				</div>
				<div class="clearfix"></div>
				<span class="contain-inside">
					<?php
						$files = $folder->getNumberOfFiles();

						echo $files.($files > 1 ? " ".__("files") : " ".__("file"));

						if($files > 0)
							echo " | ".MyTools::getSize($folder->getSize());
					?>
				</span>
			</div>
		</a>
	</div>
</div>