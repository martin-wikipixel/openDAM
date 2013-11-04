<?php
	$absolute = null;
	$properties = "";
	$drawOverlay = false;
	$drawFavorites = false;
	$showShare = false;
	$roleGroup = $sf_user->getRole($file->getGroupeId());

	if ($roleGroup) {
		$drawOverlay = true;

		if ($sf_user->haveAccessModule(ModulePeer::__MOD_FAVORITE)) {
			$drawFavorites = true;
		}
	}

	if ($sf_user->haveAccessModule(ModulePeer::__MOD_PERMALINK)) {
		if ($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__PERMALINK_FILE) && $sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__SHARE, RolePeer::__READER)) {
			if ($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH) {
				$showShare = true;
			}
		}
	}

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

	if($absolute) {
		$size = getimagesize($absolute);

		if(empty($size)) {
			$size = ImageTools::getSize($absolute);
		}
	}
	else {
		$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
		$size = getimagesize($absolute);
	}
?>

<?php if($absolute) : ?>
	<div class="item-file file" data-id="file_<?php echo $file->getId(); ?>" data-value="<?php echo $file->getId(); ?>" data-href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>">
		<div class="contain">
			<?php if($drawOverlay) : ?>
				<div class="overlay horizontal">
					<a class="actions selected-file" title="<?php echo __("Select this file"); ?>"><i class="icon-check-empty"></i></a>

					<?php if($showShare) : ?>
						<a class="actions share" title="<?php echo __("Share this file"); ?>" href="javascript: void(0);"><i class="icon-share"></i></a>
					<?php endif; ?>

					<?php if($drawFavorites) : ?>
						<?php if($item = FavoritesPeer::getFavorite($file->getId(), FavoritesPeer::__TYPE_FILE, $sf_user->getId())): ?>

								<a class="actions unfavorites" title="<?php echo __("Remove from favorites"); ?>" href="javascript: void(0);"><i class="icon-star"></i></a>

						<?php else: ?>

								<a class="actions favorites" title="<?php echo __("Add to favorites"); ?>" href="javascript: void(0);"><i class="icon-star-empty"></i></a>

						<?php endif; ?>
					<?php endif; ?>

					<?php if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH) : ?>

							<a class="actions basket" href="javascript: void(0);" title="<?php echo __("Add to collection"); ?>" onclick='addToBasket("file", <?php echo $file->getId(); ?>);'><i class="icon-pushpin"></i></a>

					<?php endif; ?>

					<div class="file-details">
						<div><?php echo myTools::getSize($file->getSize()).($file->getHeight() && $file->getWidth() ? " | ".$file->getWidth()."x".$file->getHeight()." ".__("pixels") : ""); ?></div>
						<div><?php echo __("Uploaded by"); ?> <?php echo $file->getUser()->getFullname(); ?></div>
					</div>
				</div>
			<?php endif; ?>
			<a class="thumbnail" href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>">
				<img src="" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" <?php echo $properties; ?> />
			</a>
			<div class="info-file">
				<div class="title">
					<a href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>">
						<?php if ($roleGroup <= RolePeer::__ADMIN) : ?>
							<?php if (FileWaitingPeer::retrieveByFileIdAndType($file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE)) : ?>
								<i class="icon-trash tooltip" name="<?php echo __("Pending deletion"); ?>"></i> 
							<?php endif; ?>
	
							<?php if ($file->getState() == FilePeer::__STATE_WAITING_VALIDATE) : ?>
								<i class="icon-ok tooltip" name="<?php echo __("Pending validation"); ?>"></i>
							<?php endif; ?>
						<?php endif; ?>

						<?php
							$pos = strrpos(strtolower($file), ".".$file->getExtention());

							if ($pos === false) {
								echo $file;
							}
							else {
								echo substr($file, 0, $pos);
							}
						?>
					</a>
				</div>
				<div class="mime">
					<?php echo $file->getExtention(); ?>
				</div>
			</div>
		</div>
	</div>
	<script>
		jQuery(document).ready(function() {
			if(jQuery(".item-selection[data-id^=<?php echo $file->getId(); ?>]").length > 0)
				jQuery(".item-file[data-id^=file_<?php echo $file->getId(); ?>]").find(".actions.basket").addClass("added");
		});
	</script>
<?php endif; ?>