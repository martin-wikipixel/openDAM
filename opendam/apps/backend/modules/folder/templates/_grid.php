<?php
	$roleGroup = $sf_user->getRole($folder->getGroupeId());
	$has_access = $sf_user->getRole($folder->getGroupeId(), $folder->getId());
	$request= null;

	$moveDnd = $roleGroup && $roleGroup <= RolePeer::__ADMIN ? true : false;

	if($has_access) {
		if($roleGroup) {
			if($roleGroup < RolePeer::__ADMIN) {
				$role = array("share", "edit", "move", "delete", "right", "thumbnail");
			}
			elseif($roleGroup == RolePeer::__ADMIN) {
				if($sf_user->hasCredential("admin")) {
					$role = array("share", "edit", "move", "delete", "right", "thumbnail");
				}
				elseif($sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$role = array("share", "edit", "move", "delete", "right", "thumbnail");
				}
				elseif($folder->getUserId() == $sf_user->getId()) {
					$role = array("share", "edit", "move", "delete", "right", "thumbnail");
				}
				else {
					$role = Array("share");
				}
			}
			elseif($roleGroup == RolePeer::__CONTRIB) {
				if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) {
						$role = array("share", "edit", "move", "delete", "right", "thumbnail");
					}
					else {
						$role = array("share", "edit", "move", "delete", "thumbnail");
					}
				}
				elseif($folder->getUserId() == $sf_user->getId()) {
					if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) {
						$role = array("share", "edit", "move", "delete", "right", "thumbnail");
					}
					else {
						$role = array("share", "edit", "move", "delete", "thumbnail");
					}
				}
				else {
					$role = Array();
				}
			}
			else {
				$role = Array();
			}
		}
		else {
			$role = array();
		}

		if (!$folder->getThumbnail()) {
			$relative = image_path("no-access-file-200x200.png");
			$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
		}
		else {
			$absolute = $folder->getRealPathname();
			$relative = path("@folder_thumbnail", array("id" => $folder->getId()));
		}

		if($sf_params->get("selected_tag_ids")) {
			$url = "href='".url_for("folder/show")."?id=".$folder->getId()."&selected_tag_ids[]=".implode("&selected_tag_ids[]=", $sf_params->get("selected_tag_ids")->getRawValue())."'";
		}
		else {
			$url = "href='".url_for("folder/show?id=".$folder->getId())."'";
		}
	}
	else {
		$role = array();
		$relative = image_path("no-access-file-200x200.png");
		$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";

		if($request = RequestPeer::getRequestFolder($folder->getId(), $sf_user->getId())) {
			$url = "href='".url_for('@request_cancel_folder?folder_id='.$folder->getId())."'";
		}
		else {
			$url = "href='".url_for('request/sendRequestFolder?folder_id='.$folder->getId()."&request_type=1")."' rel='facebox'";
		}
	}

	$size = getimagesize($absolute);
?>

<div class="folder item-folder item <?php echo $has_access ? "open" : ""; ?> <?php echo $moveDnd ? "moveDnd" : ""; ?>" data-id="folder_<?php echo $folder->getId(); ?>" data-value="<?php echo $folder->getId(); ?>">
	<div class="contain">
		<a <?php echo $url; ?>>
			<div class="thumbnail">
				<img src="<?php echo $relative; ?>" data-width="<?php echo $size[0]; ?>" data-height="<?php echo $size[1]; ?>" />
			</div>
		</a>
		<div class="info-folder">
			<a <?php echo $url; ?>>
				<div class="title">
					<?php if($has_access) : ?>
						<i class="icon-folder-close"></i>
					<?php else: ?>
						<i class="icon-lock"></i>
					<?php endif; ?>
	
					<?php echo $folder->getName(); ?>
				</div>
			</a>
			<div class="clearfix"></div>
			<span class="contain-inside">
				<?php
					$files = $folder->getNumberOfFiles();

					echo $files.($files > 1 ? " ".__("files") : " ".__("file"));

					if ($files > 0) {
						echo " | ".MyTools::getSize($folder->getSize());
					}

					if (!$has_access && $request) {
						echo "&nbsp;&nbsp;&nbsp;<i class='icon-time tooltip' name=\"".__("Request send on %1% at %2%", array("%1%" => $request->getCreatedAt("d/m/Y"), "%2%" => $request->getCreatedAt("H:i:s")))."\"></i>";
					}
				?>

				<?php if($has_access) : ?>
					<?php if((in_array("share", $role) && $folder->getFree()) || in_array("edit", $role) || in_array("move", $role) || in_array("delete", $role) || in_array("right", $role) || in_array("thumbnail", $role)) : ?>
						<div class="actions container-dropdown pull-right">
							<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-cogs"></i> <i class="icon-caret-down"></i></a>
							<ul class="dropdown-menu">
								<?php if(in_array("right", $role)) : ?>
									
										<!-- <li><a class="actions" title="<?php echo __("Access management"); ?>" href="<?php echo url_for("folder/manageUsers?id=".$folder->getId()); ?>" rel="facebox"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li> -->
										<li><a class="actions" title="<?php echo __("Access management"); ?>" data-toogle="modal-iframe" href="<?php echo path("@folder_right_user_list", array("folder" => $folder->getId())); ?>"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li>
									
								<?php endif; ?>
	
								<li>
									<a title="<?php echo __("Add to collection"); ?>" href="javascript: void(0);" onClick="addToBasket('folder', <?php echo $folder->getId()?>)">
										<i class="icon-pushpin"></i> <?php echo __("Add to collection"); ?></a>
									</li>
								
								<?php if(in_array("thumbnail", $role)) : ?>
									<li><a href="<?php echo url_for("folder/thumbnail?id=".$folder->getId()); ?>" rel="facebox"><i class="icon-picture"></i> <?php echo __("Thumbnail"); ?></a></li>
								<?php endif; ?>
								
								<?php if($folder->getFree() && in_array("share", $role) && $sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__PERMALINK_FOLDER)) : ?>
									<li><a class="actions share" title="<?php echo __("Share this folder"); ?>" href="javascript: void(0);"><i class="icon-share"></i> <?php echo __("Share this folder"); ?></a></li>
								<?php endif; ?>
		
								<?php if(in_array("edit", $role) || in_array("move", $role) || in_array("delete", $role)) : ?>
									<?php if(in_array("edit", $role)) : ?>
										<li><a href="<?php echo url_for("folder/edit?id=".$folder->getId()."&group_id=".$folder->getGroupeId()."&inside=1"); ?>" rel="facebox"><i class="icon-info-sign"></i> <?php echo __("Rename"); ?></a></li>
									<?php endif; ?>
	
									<?php if(in_array("move", $role)) : ?>
										<li><a href="<?php echo url_for('folder/move?id='.$folder->getId()); ?>" rel="facebox"><i class="icon-move"></i> <?php echo __("Move"); ?></a></li>
									<?php endif; ?>
	
									<?php if(in_array("delete", $role)) : ?>
										<li><a href="<?php echo url_for('folder/delete?id='.$folder->getId()); ?>" rel="facebox"><i class="icon-remove"></i> <?php echo __("Delete"); ?></a></li>
									<?php endif; ?>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</span>
		</div>
	</div>
</div>