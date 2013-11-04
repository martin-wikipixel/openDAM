<div id="folder-right-user-list-page">
	<h2><?php echo __("Management of access right of"); ?> "<?php echo $folder->getName(); ?>"</h2>

	<?php include_partial("folder/rightTab", array("folder" => $folder, "selected" => "user")); ?>

	<div class="search-block clearfix">
		<div class="pull-left">
			<ul class="filter">
				<li>
					<?php echo __("Show");?>
				</li>

				<li>
					<a class="<?php if (!$currentState && !$currentLetter && !$currentRole) echo "selected"?>"
						href="<?php echo path("@folder_right_user_list", 
						merge_request_params(null, array("page", "state", "letter", "role")));?>">
						<?php echo __("ALL"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentState == "access") echo "selected"?>" href="<?php echo 
						path("@folder_right_user_list", 
						merge_request_params(array("state" => "access"),
						array("page", "letter", "role")));?>">
						<?php echo __("HAVE ACCESS"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentState == "noAccess") echo "selected"?>" href="<?php echo 
						path("@folder_right_user_list", 
						merge_request_params(array("state" => "noAccess"),
						array("page", "letter", "role")));?>">
						<?php echo __("HAVE NO ACCESS"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == "active") echo "selected"?>" href="<?php echo 
						path("@folder_right_user_list", 
						merge_request_params(array("role" => "active"),
						array("page", "letter", "state")));?>">
						<?php echo __("ACTIVE"); ?>
					</a>
				</li>

				<li>
					<a class="<?php if ($currentRole == "pending") echo "selected"?>" href="<?php echo 
						path("@folder_right_user_list", 
						merge_request_params(array("role" => "pending"),
						array("page", "letter", "state")));?>">
						<?php echo __("PENDING"); ?>
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
				<th class="user text-center" rowspan="2"><?php echo __("Users"); ?></th>
				<th class="text-center" colspan="2"><?php echo __("Permissions"); ?></th>
				<th class="user text-center" rowspan="2"><?php echo __("State"); ?></th>
			</tr>
			<tr>
				<th class="text-center"><?php echo __("Have access"); ?></th>
				<th class="text-center"><?php echo __("Have no access"); ?></th>
			</tr>
		</thead>
	
		<tbody>
			<?php if ($folder->getCustomerId() == $sf_user->getCustomerId() && !$currentState && !$currentLetter
					&& !$currentRole && !$keyword && $page == 1) :?>
				<tr>
					<td class="user"><i class="icon-group"></i> <?php echo __("unit.everybody"); ?></td>
	
					<td class="text-center user">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							name="radio-everybody" value="1" 
							type="radio" <?php echo $folder->getFree() ? "checked" : ""; ?> />
					</td>
	
					<td class="text-center user">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							name="radio-everybody" value="0" 
							type="radio" <?php echo !$folder->getFree() ? "checked" : ""; ?> />
					</td>
	
					<td class="text-center user">
						-
					</td>
				</tr>
			<?php endif; ?>

			<?php foreach ($users as $user): ?>
				<?php
					$radioName = "radio-".$user->getId();

					$array = RightUtils::getAccessForFolderAndUser($folder->getRawValue(), $user->getRawValue());
					$object = $array && array_key_exists("object", $array) ? $array["object"] : null;
					$inherit = $array && array_key_exists("inherit", $array) ? $array["inherit"] : null;

					// associé directement au user
					$isUser = $object instanceof UserFolder;

					// via un groupe
					$isGroup = $object instanceof Unit;

					// via un dossier
					$isFolder = $object instanceof Folder;

					$inheritFromGroup = $inherit instanceof Unit;

					// via une demande d'accès (request)
					$requestObject = RequestPeer::getRequestFolder($folder->getId(), $user->getId());

					$isRequest = $requestObject instanceof Request;

					$access = false;
					$noAccess = false;

					// if ($isRequest && !$isUser) {
					if ($isRequest) {
						$access = false;
						$noAccess = false;
					}
					else {
						if ($array && array_key_exists("access", $array)) {
							$access = $array["access"];
							$noAccess = !$array["access"];
						}
					}

					if ($inheritFromGroup) {
						$object = $inherit;
					}
				?>
				<tr>
					<td class="user">
						<i class="icon-user"></i> <?php echo $user->getEmail(); ?>

						<?php if ($isGroup || $inheritFromGroup) : ?>
							<?php
								$name = '<i class="icon-group"></i> '.$object->getName();

								echo " (".__("via %name", array("%name" => $name)).")";
							?>
						<?php endif; ?>

						<?php if ($user->getComment()) echo "<em>(".$user->getComment().")</em>"; ?>
					</td>

					<td class="text-center user">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							data-user-id="<?php echo $user->getId()?>" 
							name="<?php echo $radioName;?>" value="1" 
							<?php if ($isRequest) echo "class='pending'"; ?>
							type="radio" <?php echo $access ? "checked" : ""; ?> />
					</td>

					<td class="text-center user">
						<input data-folder-id="<?php echo $folder->getId()?>" 
							data-user-id="<?php echo $user->getId()?>" 
							name="<?php echo $radioName;?>" value="0" 
							<?php if ($isRequest) echo "class='pending'"; ?>
							type="radio" <?php echo $noAccess ? "checked" : ""; ?> />
					</td>

					<td class="text-center user">
						<?php if ($isRequest) : ?>
							<?php echo __("Pending"); ?>
						<?php else: ?>
							<?php echo __("Active"); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo pagination($users, "@folder_right_user_list");?>
</div>