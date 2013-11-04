<div id="folder-right-group-list-page">
	<h2><?php echo __("Management of access right of"); ?> "<?php echo $folder->getName(); ?>"</h2>

	<?php include_partial("folder/rightTab", array("folder" => $folder, "selected" => "group")); ?>

	<div class="search-block clearfix">
		<div class="pull-left">
			<ul class="filter">
				<li>
					<?php echo __("Show");?>
				</li>

				<li>
					<a class="<?php if (!$currentState && !$currentLetter) echo "selected"?>"
						href="<?php echo path("@folder_right_group_list", 
						merge_request_params(null, array("page", "state", "letter")));?>">
						<?php echo __("ALL"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentState == "access") echo "selected"?>" href="<?php echo 
						path("@folder_right_group_list", 
						merge_request_params(array("state" => "access"),
						array("page", "letter")));?>">
						<?php echo __("HAVE ACCESS"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentState == "noAccess") echo "selected"?>" href="<?php echo 
						path("@folder_right_group_list", 
						merge_request_params(array("state" => "noAccess"),
						array("page", "letter")));?>">
						<?php echo __("HAVE NO ACCESS"); ?>
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
							path("@folder_right_user_list", 
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
				<th class="group text-center" rowspan="2"><?php echo __("group.title"); ?></th>
				<th class="text-center" colspan="3"><?php echo __("Permissions"); ?></th>
			</tr>
			<tr>
				<th class="text-center"><?php echo __("Have access"); ?></th>
				<th class="text-center"><?php echo __("Have no access"); ?></th>
				<th class="text-center"><?php echo __("n/a"); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($groups as $group): ?>
				<?php
					$radioName = "radio-".$group->getId();

					$object = UnitFolderPeer::retrieveByUnitIdAndFolderId($group->getId(), $folder->getId());

					$access = false;
					$noAccess = false;

					if ($object) {
						if (!$object->getRole()) {
							$noAccess = true;
						}

						if ($object->getRole()) {
							$access = true;
						}
					}
				?>
				<tr>
					<td class="group">
						<i class="icon-group"></i> <?php echo $group->getTitle(); ?>
					</td>

					<td class="text-center group">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							data-group-id="<?php echo $group->getId()?>" 
							name="<?php echo $radioName;?>" value="1" 
							type="radio" <?php echo $access ? "checked" : ""; ?> />
					</td>

					<td class="text-center group">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							data-group-id="<?php echo $group->getId()?>" 
							name="<?php echo $radioName;?>" value="0" 
							type="radio" <?php echo $noAccess ? "checked" : ""; ?> />
					</td>

					<td class="text-center group">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							data-group-id="<?php echo $group->getId()?>" 
							name="<?php echo $radioName;?>" value="-1" 
							type="radio" <?php echo !$access && !$noAccess ? "checked" : ""; ?> />
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo pagination($groups, "@folder_right_group_list");?>
</div>