<div id="admin-user-album-right-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_edit", array("id" => $user->getId())), "text" => $user->__toString()),
			array("link" => path("@admin_user_album_right_list", array("user" => $user->getId())), "text" => __("Albums's rights")),
		));
	?>

	<?php include_partial("admin/userTab", array("user" => $user, "selected" => "album_right")); ?>
	<?php include_partial("admin/userRightAdd", array("user" => $user, "albums" => $albumsCanAdd, 
		"csrfToken" => $csrfToken, "roles" => $roles)); ?>

	<div class="search-block clearfix">
		<div class="pull-left">
			
			<ul class="filter">
				<li>
					<?php echo __("Show");?>
				</li>

				<li>
					<a class="<?php if (!$currentInherit) echo "selected"?>" href="<?php echo path("@admin_user_album_right_list", 
							merge_request_params(null, array("inherit", "page")));?>"><?php echo __("ALL")?>
					</a> 
				</li>

				<li>
					<a class="<?php if ($currentInherit == 1) echo "selected"?>" href="<?php echo path("@admin_user_album_right_list", 
							merge_request_params(array("inherit" => 1), array("page")));?>"><?php echo __("ONLY USERS");?></a>
				</li>

				<li>
					<a class="<?php if ($currentInherit == 2) echo "selected"?>" href="<?php echo path("@admin_user_album_right_list", 
							merge_request_params(array("inherit" => 2), array("page")));?>"><?php echo __("ONLY GROUPS");?></a>
				</li>

				<li>
					<a class="<?php if ($currentInherit == 3) echo "selected"?>" href="<?php echo path("@admin_user_album_right_list", 
							merge_request_params(array("inherit" => 3), array("page")));?>"><?php echo __("ONLY ALBUMS");?></a>
				</li>
			</ul>
		</div>

		<!--
		<form class="form-search pull-right">
			<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>

			<div class="input-append">
				<input name="keyword" type="text" class="input-medium search-query" placeholder="<?php echo __("Search")?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>
		-->
	</div>
	
	<table id="permissions-table" class="table table-bordered">
		<thead>
			<tr>
				<th class="album" rowspan="2"><?php echo __("Albums")?></th>
				<th colspan="3"><?php //echo __("Roles")?><?php echo __("Permissions"); ?></th>
				<th class="album" rowspan="2"><?php echo __("Actions");?></th>
			</tr>
			<tr>
				<?php foreach ($roles as $role) :?>
					<th><?php echo $role->getName(); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		
		<tbody>
				<?php foreach ($albums as $album):?>
					<?php 
						$object = RightUtils::getObjectForAlbumAndUser($album->getRawValue(), $user->getRawValue());
						
						// associé directement au user
						$isUser = $object instanceof UserGroup;
						
						// via un groupe
						$isGroup = $object instanceof UnitGroup;
						
						// via un album
						$isAlbum = $object instanceof Groupe;
					?>
					<tr>
						<td>
							<i class="icon-book"></i> <?php echo $album->getName(); ?>

							<div class="inherit-right-by">
								<?php if ($isUser):?>
								<?php elseif ($isGroup):?>
									
									<?php 
										$path = path("admin_group_permission_list", array("id" => $object->getUnit()->getId()));

										echo __("inherits rights from the group %name", array("%name" => '<a target="blank" href="'.
											$path.'"> <i class="icon-group"></i> '.
											$object->getUnit()->getName()."</a>"));
									?>
								<?php elseif ($isAlbum):?>
									<?php echo __("inherits rights from the album \"%name\"", array("%name" => $object->getName()));?>
								<?php endif?>
							</div>
						</td>
						<?php foreach ($roles as $role) :?>
							<?php 
								$roleId = $role->getId();
								$radioName = "radio-".$album->getId();
							?>
							<td class="text-center">
								<?php if ($isUser): ?>
									<input data-album-id="<?php echo $album->getId()?>" data-user-id="<?php echo $user->getId()?>" 
										name="<?php echo $radioName;?>" value="<?php echo $roleId; ?>" 
										type="radio" <?php echo $roleId == $object->getCredential()->getId() ? "checked" : ""; ?>/>
								<?php else: ?>
									<input disabled name="<?php echo $radioName;?>" type="radio" 
										<?php echo $roleId == $object->getCredential()->getId()  ? "checked" : ""; ?>/>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>

						<td class="text-center">
							<?php if ($isUser):?>
								<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_user_album_right_delete", 
										array("album" => $album->getId(), "user"=> $user->getId(), "csrfToken" => $csrfToken)); ?>">
									<i class="icon-trash"></i> <?php echo __("Remove");?>
								</a>
							<?php endif;?>
						</td>
					</tr>
				<?php endforeach;?>
		</tbody>
	</table>

	<?php echo pagination($albums, "@admin_user_album_right_list");?>
</div>