<?php
	$roleGroup = $sf_user->getRole($folder->getGroupeId());
	$role = Array();

	if ($roleGroup) {
		if ($roleGroup < RolePeer::__ADMIN) {
			$role = Array("basket", "favorite", "info", "move", "copy", "map", "delete", "rotate");
		}
		else {
			if($roleGroup == RolePeer::__ADMIN) {
				if ($sf_user->hasCredential("admin")) {
					$role = Array("basket", "favorite", "info", "move", "copy", "map", "delete", "rotate");
				}
				elseif ($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$role = Array("basket", "favorite", "info", "move", "copy", "map", "delete", "rotate");
				}
				elseif ($folder->getUserId() == $sf_user->getId()) {
					$role = Array("basket", "favorite", "info", "move", "copy", "map", "delete", "rotate");
				}
				else {
					$role = Array("basket", "favorite", "copy", "map");
				}
			}
			elseif ($groupRole == RolePeer::__CONTRIB) {
				if ($sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$role = Array("basket", "favorite", "info", "move", "copy", "map", "delete", "rotate");
				}
				elseif ($folder->getUserId() == $sf_user->getId()) {
					$role = Array("basket", "favorite", "info", "move", "copy", "map", "delete", "rotate");
				}
				else {
					$role = Array("basket", "favorite", "copy", "map");
				}
			}
			else {
				$role = Array("basket", "favorite", "map");
			}
		}
	}
	else {
		$role = Array();
	}
?>

<li><span id="countSelection">0</span> <i class="icon-picture"></i></li>
<?php if(in_array("basket", $role)) : ?>
	<li><a href="javascript: void(0);" class="selected-basket"><i class="icon-pushpin"></i> <?php echo __("Add to collection"); ?></a></li>
<?php endif; ?>

<?php if(in_array("favorite", $role)) : ?>
	<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_FAVORITE)) : ?>
		<li><a href="javascript: void(0);" class="selected-favorites"><i class="icon-star-empty"></i> <?php echo __("Favorites"); ?></a></li>
	<?php endif; ?>
<?php endif; ?>

<?php if(in_array("info", $role)) : ?>
	<li><a href="javascript: void(0);" class="selected-info"><i class="icon-info-sign"></i> <?php echo __("Edit infos"); ?></a></li>
<?php endif; ?>

<?php if(in_array("move", $role)) : ?>
	<li><a href="javascript: void(0);" class="selected-move"><i class="icon-move"></i> <?php echo __("Move"); ?></a></li>
<?php endif; ?>

<?php if(in_array("copy", $role)) : ?>
	<li><a href="javascript: void(0);" class="selected-copy"><i class="icon-copy"></i> <?php echo __("Copy"); ?></a></li>
<?php endif; ?>

<?php if(in_array("map", $role)) : ?>
	<li><a href="javascript: void(0);" class="selected-map"><i class="icon-globe"></i> <?php echo __("Show on map"); ?></a></li>
<?php endif; ?>

<?php if(in_array("delete", $role)) : ?>
	<li><a href="javascript: void(0);" class="selected-remove"><i class="icon-remove"></i> <?php echo __("Delete"); ?></a></li>
<?php endif; ?>