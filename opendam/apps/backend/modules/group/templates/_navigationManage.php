<?php $role = $sf_user->getRole($group->getId()); ?>
<div class="navigation">
	<?php if($role <= RolePeer::__ADMIN) : ?>
		
			<a href='<?php echo url_for("group/step1?id=".$group->getId()); ?>' rel='facebox' style='<?php echo $selected == "step1" ? "text-decoration:underline; font-weight:bold;" : ""; ?>'><?php echo __("Informations"); ?></a>
	

		<?php if($group->haveWaitingFiles()) : ?>
			&nbsp; - &nbsp;<a href='<?php echo url_for("group/waiting?id=".$group->getId()); ?>' rel='facebox' style='<?php echo $selected == "files" ? "text-decoration:underline; font-weight:bold;" : ""; ?>'><?php echo __("Files awaiting"); ?></a>
		<?php endif; ?>

		

		<?php if (($sf_user->isAdmin()) && $sf_user->getCustomerId() == $group->getCustomerId()) : ?>
			
				&nbsp; - &nbsp;<a href='<?php echo url_for("group/merge?group_from=".$group->getId()); ?>' rel='facebox' style='<?php echo $selected == "merge" ? "text-decoration:underline; font-weight:bold;" : ""; ?>'><?php echo __("Actions"); ?></a>
		
		<?php endif; ?>
	<?php endif; ?>
</div>
