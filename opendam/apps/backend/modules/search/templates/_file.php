<?php
	$absolute = null;
	$properties = "";

	if ($file->existsThumbTab()) {
		if (!$absolute) {
			$absolute = $file->getThumbTabPathname();
		}

		$properties .= " data-srcTab = '".path("@file_thumbnail", array("id" => $file->getId(), "format" => "tab"))."' ";
	}

	if ($file->existsThumbMob()) {
		if (!$absolute) {
			$absolute = $file->getThumbMobPathname();
		}

		$properties .= " data-srcMob = '".path("@file_thumbnail", array("id" => $file->getId(), "format" => "mob"))."' ";
	}

	if ($file->existsThumb400()) {
		if (!$absolute) {
			$absolute = $file->getThumb400Pathname();
		}

		$properties .= " data-src400 = '".path("@file_thumbnail", array("id" => $file->getId(), "format" => "400"))."' ";
	}

	if ($file->existsThumb200()) {
		if (!$absolute) {
			$absolute = $file->getThumb200Pathname();
		}

		$properties .= " data-src200 = '".path("@file_thumbnail", array("id" => $file->getId(), "format" => "200"))."' ";
	}

	if ($absolute) {
		$size = getimagesize($absolute);

		if (empty($size)) {
			$size = ImageTools::getSize($absolute);
		}
	}
?>

<?php if($absolute) : ?>
	<div class="item-file file" data-id="file_<?php echo $file->getId(); ?>" data-value="<?php echo $file->getId(); ?>" data-href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>">
		<div class="contain">
			<a class="thumbnail" href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>">
				<img src="" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" <?php echo $properties; ?> />
			</a>
			<div class="info-file">
				<div class="title">
					<a href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>">
						<?php
							$pos = strrpos(strtolower($file), ".".$file->getExtention());

							if($pos === false)
								echo $file;
							else
								echo substr($file, 0, $pos);
						?>
					</a>
				</div>
				<div class="mime">
					<?php echo $file->getExtention(); ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>