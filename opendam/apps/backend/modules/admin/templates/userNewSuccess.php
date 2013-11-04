<div id="admin-user-new-page" class="span12">
	<?php
		$customer = $customer->getRawValue();
	
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_new"), "text" => __("New user")),
		));
	?>

	<form class="form-horizontal" method="post" autocomplete="off">
		<?php echo $form["_csrf_token"]->render(); ?>
	
		<div class="row-fluid">	
			<div class="span6">
				<div class="control-group">
					<label class="control-label required" for="data_role_id"><?php echo __("User role")?></label>
					<div class="controls">
						<?php echo $form["role_id"]->render(); ?>
						<?php echo $form["role_id"]->renderError(); ?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label required" for="data_firstname"><?php echo __("First name")?></label>
					<div class="controls">
						<?php echo $form["firstname"]->render(); ?>
						<?php echo $form["firstname"]->renderError(); ?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label required" for="data_lastname"><?php echo __("Last name")?></label>
					<div class="controls">
						<?php echo $form["lastname"]->render(); ?>
						<?php echo $form["lastname"]->renderError(); ?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label required" for="data_email"><?php echo __("Email")?></label>
					<div class="controls">
						<?php echo $form["email"]->render(); ?>
						<?php echo $form["email"]->renderError(); ?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label required" for="data_country"><?php echo __("Country")?></label>
					<div class="controls">
						<?php //echo $form["country"]->render(); ?>
						<select id="data_country" name="data[country]">
							<?php foreach ($countries as $country):?>
								<option data-phone-code="<?php echo $country->getPhoneCode()?>" <?php echo $country->getId() == $currentCountry ? "selected" : ""?> value="<?php echo $country->getId()?>">
									<?php echo $country->getName()?>
								</option>
							<?php endforeach;?>
						</select>
						<?php echo $form["country"]->renderError(); ?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label required" for="data_culture"><?php echo __("Language")?></label>
					<div class="controls">
						<?php echo $form["culture"]->render(); ?>
						<?php echo $form["culture"]->renderError(); ?>
					</div>
				</div>
			</div>

			<div class="span6">
				<div class="control-group">
					<label class="control-label required" for="data_password"><?php echo __("Password")?></label>
					<div class="controls">
						<?php echo $form["password"]->render(); ?>
						<?php echo $form["password"]->renderError(); ?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label required" for="data_confirm_password"><?php echo __("Confirm password")?></label>
					<div class="controls">
						<?php echo $form["confirm_password"]->render(); ?>
						<?php echo $form["confirm_password"]->renderError(); ?>
					</div>
				</div>

				 <div class="control-group">
					<div class="controls">
						<label class="checkbox">
							<?php echo $form["send_username"]->render(); ?>
							<?php echo __("Send login information by email"); ?>
						</label>
					</div>
				</div>
		
				<div class="control-group">
					<label class="control-label" for="data_phone"><?php echo __("Phone number")?></label>
					<div class="controls">
						<?php echo $form["phone_code"]->render(); ?><?php echo $form["phone"]->render(); ?>
						<?php echo $form["phone"]->renderError(); ?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="data_position"><?php echo __("Position")?></label>
					<div class="controls">
						<?php echo $form["position"]->render(); ?>
						<?php echo $form["position"]->renderError(); ?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="data_comment"><?php echo __("Comment")?></label>
					<div class="controls">
						<?php echo $form["comment"]->render(); ?>
						<?php echo $form["comment"]->renderError(); ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Add")?></button>
		</div>
	</form>
</div>