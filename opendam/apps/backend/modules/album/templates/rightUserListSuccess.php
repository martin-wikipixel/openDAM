<div id="album-right-user-list-page">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@homepage"), "text" => __("Homepage")),
			array("link" => path("@album_show", array("id" => $album->getId())), "text" => $album->getName()),
			array("link" => path("@album_right_user_list", array("album" => $album->getId())), "text" => __("Management of access right")),
		));
	?>

	<?php include_partial("album/rightTab", array("album" => $album, "selected" => "user")); ?>

	<?php if ($sf_user->isAdmin() && !$isExternalAlbum) : ?>
		<div id="admin-top-bar">
			<a href="<?php echo path("@admin_user_new"); ?>">
				<i class="icon-user"></i> <?php echo __("Add user"); ?>
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
					<a class="<?php if (!$currentRole && !$currentState && !$currentLetter) echo "selected"?>"
						href="<?php echo path("@album_right_user_list", 
						merge_request_params(null, array("role", "page", "state", "letter")));?>">
						<?php echo __("ALL"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__ADMIN) echo "selected"?>" href="<?php echo 
						path("@album_right_user_list", 
						merge_request_params(array("role" => RolePeer::__ADMIN),
						array("page", "state", "letter")));?>">
						<?php echo __("ADMINISTRATION"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__CONTRIB) echo "selected"?>" href="<?php echo 
						path("@album_right_user_list", 
						merge_request_params(array("role" => RolePeer::__CONTRIB),
						array("page", "state", "letter")));?>">
						<?php echo __("WRITING"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__READER) echo "selected"?>" href="<?php echo 
						path("@album_right_user_list", 
						merge_request_params(array("role" => RolePeer::__READER),
						array("page", "state", "letter")));?>">
						<?php echo __("READING"); ?>
					</a>
				</li>

				<?php if (!$isExternalAlbum) : ?>
					<li>
						<a class="<?php if ($currentState == "active") echo "selected"?>" href="<?php echo 
							path("@album_right_user_list", 
							merge_request_params(array("state" => "active"), array("page", "role", "letter")));?>">
							<?php echo __("ACTIVE");?>
						</a>
					</li>
				<?php endif; ?>

				<li>
					<a class="<?php if ($currentState == "pending") echo "selected"?>" href="<?php echo 
						path("@album_right_user_list", 
						merge_request_params(array("state" => "pending"), array("page", "role", "letter")));?>">
						<?php echo __("PENDING");?>
					</a>
				</li>
			</ul>
			
			<?php if ($letters->count()):?>
				<ul class="filter">
					<li>
						<?php echo __("Sort by email");?>
					</li>
					<?php foreach ($letters as $letter) : ?>
						<li>
							<a class="<?php if ($currentLetter == $letter) echo "selected"?>" href="<?php echo 
								path("@album_right_user_list", 
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
					placeholder="<?php echo __("Search"); ?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>
	</div>

	<table id="permissions-table" class="table table-bordered">
		<thead>
			<tr>
				<th class="user text-centered" rowspan="2"><?php echo __("Users"); ?></th>
				<th class="text-centered" colspan="4"><?php echo __("Permissions"); ?></th>
				<th class="user text-centered" rowspan="2"><?php echo __("State"); ?></th>
				<th class="user text-centered" rowspan="2"><?php echo __("Actions"); ?></th>
			</tr>
			<tr>
				<?php foreach ($roles as $role) :?>
					<th class="text-centered"><?php echo $role->getName(); ?></th>
				<?php endforeach; ?>

				<th class="text-centered"><?php echo __("n/a");?></th>
			</tr>
		</thead>
	
		<tbody>
			<!-- add everybody -->
			<?php if (($page == 1 || !$page) && !$currentRole && !$currentState && !$currentLetter && !$keyword) : ?>
				<tr>
					<td><i class="icon-group"></i> <strong><?php echo __("unit.everybody")?></strong></td>
					<?php foreach ($roles as $role) :?>
						<td class="text-centered user">
							<?php if ($role->getId() > RolePeer::__ADMIN) : ?>
								<input data-album-id="<?php echo $album->getId()?>" 
									name="radio-everybody" value="<?php echo $role->getId(); ?>" 
									type="radio" 
									<?php if ($album->getFree() && $role->getId() == $album->getFreeCredential()) : ?>
										<?php echo "checked"; ?>
									<?php endif; ?>
									/>
							<?php else: ?>
								&nbsp;
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
	
					<td class="text-centered">
						<input data-album-id="<?php echo $album->getId()?>" 
							name="radio-everybody" value="5" 
							type="radio" 
							<?php echo !$album->getFree() ? "checked" : ""; ?> />
					</td>
					<td class="text-centered user">-</td>
					<td>&nbsp;</td>
				</tr>
			<?php endif; ?>

			<?php foreach ($users as $user): ?>
				<?php
					$radioName = "radio-".$user->getId();

					$object = RightUtils::getObjectForAlbumAndUser($album->getRawValue(), $user->getRawValue());

					// associé directement au user
					$isUser = $object instanceof UserGroup;

					// via un groupe
					$isGroup = $object instanceof UnitGroup;

					// via un album
					$isAlbum = $object instanceof Groupe;

					$requestObject = null;
					
					// via une demande d'accès (request)
					if (!$isUser && !$isGroup) {
						$requestObject = RequestPeer::getRequest($album->getId(), $user->getId());
					}

					$isRequest = $requestObject !== null;
				?>
				<tr>
					<td>
						<i class="icon-user"></i> <?php echo $user->getEmail(); ?>

						<?php if ($isExternalAlbum) : ?>
							<em>(<?php echo __("External"); ?>)</em>
						<?php endif; ?>

						<?php if ($isGroup) : ?>
							<?php
								if ($sf_user->isAdmin() && !$isExternalAlbum) {
									$path = path("group_right_group_search", 
										array("album" => $album->getId(), "id" => $object->getUnit()->getId()));
									
									$name = '<a href="'.$path.'">';
									$name .= '<i class="icon-group"></i> '.$object->getUnit()->getName().'</a>';
								}
								else {
									$name = '<i class="icon-group"></i> '.$object->getUnit()->getName();
								}

								echo " (".__("via %name", array("%name" => $name)).")";
							?>
						<?php endif; ?>
					</td>

					<?php foreach ($roles as $role) :?>
						<td class="text-centered">
							<input data-album-id="<?php echo $album->getId()?>" 
								data-user-id="<?php echo $user->getId()?>" 
								name="<?php echo $radioName;?>" value="<?php echo $role->getId(); ?>" 
								type="radio" 
								<?php if(!$isRequest && $object->getCredential()
										&& $role->getId() == $object->getCredential()->getId()) : ?>
									<?php echo "checked"; ?>
								<?php endif; ?>
								/>
						</td>
					<?php endforeach; ?>

					<td class="text-centered">
						<input data-album-id="<?php echo $album->getId()?>" data-user-id="<?php echo $user->getId()?>" 
							name="<?php echo $radioName;?>" value="" 
							type="radio" 
							<?php echo $isExternalAlbum ? "disabled" : ""; ?> />
					</td>

					<!-- state -->
					<td class="text-centered">
						<?php if ($isUser || $isGroup) : ?>
							<?php echo __("Active"); ?>
						<?php elseif ($isRequest) : ?>
							<?php echo __("Pending"); ?>
						<?php else: ?>
							-
						<?php endif;?>
					</td>

					<!-- actions -->
					<td class="text-centered">
						<?php if ($isUser || $isGroup) : ?>
							<a class="btn" data-action="notify-user" data-album-id="<?php echo $album->getId(); ?>"
									data-user-id="<?php echo $user->getId(); ?>" href="javascript: void(0);">
								<i class="icon-envelope"></i> <?php echo __("Notify"); ?>
							</a>
						<?php endif; ?>

						<?php if (($isUser && !$isExternalAlbum) || ($isRequest)) : ?>
							<a class="btn btn-danger" data-action="delete" 
									href="<?php echo path("@group_right_user_delete", 
									array(
										"album"		=> $album->getId(),
										"user"		=> $user->getId(),
										"csrfToken"	=> $csrfToken
									)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						<?php else: ?>
							&nbsp;
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo pagination($users, "@album_right_user_list");?>
</div>