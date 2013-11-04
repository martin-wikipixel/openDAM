<?php
	$has_access = $sf_user->getRole($group->getId());
	$request= null;

	if ($has_access) {
		$link = "href='".url_for("@group_show?id=".$group->getId())."'";

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
					$relative = path("@group_thumbnail", array("id" => $group->getId()));
				}
			}
			else {
				$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
				$relative = path("@group_thumbnail", array("id" => $group->getId()));
			}
		}
		else {
			$absolute = $group->getPathname();
			$relative = path("@group_thumbnail", array("id" => $group->getId()));
		}
	}
	else {
		if ($request = RequestPeer::getRequest($group->getId(), $sf_user->getId())) {
			$link = "href='".url_for('@request_cancel?group_id='.$group->getId())."'";
		}
		else {
			$link = "href='".url_for('request/sendRequest?group_id='.$group->getId()."&request_type=1")."' rel='facebox'";
		}

		$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
		$relative = $relative = path("@group_thumbnail", array("id" => $group->getId()));
	}

	$fileExists = file_exists($absolute);

	if ($fileExists) {
		$size = getimagesize($absolute);
	}
	else {
		$size = array(0, 0);
	}

	if ($size[0] == 0 && $size[1] == 0) {
		$fileExists = true;
		$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
		$relative = $relative = path("@group_thumbnail", array("id" => $group->getId()));
		$size = getimagesize($absolute);
	}
?>

<div class="group item-group item <?php echo $has_access ? "open" : ""; ?>" data-id="group_<?php echo $group->getId(); ?>" data-value="<?php echo $group->getId(); ?>">
	<div class="contain">
		<a <?php echo $link; ?>">
			<div class="thumbnail">
				<?php if ($fileExists):?>
					<img src="<?php echo $relative; ?>" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" />
				<?php endif?>
			</div>
		</a>
		<div class="info-group">
			<a <?php echo $link; ?>">
				<div class="title">
					
						<?php if($has_access) : ?>
							<i class="icon-book"></i>
						<?php else: ?>
							<i class="icon-lock"></i>
						<?php endif; ?>

						<?php echo $group->getName(); ?>
					

					<?php
						if (!$has_access) {
							if (!$request) {
								echo " | ".__("Send request for access");
							}
						}
					?>
				</div>
			</a>
			<div class="clearfix"></div>
			<span class="contain-inside">
				<?php
					$files = $group->getNumberOfFiles();
					echo $files.($files > 1 ? " ".__("files") : " ".__("file"));

					if ($files > 0) {
						echo " | ".MyTools::getSize($group->getSize());
					}

					if (!$has_access && $request) {
						echo "&nbsp;&nbsp;&nbsp;<i class='icon-time tooltip' name=\"".__("Request send on %1% at %2%", array("%1%" => $request->getCreatedAt("d/m/Y"), "%2%" => $request->getCreatedAt("H:i:s")))."\"></i>";
					}
				?>

				<?php if($has_access) : ?>
					<?php if($has_access <= RolePeer::__ADMIN): ?>
						<div class="container-dropdown pull-right">
							<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-cogs"></i> <i class="icon-caret-down"></i></a>
							<ul class="dropdown-menu">
								
									<li><a class="actions tooltip" data-toogle="modal-iframe" href="<?php echo path("@group_right_user_list", array("album" => $group->getId())); ?>" name="<?php echo __("Access management")."<br />".__("This section allows you to invite users and manage permissions to read and write."); ?>"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li>
								
	
								<li><a class="actions" href="<?php echo url_for("group/thumbnail?id=".$group->getId()); ?>" rel="facebox"><i class="icon-picture"></i> <?php echo __("Thumbnail"); ?></a></li>
	
								<li><a class="actions share" href="javascript: void(0);"><i class="icon-share"></i> <?php echo __("Share this album"); ?></a></li>
	
								
									<?php if($has_access && $has_access <= RolePeer::__ADMIN && (($sf_user->isAdmin()) && $sf_user->getCustomerId() == $group->getCustomerId())) : ?>
										<li><a class="actions" href="<?php echo url_for("group/merge?group_from=".$group->getId()); ?>" rel="facebox"><i class="icon-exchange"></i> <?php echo __("Merge"); ?></a></li>
			
										<li><a class="actions" href="<?php echo url_for("group/remove?id=".$group->getId()); ?>" rel="facebox"><i class="icon-remove"></i> <?php echo __("Remove"); ?></a></li>
									<?php endif;?>
								
							</ul>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</span>
		</div>
	</div>
</div>
