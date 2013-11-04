<?php
	if (!$group->getThumbnail()) {
		if ($group->getNumberOfFiles() > 0) {
			$file = FilePeer::getFirstFileOfGroupe($group->getId());

			if ($file) {
				if ($file->existsThumb400()) {
					$absolute = $file->getThumb400Pathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "400"));
				}
				else {
					$absolute = $file->getThumb200Pathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "200"));
				}
			}
			else {
				$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
				$relative = image_path("no-access-file-200x200.png");
			}
		}
		else {
			$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
			$relative = image_path("no-access-file-200x200.png");
		}
	}
	else {
		$absolute = $group->getPathname();
		$relative = path("@group_thumbnail", array("id" => $group->getId()));
	}

	$size = getimagesize($absolute);
?>

<div class="group-folder item-search group item-group item open" data-id="group_<?php echo $group->getId(); ?>" data-value="<?php echo $group->getId(); ?>">
	<div class="contain">
		<a href="<?php echo url_for("@group_show?id=".$group->getId()); ?>">
			<div class="thumbnail">
				<img src="<?php echo $relative; ?>" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" />
			</div>
			<div class="info-group-folder">
				<div class="title">
					
						<i class="icon-book"></i>

						<?php echo $group->getName(); ?>
					
				</div>
				<div class="clearfix"></div>
				<span class="contain-inside">
					<?php
						$files = $group->getNumberOfFiles();
						echo $files.($files > 1 ? " ".__("files") : " ".__("file"));

						if($files > 0)
							echo " | ".MyTools::getSize($group->getSize());
					?>
				</span>
			</div>
		</a>
	</div>
</div>