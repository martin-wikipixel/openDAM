<div id="album-right-group-list-page">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@homepage"), "text" => __("Homepage")),
			array("link" => path("@album_show", array("id" => $album->getId())), "text" => $album->getName()),
			array("link" => path("@album_right_user_list", array("album" => $album->getId())), "text" => __("Management of access right")),
		));
	?>

	<?php include_partial("album/rightTab", array("album" => $album, "selected" => "group"))?>

	<?php if ($sf_user->isAdmin() && !$isExternalAlbum) : ?>
		<div id="admin-top-bar">
			<a href="<?php echo path("@admin_group_list"); ?>">
				<i class="icon-group"></i> <?php echo __("Create a user group of company"); ?>
			</a>
		</div>
	<?php endif;?>

	<div class="search-block clearfix">
		<div class="pull-left">
			<ul class="filter">
				<li>
					<?php echo __("Show");?>
				</li>

				<li>
					<a class="<?php if (!$currentRole && !$currentLetter) echo "selected"?>" href="<?php echo 
						path("@album_right_group_list", 
						merge_request_params(null, array("role", "page", "letter")));?>">
						<?php echo __("ALL"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__ADMIN) echo "selected"?>" href="<?php echo 
						path("@album_right_group_list", 
						merge_request_params(array("role" => RolePeer::__ADMIN), array("page", "letter")));?>">
						<?php echo __("ADMINISTRATION"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__CONTRIB) echo "selected"?>" href="<?php echo 
						path("@album_right_group_list", 
						merge_request_params(array("role" => RolePeer::__CONTRIB), array("page", "letter")));?>">
						<?php echo __("WRITING"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__READER) echo "selected"?>" href="<?php echo 
						path("@album_right_group_list", 
						merge_request_params(array("role" => RolePeer::__READER), array("page", "letter")));?>">
						<?php echo __("READING"); ?>
					</a>
				</li>
			</ul>
			
			<?php if ($letters->getRawValue()) : ?>
				<ul class="filter">
					<li>
						<?php echo __("Sort by name");?>
					</li>
					<?php foreach ($letters as $letter) : ?>
						<li>
							<a class="<?php if ($currentLetter == $letter) echo "selected"?>" href="<?php echo 
								path("@album_right_group_list", 
								merge_request_params(array("letter" => $letter), array("page")));?>">
								<?php echo $letter;?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<form class="form-search pull-right">
			<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>
	
			<div class="input-append">
				<input name="keyword" type="text" class="input-medium search-query"
					placeholder="<?php echo __("Search")?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>
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
						<i class="icon-group"></i> <?php echo $group->getName(); ?>
					</td>
					
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

	<?php echo pagination($groups, "@album_right_group_list", query_params());?>
</div>