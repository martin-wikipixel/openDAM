<div id="right-user-list-page">
	<h2><?php echo __("Management of access right of"); ?> "<?php echo $album->getName(); ?>"</h2>

	<?php if ($sf_user->isAdmin()) : ?>
		<?php include_partial("group/right_tab", array("selected" => "album", "album" => $album)); ?>
	<?php endif; ?>

	<?php include_partial("group/rightTab", array("album" => $album, "selected" => "user")); ?>

	<?php if ($sf_user->isAdmin()) : ?>
		<div id="manage-user">
			<div class="pull-right">
				<a href="<?php echo path("@admin_user_new"); ?>" target="_blank">
					<i class="icon-user"></i> <?php echo __("Add user"); ?>
				</a>
			</div>
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
						href="<?php echo path("@group_right_user_list", 
						merge_request_params(null, array("role", "page", "state", "letter")));?>">
						<?php echo __("ALL"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__ADMIN) echo "selected"?>" href="<?php echo 
						path("@group_right_user_list", 
						merge_request_params(array("role" => RolePeer::__ADMIN),
						array("page", "state", "letter")));?>">
						<?php echo __("ADMINISTRATION"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__CONTRIB) echo "selected"?>" href="<?php echo 
						path("@group_right_user_list", 
						merge_request_params(array("role" => RolePeer::__CONTRIB),
						array("page", "state", "letter")));?>">
						<?php echo __("WRITING"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == RolePeer::__READER) echo "selected"?>" href="<?php echo 
						path("@group_right_user_list", 
						merge_request_params(array("role" => RolePeer::__READER),
						array("page", "state", "letter")));?>">
						<?php echo __("READING"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentState == "active") echo "selected"?>" href="<?php echo 
						path("@group_right_user_list", 
						merge_request_params(array("state" => "active"), array("page", "role", "letter")));?>">
						<?php echo __("ACTIVE");?>
					</a>
				</li>


				<li>
					<a class="<?php if ($currentState == "pending") echo "selected"?>" href="<?php echo 
						path("@group_right_user_list", 
						merge_request_params(array("state" => "pending"), array("page", "role", "letter")));?>">
						<?php echo __("PENDING");?>
					</a>
				</li>
			</ul>
		</div>

		<form class="form-search pull-right">
			<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>
	
			<div class="input-append">
				<input name="keyword" type="text" class="input-medium search-query"
					placeholder="<?php echo __("Search"); ?>" value="<?php echo $keyword;?>">
				<button class="btn"><i class="icon-search"></i></button>
			</div>
		</form>

		<div class="clearfix"></div>

		<?php if ($letters->getRawValue()) : ?>
			<div class="pull-left">
				<ul class="filter">
					<li>
						<?php echo __("Sort by email");?>
					</li>
					<?php foreach ($letters as $letter) : ?>
						<li>
						<a class="<?php if ($currentLetter == $letter) echo "selected"?>" href="<?php echo 
							path("@group_right_user_list", 
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
				<th class="user text-center" rowspan="2"><?php echo __("Users"); ?></th>
				<th class="text-center" colspan="4"><?php echo __("Permissions"); ?></th>
				<th class="user text-center" rowspan="2"><?php echo __("State"); ?></th>
				<th class="user text-center" rowspan="2"><?php echo __("Actions"); ?></th>
			</tr>
			<tr>
				<?php foreach ($roles as $role) :?>
					<th class="text-center"><?php echo $role->getName(); ?></th>
				<?php endforeach; ?>

				<th class="text-center"><?php echo __("n/a");?></th>
			</tr>
		</thead>
	
		<tbody>
			<?php if ($sf_user->isAdmin() && $album->getCustomerId() == $sf_user->getCustomerId() 
				&& $page == 1 && !$currentRole && !$currentState && !$currentLetter && !$keyword) : ?>
				<tr>
					<td class="user"><i class="icon-group"></i> <?php echo __("unit.everybody"); ?></td>
					<?php foreach ($roles as $role) :?>
						<td class="text-center user">
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
	
					<td class="text-center user">
						<input data-album-id="<?php echo $album->getId()?>" 
							name="radio-everybody" value="5" 
							type="radio" 
							<?php echo !$album->getFree() ? "checked" : ""; ?> />
					</td>
					<td class="user">-</td>
					<td class="user">&nbsp;</td>
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

					// via une demande d'accès (request)
					if (!$isUser && !$isGroup) {
						$requestObject = RequestPeer::getRequest($album->getId(), $user->getId());
					}
					else {
						$requestObject = null;
					}

					$isRequest = $requestObject instanceof Request;
				?>
				<tr>
					<td class="user">
						<?php if ($sf_user->isAdmin()) : ?>
							<a href="<?php echo 
								path("@group_right_user_search", 
								array("album" => $album->getId(), "id" => $user->getId())); ?>">
									<i class="icon-user"></i> <?php echo $user->getEmail(); ?>
							</a>
						<?php else: ?>
							<i class="icon-user"></i> <?php echo $user->getEmail(); ?>
						<?php endif; ?>

						<?php if ($album->getCustomerId() != $user->getCustomerId()) : ?>
							<em>(<?php echo __("External"); ?>)</em>
						<?php endif; ?>

						<?php if ($isGroup) : ?>
							<?php
								if ($sf_user->isAdmin()) {
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

						<?php if ($user->getComment()) echo "<em>(".$user->getComment().")</em>"; ?>
					</td>

					<?php foreach ($roles as $role) :?>
						<td class="text-center user">
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

					<td class="text-center user">
						<input data-album-id="<?php echo $album->getId()?>" data-user-id="<?php echo $user->getId()?>" 
							name="<?php echo $radioName;?>" value="" 
							type="radio" 
							<?php echo $album->getCustomerId() != $user->getCustomerId() ? "disabled" : ""; ?> />
					</td>

					<td class="user">
						<?php if ($isUser || $isGroup) : ?>
							<?php echo __("Active"); ?>
						<?php elseif($isRequest) : ?>
							<?php echo __("Pending"); ?>
						<?php else: ?>
							-
						<?php endif;?>
					</td>

					<td class="user">
						<?php if ($isUser || $isGroup) : ?>
							<a class="btn" data-action="notify-user" data-album-id="<?php echo $album->getId(); ?>"
									data-user-id="<?php echo $user->getId(); ?>" href="javascript: void(0);">
								<i class="icon-envelope"></i> <?php echo __("Notify"); ?>
							</a>
						<?php endif; ?>

						<?php if (($isUser && $album->getCustomerId() != $user->getCustomerId()) || ($isRequest)) : ?>
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
	<?php echo pagination($users, "@group_right_user_list");?>
</div>