<div id="admin-user-edit-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_user_list"), "text" => __("Users")),
			array("link" => path("@admin_user_edit", array("id" => $user->getId())), "text" => $user->__toString())
		));
	?>

	<?php include_partial("admin/userTab", array("user" => $user, "selected" => "user")); ?>

	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>
		<?php echo $form["id"]->render(); ?>

		<div class="row-fluid">	
			<div class="span6">
				<div class="control-group">
					<label class="control-label required" for="data_role_id"><?php echo __("User role")?></label>
					<div class="controls">
						<?php echo $form["role_id"]->render(); ?>
						<?php echo $form["role_id"]->renderError();?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label required" for="data_firstname"><?php echo __("First name")?></label>
					<div class="controls">
						<?php echo $form["firstname"]->render(); ?>
						<?php echo $form["firstname"]->renderError();?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label required" for="data_lastname"><?php echo __("Last name")?></label>
					<div class="controls">
						<?php echo $form["lastname"]->render(); ?>
						<?php echo $form["lastname"]->renderError();?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label required" for="data_email"><?php echo __("Email")?></label>
					<div class="controls">
						<?php echo $form["email"]->render(); ?>
						<?php echo $form["email"]->renderError();?>
					</div>
				</div>
			</div>
			
			<div class="span6">
				<div class="control-group">
					<label class="control-label required" for="data_country"><?php echo __("Country")?></label>
					<div class="controls">
						<?php //echo $form["country"]->render(); ?>
						<select id="data_country" name="data[country]">
							<?php foreach ($countries as $country):?>
								<option data-phone-code="<?php echo $country->getPhoneCode()?>" <?php echo $country->getId() == $selectedCountryId ? "selected" : ""?> value="<?php echo $country->getId()?>">
									<?php echo $country->getName()?>
								</option>
							<?php endforeach;?>
						</select>
						
						<?php echo $form["country"]->renderError();?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label required" for="data_culture"><?php echo __("Language")?></label>
					<div class="controls">
						<?php echo $form["culture"]->render(); ?>
						<?php echo $form["culture"]->renderError();?>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="data_phone"><?php echo __("Phone")?></label>
					<div class="controls">
						<?php echo $form["phone_code"]->render(); ?><?php echo $form["phone"]->render(); ?>
						<?php if ($user->getPhone()):?>
							<?php
								if(substr($user->getPhone(), 0, 1) == "0" && strlen(trim($user->getPhone())) == 10)
									$phone = $user->getCountry()->getPhoneCode().trim(substr($user->getPhone(), 1));
								else
									$phone = $user->getCountry()->getPhoneCode().$user->getPhone();
							?>
			
							<a href="http://www.pagesjaunes.fr/pagesblanches/rechercheInverse.do?numeroTelephone=<?php echo $user->getPhone(); ?>" target="_blank"><?php echo __("Who?"); ?></a>
						<?php endif;?>
						<?php echo $form["phone"]->renderError();?>
					</div>
				</div>
	
				<div class="control-group">
					<label class="control-label" for="data_position"><?php echo __("Position")?></label>
					<div class="controls">
						<?php echo $form["position"]->render(); ?>
						<?php echo $form["position"]->renderError();?>
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
			<button class="btn btn-primary"><?php echo __("Save");?></button>
		</div>
	</form>
</div>