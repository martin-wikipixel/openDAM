<?php $role = $sf_user->getRole($group->getId()); ?>

<div class="breadcrumb-infos">
	<span><?php echo $group->getNumberOfFolders(); ?> <i class="icon-folder-close"></i></span>
	<span><?php echo $group->getNumberOfFiles(); ?> <i class="icon-file"></i></span>
	<span><?php echo MyTools::getSize($group->getSize()); ?></span>
	<?php if($role && $role <= RolePeer::__ADMIN) :?>
		<span class="container-dropdown">
			<a href="javascript: void(0);" class="custom-button dropdown-toggle"><i class="icon-cogs"></i> <i class="icon-caret-down"></i></a>
			<ul class="dropdown-menu" role="menu">
				
					<!-- <li><a href="<?php echo url_for("group/manageUsers?id=".$group->getId())?>" rel="facebox"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li> -->
					<li><a data-toogle="modal-iframe" href="<?php echo path("@group_right_user_list", array("album" => $group->getId())); ?>"><i class="icon-group"></i> <?php echo __("Rights and users"); ?></a></li>
					<li><a data-toogle="modal-iframe" href="<?php echo path("@group_right_constraint_list", array("album" => $group->getId())); ?>"><i class="icon-puzzle-piece"></i> <?php echo __("Manage constraints"); ?></a></li>
		
				<li><a href="<?php echo url_for("group/thumbnail?id=".$group->getId())?>" rel="facebox"><i class="icon-picture"></i> <?php echo __("Thumbnail")?></a></li>
				<li><a href="<?php echo url_for("group/tags?id=".$group->getId())?>" rel="facebox"><i class="icon-tags"></i> <?php echo __("Default tags")?></a></li>
				<li><a href="<?php echo url_for("group/fields?id=".$group->getId())?>" rel="facebox"><i class="icon-th-list"></i> <?php echo __("Specific field")?></a></li>
				<?php if($group->haveWaitingFiles()) : ?>
					<li><a href="<?php echo url_for("group/waiting?id=".$group->getId())?>" rel="facebox"><i class="icon-time"></i> <?php echo __("Files awaiting")?></a></li>
				<?php endif; ?>
			</ul>
		</span>
	<?php endif; ?>
</div>