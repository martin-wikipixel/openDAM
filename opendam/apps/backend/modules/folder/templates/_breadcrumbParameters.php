<?php
	$roleGroup = $sf_user->getRole($folder->getGroupeId());

	if($roleGroup) {
		if($roleGroup < RolePeer::__ADMIN) {
			$role = Array("right", "thumbnail", "default");
		}
		elseif($roleGroup == RolePeer::__ADMIN) {
			if($sf_user->hasCredential("admin")) {
				$role = Array("right", "thumbnail", "default");
			}
			elseif($sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$role = Array("right", "thumbnail", "default", );
			}
			elseif($folder->getUserId() == $sf_user->getId()) {
				$role = Array("right", "thumbnail", "default");
			}
			else {
				$role = Array();
			}
		}
		elseif($roleGroup == RolePeer::__CONTRIB) {
			if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) {
					$role = Array("right", "thumbnail", "default");
				}
				else {
					$role = Array("thumbnail", "default");
				}
			}
			elseif($folder->getUserId() == $sf_user->getId()) {
				if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) {
					$role = Array("right", "thumbnail", "default");
				}
				else {
					$role = Array("thumbnail", "default");
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
		$role = Array();
	}
?>
<div class="breadcrumb-infos">
	<span><?php echo $folder->getNumberOfFolders(); ?> <i class="icon-folder-close"></i></span>
	<span><?php echo $folder->getNumberOfFiles(); ?> <i class="icon-file"></i></span>
	<span><?php echo MyTools::getSize($folder->getSize()); ?></span>

	<?php if(in_array("right", $role) || in_array("thumbnail", $role) || in_array("default", $role)) : ?>
		<span class="container-dropdown">
			<a href="javascript: void(0);" class="custom-button dropdown-toggle"><i class="icon-cogs"></i> <i class="icon-caret-down"></i></a>

			<ul class="dropdown-menu" role="menu">
				<?php if(in_array("right", $role)) : ?>
					
						<!-- <li><a href="<?php echo url_for("folder/manageUsers?id=".$folder->getId()); ?>" rel="facebox"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li> -->
						<li><a data-toogle="modal-iframe" href="<?php echo path("@folder_right_user_list", array("folder" => $folder->getId())); ?>"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li>
					
				<?php endif; ?>
	
				<?php if(in_array("thumbnail", $role)) : ?>
					<li><a href="<?php echo url_for("folder/thumbnail?id=".$folder->getId())?>" rel="facebox"><i class="icon-picture"></i> <?php echo __("Thumbnail"); ?></a></li>
				<?php endif; ?>
	
				<?php if(in_array("default", $role)) : ?>
					<li><a href="<?php echo url_for("folder/default?id=".$folder->getId())?>" rel="facebox"><i class="icon-screenshot"></i> <?php echo __("Default values"); ?></a></li>
				<?php endif; ?>
			</ul>
		</span>
	<?php endif; ?>
</div>