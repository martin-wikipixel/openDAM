<?php $roleGroup = $sf_user->getRole($folder->getGroupeId()); ?>

<?php if($roleGroup <= RolePeer::__CONTRIB) :?>
	<div class="navigation">
		<?php if ($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN && ($sf_user->hasCredential("admin") || $sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $sf_user->getId())) || ($roleGroup == RolePeer::__CONTRIB && ($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) || $folder->getUserId() == $sf_user->getId()))) :?>
			
				<a href='<?php echo url_for("folder/edit?group_id=".$folder->getGroupeId()."&id=".$folder->getId());?>' rel='facebox' style='<?php echo $selected == "edit" ? "text-decoration:underline; font-weight:bold;" : ""; ?>'><?php echo __("Informations"); ?></a>

		<?php endif; ?>

		<?php if ($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN && ($sf_user->hasCredential("admin") || $sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $sf_user->getId())) || ($roleGroup == RolePeer::__CONTRIB && ($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) || $folder->getUserId() == $sf_user->getId()))) :?>
			 &nbsp; - &nbsp; <a href='<?php echo url_for("folder/move?id=".$folder->getId());?>' rel='facebox' style='<?php echo $selected == "move" ? "text-decoration:underline; font-weight:bold;" : ""; ?>'><?php echo __("Actions"); ?></a>
		<?php endif; ?>
	</div>
<?php endif; ?>