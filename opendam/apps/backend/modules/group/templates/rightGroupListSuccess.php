<div id="right-group-list-page">
	<h2><?php echo __("Management of access right of"); ?> "<?php echo $album->getName(); ?>"</h2>

	<?php if ($sf_user->isAdmin()) : ?>
		<?php include_partial("group/right_tab", array("selected" => "album", "album" => $album)); ?>
	<?php endif; ?>

	<?php include_partial("group/rightTab", array("album" => $album, "selected" => "group"))?>

	<?php if ($sf_user->isAdmin()) : ?>
		<div class="pull-right">
			<a href="<?php echo path("@admin_group_list"); ?>" target="_blank">
				<i class="icon-group"></i> <?php echo __("Create a user group of company"); ?>
			</a>
		</div>

		<div class="clearfix"></div>
	<?php endif;?>

	<div class="search-block clearfix">
		<div class="pull-left">
			<ul class="filter">
				<li>
					<?php echo __("Show");?>
				</li>

				<li>
					<a class="<?php if (!$currentRole && !$currentLetter) echo "selected"?>" href="<?php echo 
						path("@group_right_group_list", 
						merge_request_params(null, array("role", "page", "letter")));?>">
						<?php echo __("ALL"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__ADMIN) echo "selected"?>" href="<?php echo 
						path("@group_right_group_list", 
						merge_request_params(array("role" => RolePeer::__ADMIN), array("page", "letter")));?>">
						<?php echo __("ADMINISTRATION"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__CONTRIB) echo "selected"?>" href="<?php echo 
						path("@group_right_group_list", 
						merge_request_params(array("role" => RolePeer::__CONTRIB), array("page", "letter")));?>">
						<?php echo __("WRITING"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__READER) echo "selected"?>" href="<?php echo 
						path("@group_right_group_list", 
						merge_request_params(array("role" => RolePeer::__READER), array("page", "letter")));?>">
						<?php echo __("READING"); ?>
					</a>
				</li>
			</ul>
		</div>

		<form class="form-search pull-right">
			<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>
	
			<div class="input-append">
				<input name="keyword" type="text" class="input-medium search-query"
					placeholder="<?php echo __("Search")?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>

		<div class="clearfix"></div>

		<?php if ($letters->getRawValue()) : ?>
			<div class="pull-left">
				<ul class="filter">
					<li>
						<?php echo __("Sort by name");?>
					</li>
					<?php foreach ($letters as $letter) : ?>
						<li>
						<a class="<?php if ($currentLetter == $letter) echo "selected"?>" href="<?php echo 
							path("@group_right_group_list", 
							merge_request_params(array("letter" => $letter), array("page")));?>">
							<?php echo $letter;?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>

	<table id="permissions-table" class="table table-bordered">
		<thead>
			<tr>
				<th class="album" rowspan="2"><?php echo __("Name")?></th>
				<th class="text-center" colspan="4"><?php echo __("Permissions"); ?></th>

			</tr>
			<tr>
				<?php foreach ($roles as $role) :?>
					<th class="text-center"><?php echo $role->getName(); ?></th>
				<?php endforeach; ?>
				
				<th class="text-center"><?php echo __("n/a");?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($groups as $group):?>
				<?php 
					$radioName = "radio-".$group->getId();
					$groupAlbum = UnitGroupPeer::retrieveByUnitIdAndGroupeId($group->getId(), $album->getId());
				?>
				<tr>
					<td>
					<?php if ($sf_user->isAdmin()): ?>
						<a href="<?php echo 
							path("@group_right_group_search", 
							array("album" => $album->getId(), "id" => $group->getId())); ?>">
								<i class="icon-group"></i> <?php echo $group->getName(); ?>
						</a>
					<?php else: ?>
						<i class="icon-group"></i> <?php echo $group->getName(); ?>
					<?php endif; ?>

					<?php foreach ($roles as $role) :?>
						<?php 
							$roleId = $role->getId();
						?>
						<td class="text-center">
							<input data-action="update" name="<?php echo $radioName;?>" value="<?php echo $roleId; ?>" 
								data-album-id="<?php echo $album->getId()?>"
								data-group-id="<?php echo $group->getId()?>"
								type="radio" <?php echo ($groupAlbum 
										&& $groupAlbum->getCredential()->getId() == $roleId) ? "checked" : ""; ?>/>
						</td>
					<?php endforeach; ?>

					<td class="text-center">
						<input data-action="delete" name="<?php echo $radioName;?>" value="" 
							data-album-id="<?php echo $album->getId()?>" data-group-id="<?php echo $group->getId()?>"
							type="radio" <?php echo $groupAlbum === null ? "checked" : ""; ?>/>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo pagination($groups, "@group_right_group_list", query_params());?>
</div>