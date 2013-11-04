<div id="right-group-search-page">
	<h2><?php echo __("Management of access right of"); ?> "<?php echo $album->getName(); ?>"</h2>

	<?php include_partial("group/right_tab", array("selected" => "group", "album" => $album)); ?>

	<div id="search-box">
		<label><?php echo __("Select a group")?></label>
		<div class="form-search" style="display: inline-block;">
			<div class="input-append">
				<input type="text" value="" data-album-id="<?php echo $album->getId(); ?>" id="group-autocomplete" placeholder="<?php echo __("Search by name");?>" class="input-xlarge search-query" name="keyword">
				<button class="btn" type="submit" name="commit"><i class="icon-search"></i></button>
			</div>
		</div>
	</div>

	<?php if ($group):?>
		<h3><?php echo __("Group's access list of \"%name\"", array("%name" => $group->getName()));?></h3>

		<div class="search-block clearfix">
			<div class="pull-left">
				<ul class="filter">
					<li>
						<?php echo __("Sort by name");?>
					</li>

					<li>
						<a class="<?php if (!$currentLetter) echo "selected"?>" href="<?php echo 
							path("@group_right_group_search", 
							merge_request_params(null, array("page","letter")));?>">
							<?php echo __("ALL"); ?>
						</a>
					</li>

					<?php foreach ($letters as $letter) : ?>
						<li>
						<a class="<?php if ($currentLetter == $letter) echo "selected"?>" href="<?php echo 
							path("@group_right_group_search", 
							merge_request_params(array("letter" => $letter), array("page")));?>">
							<?php echo $letter;?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
	
			<form class="form-search pull-right">
				<?php params_to_input_hidden(merge_query_params(null, array("keyword", "page")));?>
		
				<div class="input-append">
					<input name="keyword" type="text" class="input-medium search-query" placeholder="<?php echo __("Search")?>" value="<?php echo $keyword;?>">
					<button class="btn"><i class="icon-search"></i></button>
				</div>
			</form>
		</div>
		<table id="permissions-table" class="table table-bordered">
			<thead class="text-center">
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
				<?php foreach ($rights as $right) :?>
					<?php
						$album = $right->getGroupe(); 
					?>
					<tr>
						<td><i class="icon-book"></i> <?php echo $album->getName(); ?></td>
						<?php foreach ($roles as $role) :?>
							<?php 
								$roleId = $role->getId();
								$radioName = "radio-".$album->getId();
							?>
							<td class="text-center">
								<?php if (!$album->getFree() || ($album->getFree() && ($roleId == RolePeer::__ADMIN 
										|| $roleId == $album->getFreeCredential()))): ?>
									<input name="<?php echo $radioName;?>" data-album-group-id="<?php echo $right->getId()?>" value="<?php echo $roleId; ?>" 
										type="radio" <?php echo $roleId == $right->getRole() ? "checked" : ""; ?>/>
								<?php else: ?>
									-
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
						<td class="text-center">
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_group_permission_delete", 
									array("album" => $album->getId(), "group"=> $group->getId(), "csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo pagination($rights, "@group_right_group_search");?>
	<?php else:?>
		<?php echo __("No group selected.");?>
	<?php endif;?>
</div>