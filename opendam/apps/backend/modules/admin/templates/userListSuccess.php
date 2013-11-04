<div id="admin-user-list-page" class="span12">
	<?php
		$orderBy = $orderBy->getRawValue();
		
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
		));
	?>

	<?php //include_partial("customer/tab", array("selected" => "users", "customer" => $customer)); ?>

	<div class="commands-top">
		<?php if ($canAddUser):?>
			<a class="btn btn-primary" href="<?php echo path("@admin_user_new");?>">
				<i class="icon-plus-sign"></i> <?php echo __("Add user"); ?>
			</a>
		<?php else:?>
			<p class="alert alert-info span3">
				<?php echo __("You have reached the maximum number of allowed users.");?>
			</p>
		<?php endif;?>
	</div>
	
	<div class="search-block clearfix">
		<div class="pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("orderBy", "page")));?>
				
				<label><?php echo __('Sort users by')?></label>
				<select name="orderBy[]">
					<option <?php if (in_array("lastname_asc", $orderBy)) echo "selected";?> value="lastname_asc"><?php echo __("Name ascending")?></option>
					<option <?php if (in_array("lastname_desc", $orderBy)) echo "selected";?> value="lastname_desc"><?php echo __("Name descending")?></option>
					<option <?php if (in_array("email_asc", $orderBy)) echo "selected";?> value="email_asc"><?php echo __("Email ascending")?></option>
					<option <?php if (in_array("email_desc", $orderBy)) echo "selected";?> value="email_desc"><?php echo __("Email descending")?></option>
				</select>
				
				<button class="btn"><i class="icon-search"></i></button>
			</form>
		
			<ul class="filter">
				<li>
					<a class="<?php if ($currentLetter === "") echo "selected"?>" href="<?php echo path("@admin_user_list", 
							merge_request_params(null, array("letter", "page")));?>"><?php echo __('ALL')?>
					</a> 
				</li>
				
				<?php foreach ($letters as $letter):?>
					<li>
						<a class="<?php if ($letter == $currentLetter) echo "selected"?>" href="<?php echo path("@admin_user_list", 
								merge_request_params(array("letter" => $letter), array("page")));?>"><?php echo $letter?>
						</a>
					</li>
				<?php endforeach;?>
			</ul>
	
			<ul class="filter">
				<li>
					<a class="<?php if (!$currentRole) echo "selected"?>" href="<?php echo path("@admin_user_list", 
						merge_request_params(null, array("role", "page")));?>"><?php echo __("ALL");?></a>
				</li>
				
				<li>
					<a class="<?php if ($currentRole == RolePeer::__ADMIN) echo "selected"?>" href="<?php echo path("@admin_user_list", 
						merge_request_params(array("role" => RolePeer::__ADMIN), array("page")));?>"><?php echo __("ADMINISTRATORS");?></a>
				</li>
				
				<li>
				<a class="<?php if ($currentRole == RolePeer::__CONTRIB) echo "selected"?>" href="<?php echo path("@admin_user_list", 
						merge_request_params(array("role" => RolePeer::__CONTRIB), array("page")));?>"><?php echo __("USERS");?></a>
				</li>
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

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Firstname")?></th>
				<th><?php echo __("Lastname")?></th>
				<th><?php echo __("Email")?></th>
				<th><?php echo __("Role of user")?></th>
				<th><?php echo __("Comment")?></th>
				<th><?php echo __("Actions")?></th>
			</tr>
		</thead>
		
		<tbody>
			<?php if (!count($users->getResults())):?>
				<tr>
					<td colspan="5"><?php echo __("No users found.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($users as $user):?>
					<tr>
						<td><?php echo $user->getFirstname();?></td>
						<td><?php echo $user->getLastName();?></td>
						<td><?php echo $user->getEmail();?></td>
						<td><?php echo $user->getRoleId() == RolePeer::__READER ? __("User") : __($user->getRole()); ?></td>
						<td>
							<?php if ($user->getComment()) : ?>
								<?php echo $user->getComment(); ?>
							<?php else: ?>
								-
							<?php endif; ?>
						</td>
						<td>
							<a class="btn" href="<?php echo path("@admin_user_edit", array("id" => $user->getId()));?>"><?php echo __("Manage"); ?></a>

							<?php if ($user->getState() == UserPeer::__STATE_ACTIVE) : ?>
								<a class="btn" href="<?php echo path("@admin_user_login_into", array("id" => $user->getId()));?>">
									<?php echo __("Connection"); ?>
								</a>
							<?php endif; ?>

							<?php if ($user->getState() == UserPeer::__STATE_SUSPEND) : ?>
								<a class="btn" href="<?php echo path("@admin_user_activate", array("id" => $user->getId(), "csrfToken" => $csrfToken));?>">
									<?php echo __("Activate"); ?>
								</a>
							<?php endif;?>
							
							<a class="btn btn-danger" data-action="delete" href="<?php echo path("@admin_user_delete", array("id" => $user->getId(), 
									"csrfToken" => $csrfToken)); ?>">
								<i class="icon-trash"></i> <?php echo __("Remove");?>
							</a>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($users, "@admin_user_list");?>
</div>
