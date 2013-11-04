<div id="selection-edit-page" class="span12">
	<?php
		$errors = $errors->getRawValue();
	
		draw_breadcrumb(array(
			array("link" => path("@selection_list"), "text" => __("Collections")),
			array("link" => path("@selection_edit", array("id" => $selection->getId())), "text" => $selection->getTitle()),
		));
	?>
	
	<?php include_partial("selection/tab", array("selection" => $selection, "selected" => "edit"))?>
	
	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>

		<div class="control-group">
			<label class="control-label required" for="data_name"><?php echo __("Name")?></label>
			<div class="controls">
				<?php echo $form["name"]->render()?>
				<?php echo $form["name"]->renderError()?>
			</div>
		</div>
				
		<div class="control-group">
			<label class="control-label" for="data_description"><?php echo __("Description")?></label>
			<div class="controls">
				<?php echo $form["description"]->render()?>
				<?php echo $form["description"]->renderError()?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label required"><?php echo __("Share")?></label>
			<div class="controls">
				<label class="radio inline">
					<?php echo __("Yes");?>
					<input type="radio" name="isShared" value="1" <?php echo $isShared ? "checked": ""?>>
				</label>
				
				<label class="radio inline">
					<?php echo __("No");?>
					<input type="radio" name="isShared" value="" <?php echo $isShared ? "": "checked"?>>
				</label>
			</div>
		</div>

		<div id="share-container" class="<?php echo $isShared ? "": "hide"?>">
			<div class="control-group">
				<label class="control-label required" for="data_permalink"><?php echo __("Permalink")?></label>
				<div class="controls">
					<input class="span3" data-action="select" type="text" readonly name="data_permalink" id="data_permalink" value="<?php echo $selection->getIsValid() ? 
						url("public_selection_show", array("code" => $selection->getCode())) : __("Pending validation by administrator") ?>">
				</div>
			</div>
			
			<div class="control-group">
				<label class="control-label required"><?php echo __("Protected by password")?></label>
				<div class="controls">
					<label class="radio inline">
						<?php echo __("Yes");?>
						<input type="radio" name="allow_password" value="1" <?php echo $allowPassword ? "checked": ""?>>
					</label>
					
					<label class="radio inline">
						<?php echo __("No");?>
						<input type="radio" name="allow_password" value="" <?php echo $allowPassword ? "": "checked"?>>
					</label>
	
					<div id="selection-password-panel" class="<?php echo $allowPassword ? "" : "hide"?>">
						<?php if ($allowPassword && $selection->getPassword()):?>
							<div class="alert alert-info">
								<?php echo __("Enter an password to erase the previous password.");?>
							</div>
						<?php endif;?>
						
						<div>
							<input type="password" name="password" placeholder="<?php echo __("Password")?>" value="<?php echo $password?>">
	
							<?php if (isset($errors["password"])):?>
								<ul class="error_list">
									<li><?php echo $errors["password"]?></li>
								</ul>
							<?php endif;?>
						</div>
	
						<div>
							<input type="password" name="confirmPassword" placeholder="<?php echo __("Password")?>" value="<?php echo $confirmPassword?>">
	
							<?php if (isset($errors["confirm_password"])):?>
								<ul class="error_list">
									<li><?php echo $errors["confirm_password"]?></li>
								</ul>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div>
	
			<div class="control-group">
				<label class="control-label required"><?php echo __("Allow comments")?></label>
				<div class="controls">
					<label class="radio inline">
						<?php echo __("Yes");?>
						<input type="radio" name="allow_comment" value="1" <?php echo $allowComment ? "checked": ""?>>
					</label>
					
					<label class="radio inline">
						<?php echo __("No");?>
						<input type="radio" name="allow_comment" value="" <?php echo $allowComment ? "": "checked"?>>
					</label>
				</div>
			</div>
		</div>

		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Save")?></button>
		</div>
	</form>
</div>
