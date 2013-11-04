<?php
	$roleGroup = $sf_user->getRole($file->getGroupeId());
	$role = Array();

	if ($roleGroup) {
		if ($roleGroup < RolePeer::__ADMIN) {
			$role = Array("download", "basket", "favorite", "print", "email", "version", "edit", "move", "copy", "remove", "replace");
		}
		else {
			if ($roleGroup == RolePeer::__ADMIN) {
				if ($sf_user->hasCredential("admin")) {
					$role = Array("download", "basket", "favorite", "print", "email", "version", "edit", "move", "copy", "remove", "replace");
				}
				elseif ($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$role = Array("download", "basket", "favorite", "print", "email", "version", "edit", "move", "copy", "remove", "replace");
				}
				elseif ($file->getUserId() == $sf_user->getId()) {
					$role = Array("download", "basket", "favorite", "print", "email", "version", "edit", "move", "copy", "remove", "replace");
				}
				else {
					$role = Array("download", "basket", "favorite", "print", "email", "copy");
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB)
			{
				if ($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$role = Array("download", "basket", "favorite", "print", "email", "version", "edit", "move", "copy", "remove", "replace");
				}
				elseif ($file->getUserId() == $sf_user->getId()) {
					$role = Array("download", "basket", "favorite", "print", "email", "version", "edit", "move", "copy", "remove", "replace");
				}
				else {
					$role = Array("download", "basket", "favorite", "print", "email", "copy");
				}
			}
			else {
				$role = Array("download", "basket", "favorite", "print", "email");
			}
		}
	}
	else {
		$role = Array();
	}
?>

<?php if(in_array("download", $role)) : ?>
	<?php if($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__DOWNLOAD, RolePeer::__READER)) : ?>
		<?php if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) : ?>
			<li><a href="javascript: void(0);" class="disabled tooltip" name="<?php echo __("Unauthorized disclosure"); ?>"><i class="icon-download"></i> <?php echo __("Download"); ?></a></li>
		<?php else: ?>
			<li class="container-dropdown">
				<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-download"></i> <?php echo __("Download"); ?> <i class="icon-caret-down"></i></a>

				<ul class="dropdown-menu" role="menu">
					<li><a href="<?php echo url_for("download/download?id=".$file->getId()."&definition=original"); ?>"><i class="icon-double-angle-right"></i> <?php echo __("High definition"); ?></a><li>

					<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
						<li><a href="javascript: void(0);" class="button-custom-download"><i class="icon-double-angle-right"></i> <?php echo __("Custom export format"); ?></a><li>
					<?php endif; ?>

					<!-- <li><a href="<?php echo url_for("download/downloadNotice?id=".$file->getId()); ?>"><i class="icon-double-angle-right"></i> <?php echo __("Instruction"); ?></a><li> -->
				</ul>
			</li>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>

<?php if(in_array("basket", $role)) : ?>
	<?php if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) : ?>
		<li><a href="javascript: void(0);" class="disabled tooltip" name="<?php echo __("Unauthorized disclosure"); ?>"><i class="icon-pushpin"></i> <?php echo __("Add to collection"); ?></a></li>
	<?php else: ?>
		<li><a href="javascript: void(0);" onclick='addToBasket("file", "<?php echo $file->getId(); ?>");'><i class="icon-pushpin"></i> <?php echo __("Add to collection"); ?></a></li>
	<?php endif; ?>
<?php endif; ?>

<?php if(in_array("favorite", $role)) : ?>
	<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_FAVORITE)) : ?>
		<li>
			<?php if($item = FavoritesPeer::getFavorite($file->getId(), FavoritesPeer::__TYPE_FILE, $sf_user->getId())):?>
				<a class="bread unfavorites" href="javascript: void(0);"><i class="icon-star"></i> <?php echo __("Remove from favorites"); ?></a>
			<?php else: ?>
				<a class="bread favorites" href="javascript: void(0);"><i class="icon-star-empty"></i> <?php echo __("Add to favorites"); ?></a>
			<?php endif; ?>
		</li>
	<?php endif; ?>
<?php endif; ?>

<?php if(in_array("print", $role)) : ?>
	<?php $printType = explode(";",ConfigurationPeer::retrieveByType("_no_print_format")->getValue()); ?>
	<?php if(!in_array($file->getExtention(), $printType) && $sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__PRINT, RolePeer::__READER)) : ?>
		<li>
			<?php if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) : ?>
				<a href="javascript: void(0);" class="disabled tooltip" name="<?php echo __("Unauthorized disclosure"); ?>"><i class="icon-print"></i> <?php echo __("Print"); ?></a>
			<?php else: ?>
				<a href="javascript: void(0);" onclick='clickToPrint("<?php echo $file->getId(); ?>");'><i class="icon-print"></i> <?php echo __("Print"); ?></a>
			<?php endif; ?>
		</li>
	<?php endif; ?>
<?php endif; ?>

<?php if(in_array("email", $role)) : ?>
	<?php if($sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__PERMALINK_FILE) && $sf_user->getConstraint($file->getGroupeId(), ConstraintPeer::__SHARE, RolePeer::__READER)) : ?>
		<li>
			<?php if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) : ?>
				<a href="javascript: void(0);" class="disabled tooltip" name="<?php echo __("Unauthorized disclosure"); ?>"><i class="icon-envelope"></i> <?php echo __("Send by email"); ?></a>
			<?php else: ?>
				<a href='<?php echo url_for("file/sendFileForm?file_id=".$file->getId()); ?>' rel='facebox'><i class="icon-envelope"></i> <?php echo __("Send by email"); ?></a>
			<?php endif; ?>
		</li>
	<?php endif; ?>
<?php endif; ?>



<?php if(in_array("edit", $role) || in_array("version", $role) || in_array("move", $role) || in_array("copy", $role) || in_array("remove", $role) || in_array("replace", $role)) : ?>
	<li class="container-dropdown">
		<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-edit"></i> <?php echo __("Actions"); ?> <i class="icon-caret-down"></i></a>

		<ul class="dropdown-menu" role="menu">
			<?php if(in_array("version", $role)) : ?>
				<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_VERSIONNING)) : ?>
					<?php if(FilePeer::hasHistory($file->getPath().DIRECTORY_SEPARATOR, $file)) : ?>
						<li><a href='<?php echo url_for("file/restore?id=".$file->getId()); ?>' rel='faceframe'><i class="icon-refresh"></i> <?php echo __("Restore versions"); ?></a></li>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php if(in_array("move", $role)) : ?>
				<li><a href='<?php echo url_for('file/move?id='.$file->getId()."&folder_id=".$file->getFolderId()); ?>' rel='facebox'><i class="icon-move"></i> <?php echo __("Move file"); ?></a></li>
			<?php endif; ?>

			<?php if(in_array("copy", $role)) : ?>
				<li><a href='<?php echo url_for('file/copy?id='.$file->getId()."-&folder_id=".$file->getFolderId()); ?>' rel='facebox'><i class="icon-copy"></i> <?php echo __("Copy file"); ?></a></li>
			<?php endif; ?>

			<?php if(in_array("remove", $role)) : ?>
				<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_APPROVAL) && $roleGroup > RolePeer::__ADMIN) : ?>
					<?php if(!FileWaitingPeer::haveWaitingFile($sf_user->getId(), $file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE)) : ?>
						<li><a href='<?php echo url_for('file/deleteOnDemand?id='.$file->getId()); ?>' rel='facebox'><i class="icon-remove"></i> <?php echo __("Remove file"); ?></a></li>
					<?php endif; ?>
				<?php else: ?>
					<li><a href='<?php echo url_for('file/delete?id='.$file->getId()); ?>' onclick='return confirm("<?php echo __('Are you sure to delete this file?'); ?>");'><i class="icon-remove"></i> <?php echo __("Remove file"); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>

			<?php if(in_array("replace", $role)) : ?>
				<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
					<li><a href='<?php echo url_for('file/replace?id='.$file->getId()); ?>' data-toogle="modal-iframe"><i class="icon-paste"></i> <?php echo __('Replace file'); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>

			<?php if(in_array("edit", $role)) : ?>
				<?php $retouchType = explode(";",ConfigurationPeer::retrieveByType("_no_retouch_format")->getValue()); ?>
				<?php if($file->getType() == FilePeer::__TYPE_PHOTO && $sf_user->haveAccessModule(ModulePeer::__MOD_RETOUCH) && !in_array($file->getExtention(), $retouchType)) : ?>
					<li class="dropdown-submenu">
						<a href="javascript: void(0);"><i class="icon-pencil"></i> <?php echo __("Retouch media"); ?></a>
			
						<ul class="dropdown-menu" role="menu">
							<?php if($file->getType() == FilePeer::__TYPE_PHOTO) : ?>
								<li><a href="javascript: void(0);" onclick="rotatePicture('90', '<?php echo $file->getId(); ?>');"><i class="icon-repeat"></i> <?php echo __("Rotate 90° clockwise"); ?></a></li>
								<li><a href="javascript: void(0);" onclick="rotatePicture('-90', '<?php echo $file->getId(); ?>');"><i class="icon-undo"></i> <?php echo __("Rotate 90° counterclockwise"); ?></a></li>
								<li><a href="javascript: void(0);" onclick="rotatePicture('180', '<?php echo $file->getId(); ?>');"><i class="icon-refresh"></i> <?php echo __("Rotate 180°"); ?></a></li>
							<?php endif; ?>
						</ul>
					</li>
				<?php endif; ?>
			<?php endif; ?>
		</ul>
	</li>
<?php endif; ?>