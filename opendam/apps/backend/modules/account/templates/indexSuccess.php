<div id="account-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account"), "text" => __("Edit my profile")),
		));
	?>
	
	<?php include_partial("account/tab", array("selected" => "edit"));?>
	
	<form class="form-horizontal" method="post">
		<?php echo $form['_csrf_token']->render(); ?>

		<?php if ($sf_user->isAdmin()):?>
			<div class="control-group">
				<label class="control-label required" for="data_email"><?php echo __("Email")?></label>
				<div class="controls">
					<?php echo $form["email"]->render(); ?>
					<?php echo $form["email"]->renderError(); ?>
				</div>
			</div>
		<?php endif;?>

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
				
				<?php echo $form["country"]->renderError(); ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="data_phone"><?php echo __("Phone")?></label>
			<div class="controls">
				<?php echo $form["phone_code"]->render(); ?><?php echo $form["phone"]->render(); ?>
				<?php echo $form["phone"]->renderError();?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label required" for="data_language"><?php echo __("Language")?></label>
			<div class="controls">
				<?php echo $form["language"]->render(); ?>
				<?php echo $form["language"]->renderError(); ?>
			</div>
		</div>

		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Save")?></button>
		</div>
	</form>
</div>