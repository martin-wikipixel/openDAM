<?php
	$role = $sf_user->getRole($group->getId());

	if($role && $role <= RolePeer::__CONTRIB) {
		$upload = get_slot('link_upload');
	}
	else {
		$upload = null;
	}
?>

<?php if($role && $role <= RolePeer::__ADMIN): ?>
	<li><a href='javascript: void(0);' id="add_folder"><i class="icon-plus-sign"></i> <?php echo __("Create a folder"); ?></a></li>
<?php endif; ?>

<?php if(!empty($upload) && $sf_user->getConstraint($group->getId(), ConstraintPeer::__IMPORT, RolePeer::__ADMIN)): ?>
	<li><a href="<?php echo url_for($upload); ?>" data-toogle="modal-iframe"><i class="icon-upload"></i> <?php echo __("Import files"); ?></a></li>
<?php endif; ?>


	<?php if($role && $role <= RolePeer::__ADMIN) : ?>
		<li><a href="javascript: void(0);" id="share_group"><i class="icon-share"></i> <?php echo __("Share this album"); ?></a></li>
	<?php endif; ?>


<li class="container-dropdown">
	<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-edit"></i> <?php echo __("Actions"); ?> <i class="icon-caret-down"></i></a>
	<ul class="dropdown-menu" role="menu">
		<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_GEOLOCALISATION)) : ?>
			<li><a href="<?php echo url_for("map/file?group_id=".$group->getId()); ?>" rel="facebox"><i class="icon-globe"></i> <?php echo __("Show on map"); ?></a></li>
		<?php endif; ?>
		
		<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_FAVORITE)) : ?>

				<?php if($item = FavoritesPeer::getFavorite($group->getId(), FavoritesPeer::__TYPE_GROUP, $sf_user->getId())) : ?>
					<li><a class="bread unfavorites" href="javascript: void(0);"><i class="icon-star"></i> <?php echo __("Remove from favorites"); ?></a></li>
				<?php else: ?>
					<li><a class="bread favorites" href="javascript: void(0);"><i class="icon-star-empty"></i> <?php echo __("Add to favorites"); ?></a></li>
				<?php endif; ?>

		<?php endif; ?>


			<?php if($role && $role <= RolePeer::__ADMIN && (($sf_user->isAdmin()) && $sf_user->getCustomerId() == $group->getCustomerId())) : ?>
				<li><a href="<?php echo url_for("group/merge?group_from=".$group->getId())?>" rel="facebox"><i class="icon-exchange"></i> <?php echo __("Merge")?></a></li>
				<li><a href="<?php echo url_for("group/remove?id=".$group->getId())?>" rel="facebox"><i class="icon-remove"></i> <?php echo __("Delete")?></a></li>
			<?php endif; ?>

	</ul>
</li>

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
