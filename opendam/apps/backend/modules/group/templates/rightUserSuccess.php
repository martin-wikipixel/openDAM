<div id="right-user-search-page">
	<h2><?php echo __("Management of access right of"); ?> "<?php echo $album->getName(); ?>"</h2>

	<?php include_partial("group/right_tab", array("selected" => "user", "album" => $album)); ?>

	<div id="search-box">
		<label><?php echo __("Select an user")?></label>
		<div class="form-search" style="display: inline-block;">
			<div class="input-append">
				<input type="text" value="" data-album-id="<?php echo $album->getId(); ?>" id="user-autocomplete" placeholder="<?php echo __("Search by firstame, lastname, email");?>" class="input-xlarge search-query" name="keyword">
				<button class="btn" type="submit" name="commit"><i class="icon-search"></i></button>
			</div>
		</div>
	</div>

	<?php if ($user):?>
		<h3><?php echo __("User's access list of \"%username\"", array("%username" => $user));?></h3>

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
		<?php echo pagination($albums, "@group_right_user_search");?>
	<?php else:?>
		<?php echo __("No user selected.");?>
	<?php endif;?>
</div>