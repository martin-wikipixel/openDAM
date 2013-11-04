<?php
	$roleGroup = $sf_user->getRole($folder->getGroupeId());

	if($roleGroup) {
		if($roleGroup < RolePeer::__ADMIN) {
			$role = Array("share", "slideshow", "map", "favorite", "folder", "files", "info", "recursive", "move", "delete");
		}
		elseif($roleGroup == RolePeer::__ADMIN) {
			if($sf_user->hasCredential("admin")) {
				$role = Array("share", "slideshow", "map", "favorite", "folder", "files", "info", "recursive", "move", "delete");
			}
			elseif($sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$role = Array("slideshow", "map", "favorite", "folder", "info", "recursive", "move", "delete");
			}
			elseif($folder->getUserId() == $sf_user->getId()) {
				$role = Array("slideshow", "map", "favorite", "folder", "info", "recursive", "move", "delete");
			}
			else {
				$role = Array("slideshow", "map", "favorite", "folder");
			}
	
			if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__IMPORT, RolePeer::__ADMIN)) {
				$role[] = "files";
			}

			if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__PERMALINK_FOLDER, RolePeer::__ADMIN)) {
				$role[] = "share";
			}
		}
		elseif($roleGroup == RolePeer::__CONTRIB) {
			if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$role = Array("slideshow", "map", "favorite", "files", "info", "recursive", "move", "delete");
			}
			elseif($folder->getUserId() == $sf_user->getId()) {
				$role = Array("slideshow", "map", "favorite", "files", "info", "recursive", "move", "delete");
			}
			else {
				$role = Array("slideshow", "map", "favorite", "files");
			}

			if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__SUB_FOLDER, RolePeer::__ADMIN)) {
				$role[] = "folder";
			}

			if($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__PERMALINK_FOLDER, RolePeer::__ADMIN)) {
				$role[] = "share";
			}
		}
		else {
			$role = Array("slideshow", "map", "favorite");
		}
	}
	else {
		$role = Array();
	}
?>

<?php if(in_array("folder", $role)) : ?>
	<li><a href="javascript: void(0);" id="add_folder"><i class="icon-plus-sign"></i> <?php echo __("Create a folder"); ?></a></li>
<?php endif; ?>

<?php if(in_array("files", $role)) : ?>
	<li><a href="<?php echo url_for("upload/uploadify?folder_id=".$folder->getId()); ?>" data-toogle="modal-iframe"><i class="icon-upload"></i> <?php echo __("Import files"); ?></a></li>
<?php endif; ?>

<?php if(in_array("share", $role)) : ?>
	<li><a href="javascript: void(0);" id="share_<?php echo $folder->getId(); ?>" <?php echo !$folder->getFree() ? "class='disabled tooltip' name='".__("This folder is locked.")."'" : ""; ?>><i class="icon-share"></i> <?php echo __("Share this folder"); ?></a></li>
<?php endif; ?>

<?php if(in_array("map", $role) || in_array("favorite", $role) || in_array("recursive", $role) || in_array("move", $role) || in_array("delete", $role)) : ?>
	<li class="container-dropdown">
		<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-edit"></i> <?php echo __("Actions"); ?> <i class="icon-caret-down"></i></a>

		<ul class="dropdown-menu" role="menu">
			<?php if(in_array("map", $role)) : ?>
				<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_GEOLOCALISATION)) : ?>
					<li><a href="javascript: void(0);" class="map"><i class="icon-globe"></i> <?php echo __("Show on map"); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if(in_array("favorite", $role)) : ?>
				<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_FAVORITE)) : ?>
					<?php if($item = FavoritesPeer::getFavorite($folder->getId(), FavoritesPeer::__TYPE_FOLDER, $sf_user->getId())):?>

							<li><a class="bread unfavorites" href="javascript: void(0);"><i class="icon-star"></i> <?php echo __("Remove from favorites"); ?></a></li>

					<?php else:?>

							<li><a class="bread favorites" href="javascript: void(0);"><i class="icon-star-empty"></i> <?php echo __("Add to favorites"); ?></a></li>

					<?php endif;?>
				<?php endif;?>
			<?php endif;?>

			<?php if(in_array("recursive", $role)) : ?>
				<li><a href="<?php echo url_for("folder/recursive?id=".$folder->getId())?>" rel="facebox"><i class="icon-retweet"></i> <?php echo __("Recursive modification"); ?></a></li>
			<?php endif; ?>

			<?php if(in_array("move", $role)) : ?>
				<li><a href="<?php echo url_for('folder/move?id='.$folder->getId()); ?>" rel="facebox"><i class="icon-move"></i> <?php echo __("Move thiss folder"); ?></a></li>
			<?php endif; ?>

			<?php if(in_array("delete", $role)) : ?>
				<li><a href="<?php echo url_for('folder/delete?id='.$folder->getId()); ?>" rel="facebox"><i class="icon-remove"></i> <?php echo __("Delete this folder"); ?></a></li>
			<?php endif; ?>
		</ul>
	</li>
<?php endif; ?>

<li class="pull-right">
	<div>
		<div class="custom-select">
			<?php echo __("Sort by"); ?>
			<select name="sort" id="sort">
				<?php foreach($sorts["values"] as $id => $label) :?>
					<option value="<?php echo $id; ?>" <?php echo $sorts["selected"] == $id ? "selected" : ""; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div>
		<div class="custom-select">
			<?php echo __("Per page"); ?>
			<select name="per_page" id="per_page">
				<?php foreach($results["values"] as $id => $label) :?>
					<option value="<?php echo $id; ?>" <?php echo $results["selected"] == $id ? "selected" : ""; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</li>
